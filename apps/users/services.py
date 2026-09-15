import logging
from typing import Any

from django.conf import settings
from django.contrib.auth.tokens import default_token_generator
from django.core.mail import send_mail
from django.db import transaction
from django.utils.encoding import force_bytes, force_str
from django.utils.http import urlsafe_base64_decode, urlsafe_base64_encode
from rest_framework_simplejwt.exceptions import TokenError
from rest_framework_simplejwt.tokens import RefreshToken

from apps.common.exceptions import BusinessRuleError
from apps.users.models import User
from apps.users.selectors import UserSelector
from utils.enums import Role

logger = logging.getLogger(__name__)

PROFILE_FIELDS = ("user_name", "full_name", "email", "phone")


class UserService:
    @staticmethod
    @transaction.atomic
    def create_user(*, actor: User, data: dict[str, Any]) -> User:
        user = User(
            **{field: data[field] for field in PROFILE_FIELDS if field in data},
            role=data.get("role", Role.EMPLOYEE),
            created_by=actor,
            updated_by=actor,
        )
        user.set_password(data["password"])
        user.save()
        logger.info("User %s created by %s", user.user_name, actor.user_name)
        return user

    @staticmethod
    @transaction.atomic
    def update_user(*, actor: User, user: User, data: dict[str, Any]) -> User:
        for field in (*PROFILE_FIELDS, "role"):
            if field in data:
                setattr(user, field, data[field])
        if data.get("password"):
            user.set_password(data["password"])
        user.updated_by = actor
        user.save()
        return user

    @staticmethod
    def delete_user(*, actor: User, user: User) -> None:
        if actor.pk == user.pk:
            raise BusinessRuleError("لا يمكنك حذف حسابك")
        user.soft_delete(user=actor)
        logger.info("User %s deleted by %s", user.user_name, actor.user_name)


class AccountService:
    @staticmethod
    def update_account(*, user: User, data: dict[str, Any]) -> User:
        return UserService.update_user(
            actor=user, user=user, data={k: v for k, v in data.items() if k != "role"}
        )


class AuthService:
    @staticmethod
    def logout(refresh_token: str) -> None:
        try:
            RefreshToken(refresh_token).blacklist()  # type: ignore[arg-type]
        except TokenError as exc:
            raise BusinessRuleError("رمز الجلسة غير صالح") from exc


class PasswordResetService:
    @staticmethod
    def build_reset_link(user: User) -> str:
        uid = urlsafe_base64_encode(force_bytes(user.pk))
        token = default_token_generator.make_token(user)
        return f"{settings.FRONTEND_URL.rstrip('/')}/reset-password/{uid}/{token}"

    @classmethod
    def send_reset_email(cls, email: str) -> None:
        """Silently does nothing for unknown emails, so the endpoint cannot be used to probe accounts."""
        user = UserSelector.active_by_email(email)
        if user is None:
            return

        send_mail(
            subject="اعادة تعيين كلمة المرور",
            message=(
                f"مرحبا {user.full_name}\n\n"
                f"لاعادة تعيين كلمة المرور افتح الرابط التالي:\n{cls.build_reset_link(user)}\n\n"
                "اذا لم تطلب ذلك تجاهل هذه الرسالة."
            ),
            from_email=None,
            recipient_list=[user.email],
        )
        logger.info("Password reset email sent to user %s", user.user_name)

    @staticmethod
    def get_user_for_token(uid: str, token: str) -> User:
        try:
            user = User.objects.get(pk=force_str(urlsafe_base64_decode(uid)))
        except (User.DoesNotExist, ValueError, TypeError, OverflowError) as exc:
            raise BusinessRuleError("رابط اعادة تعيين كلمة المرور غير صالح") from exc

        if not default_token_generator.check_token(user, token):
            raise BusinessRuleError("رابط اعادة تعيين كلمة المرور غير صالح او انتهت صلاحيته")
        return user

    @classmethod
    def reset_password(cls, *, uid: str, token: str, password: str) -> User:
        user = cls.get_user_for_token(uid, token)
        user.set_password(password)
        user.save(update_fields=["password", "updated_at"])
        return user
