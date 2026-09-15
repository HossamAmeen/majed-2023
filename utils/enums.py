from django.db import models


class Role(models.IntegerChoices):
    """User roles, numbered as in the original system (users.role)."""

    MASTER = 1, "مدير عام"
    ADMIN = 2, "مسؤول"
    EMPLOYEE = 3, "موظف"
