from typing import Any

from django.contrib.auth.password_validation import validate_password
from rest_framework import serializers
from rest_framework.validators import UniqueValidator
from rest_framework_simplejwt.serializers import TokenObtainPairSerializer

from apps.users.models import User
from apps.users.validators import (
    validate_assignable_role,
    validate_email_available,
    validate_password_confirmation,
    validate_role_change,
)


class UserSerializer(serializers.ModelSerializer):  # type: ignore[type-arg]
    role_display = serializers.CharField(source="get_role_display", read_only=True)

    class Meta:
        model = User
        fields = [
            "id",
            "user_name",
            "full_name",
            "email",
            "phone",
            "role",
            "role_display",
            "last_login",
            "created_at",
        ]
        read_only_fields = fields


class _PasswordFieldsMixin(serializers.Serializer):  # type: ignore[type-arg]
    password = serializers.CharField(
        write_only=True, required=False, allow_blank=True, style={"input_type": "password"}
    )
    password_confirmation = serializers.CharField(
        write_only=True, required=False, allow_blank=True, style={"input_type": "password"}
    )

    def _validate_passwords(self, attrs: dict[str, Any], user: User | None) -> dict[str, Any]:
        validate_password_confirmation(attrs)
        attrs.pop("password_confirmation", None)
        if attrs.get("password"):
            candidate = user or User(**{k: v for k, v in attrs.items() if k != "password"})
            validate_password(attrs["password"], candidate)
        else:
            attrs.pop("password", None)
        return attrs


class UserWriteSerializer(_PasswordFieldsMixin, serializers.ModelSerializer):  # type: ignore[type-arg]
    user_name = serializers.CharField(
        max_length=150,
        validators=[UniqueValidator(queryset=User.all_objects.all(), message="اسم المستخدم مستخدم بالفعل")],
    )

    class Meta:
        model = User
        fields = [
            "user_name",
            "full_name",
            "email",
            "phone",
            "role",
            "password",
            "password_confirmation",
        ]

    def validate_email(self, value: str) -> str:
        return validate_email_available(value, exclude_pk=getattr(self.instance, "pk", None))

    def validate_role(self, value: int) -> int:
        if self.instance is None:
            return validate_assignable_role(value)
        return value

    def validate(self, attrs: dict[str, Any]) -> dict[str, Any]:
        if self.instance is None and not attrs.get("password"):
            raise serializers.ValidationError({"password": "كلمة المرور مطلوبة"})
        if self.instance is not None and "role" in attrs:
            validate_role_change(
                actor=self.context["request"].user, target=self.instance, new_role=attrs["role"]
            )
        return self._validate_passwords(attrs, self.instance)


class AccountSerializer(_PasswordFieldsMixin, serializers.ModelSerializer):  # type: ignore[type-arg]
    user_name = serializers.CharField(
        max_length=150,
        validators=[UniqueValidator(queryset=User.all_objects.all(), message="اسم المستخدم مستخدم بالفعل")],
    )

    class Meta:
        model = User
        fields = ["user_name", "full_name", "email", "phone", "password", "password_confirmation"]

    def validate_email(self, value: str) -> str:
        return validate_email_available(value, exclude_pk=getattr(self.instance, "pk", None))

    def validate(self, attrs: dict[str, Any]) -> dict[str, Any]:
        return self._validate_passwords(attrs, self.instance)


class LoginSerializer(TokenObtainPairSerializer):
    """Accepts `login` (user name or email) and `password`; returns tokens and the user."""

    username_field = "login"
    default_error_messages = {"no_active_account": "اسم المستخدم او كلمة المرور غير صحيحة"}

    def validate(self, attrs: dict[str, Any]) -> dict[str, Any]:
        tokens = super().validate(attrs)
        return {**tokens, "user": UserSerializer(self.user).data}


class LoginResponseSerializer(serializers.Serializer):  # type: ignore[type-arg]
    access = serializers.CharField()
    refresh = serializers.CharField()
    user = UserSerializer()


class LogoutSerializer(serializers.Serializer):  # type: ignore[type-arg]
    refresh = serializers.CharField()


class PasswordResetRequestSerializer(serializers.Serializer):  # type: ignore[type-arg]
    email = serializers.EmailField()


class PasswordResetConfirmSerializer(serializers.Serializer):  # type: ignore[type-arg]
    uid = serializers.CharField()
    token = serializers.CharField()
    password = serializers.CharField(write_only=True, style={"input_type": "password"})
    password_confirmation = serializers.CharField(write_only=True, style={"input_type": "password"})

    def validate(self, attrs: dict[str, Any]) -> dict[str, Any]:
        validate_password_confirmation(attrs)
        validate_password(attrs["password"])
        return attrs


class MessageSerializer(serializers.Serializer):  # type: ignore[type-arg]
    detail = serializers.CharField()
