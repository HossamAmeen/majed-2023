from rest_framework.exceptions import ValidationError

from apps.users.models import User
from utils.enums import Role

ASSIGNABLE_ROLES = (Role.ADMIN, Role.EMPLOYEE)


def validate_assignable_role(role: int) -> int:
    if role not in ASSIGNABLE_ROLES:
        raise ValidationError("يمكن تعيين صلاحية مسؤول او موظف فقط")
    return role


def validate_role_change(*, actor: User, target: User, new_role: int) -> None:
    if new_role == target.role:
        return
    if actor.pk == target.pk:
        raise ValidationError({"role": "لا يمكنك تغيير صلاحيتك"})
    if target.role == Role.MASTER:
        raise ValidationError({"role": "لا يمكن تغيير صلاحية المدير العام"})
    validate_assignable_role(new_role)


def validate_email_available(email: str, exclude_pk: int | None = None) -> str:
    queryset = User.objects.filter(email__iexact=email)
    if exclude_pk is not None:
        queryset = queryset.exclude(pk=exclude_pk)
    if queryset.exists():
        raise ValidationError("البريد الالكتروني مستخدم بالفعل")
    return email


def validate_password_confirmation(attrs: dict[str, object]) -> None:
    password = attrs.get("password")
    if password and password != attrs.get("password_confirmation"):
        raise ValidationError({"password_confirmation": "كلمتا المرور غير متطابقتين"})
