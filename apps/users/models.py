from typing import Any

from django.contrib.auth.base_user import AbstractBaseUser, BaseUserManager
from django.contrib.auth.models import PermissionsMixin
from django.db import models

from apps.common.models import AllObjectsManager, SoftDeleteModel, SoftDeleteQuerySet
from utils.enums import Role


class UserManager(BaseUserManager.from_queryset(SoftDeleteQuerySet)):  # type: ignore[misc]
    """Hides soft-deleted users, so they cannot sign in or be listed."""

    use_in_migrations = True

    def get_queryset(self) -> SoftDeleteQuerySet:
        return super().get_queryset().alive()

    def create_user(self, user_name: str, password: str | None = None, **extra_fields: Any) -> "User":
        if not user_name:
            raise ValueError("user_name is required")
        extra_fields["email"] = self.normalize_email(extra_fields.get("email", ""))
        user = self.model(user_name=user_name, **extra_fields)
        user.set_password(password)
        user.save(using=self._db)
        return user

    def create_superuser(self, user_name: str, password: str | None = None, **extra_fields: Any) -> "User":
        extra_fields["role"] = Role.MASTER
        extra_fields["is_superuser"] = True
        return self.create_user(user_name, password, **extra_fields)


class User(AbstractBaseUser, PermissionsMixin, SoftDeleteModel):
    user_name = models.CharField(max_length=150, unique=True)
    full_name = models.CharField(max_length=255)
    email = models.EmailField()
    phone = models.CharField(max_length=30, blank=True)
    role = models.PositiveSmallIntegerField(choices=Role.choices, default=Role.EMPLOYEE)
    is_active = models.BooleanField(default=True)

    objects = UserManager()  # type: ignore[misc]
    all_objects = AllObjectsManager()  # type: ignore[misc]

    USERNAME_FIELD = "user_name"
    EMAIL_FIELD = "email"
    REQUIRED_FIELDS = ["email", "full_name"]

    class Meta:
        ordering = ["-id"]

    def __str__(self) -> str:
        return self.user_name

    @property
    def is_staff(self) -> bool:
        """Django admin access is limited to superusers."""
        return self.is_superuser

    @property
    def is_master(self) -> bool:
        return self.role == Role.MASTER
