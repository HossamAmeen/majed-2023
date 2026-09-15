from typing import Any

from django.conf import settings
from django.db import models
from django.utils import timezone


class BaseModel(models.Model):
    created_at = models.DateTimeField(auto_now_add=True, db_index=True)
    updated_at = models.DateTimeField(auto_now=True)

    created_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        null=True,
        blank=True,
        on_delete=models.SET_NULL,
        related_name="%(class)s_created",
    )

    updated_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        null=True,
        blank=True,
        on_delete=models.SET_NULL,
        related_name="%(class)s_updated",
    )

    class Meta:
        abstract = True


class SoftDeleteQuerySet(models.QuerySet):  # type: ignore[type-arg]
    def alive(self) -> "SoftDeleteQuerySet":
        return self.filter(deleted_at__isnull=True)

    def deleted(self) -> "SoftDeleteQuerySet":
        return self.filter(deleted_at__isnull=False)


class SoftDeleteManager(models.Manager.from_queryset(SoftDeleteQuerySet)):  # type: ignore[misc]
    """Default manager: hides soft-deleted rows."""

    def get_queryset(self) -> SoftDeleteQuerySet:
        return super().get_queryset().alive()


class AllObjectsManager(models.Manager.from_queryset(SoftDeleteQuerySet)):  # type: ignore[misc]
    """Includes soft-deleted rows. Use `.alive()` / `.deleted()` to narrow."""


class SoftDeleteModel(BaseModel):
    """
    Rows are never removed by `soft_delete()`; they get a `deleted_at` timestamp.

    A soft-deleted order or bill is a *returned* sale, so statistics and the
    orders "returned" filter read deleted rows on purpose.

    Foreign keys pointing at a soft-deleted row still resolve, because Django
    follows relations with the base manager (which is not filtered).
    """

    deleted_at = models.DateTimeField(null=True, blank=True, db_index=True)

    objects = SoftDeleteManager()
    all_objects = AllObjectsManager()

    class Meta:
        abstract = True

    @property
    def is_deleted(self) -> bool:
        return self.deleted_at is not None

    def soft_delete(self, user: Any = None) -> None:
        self.deleted_at = timezone.now()
        fields = ["deleted_at", "updated_at"]
        if user is not None:
            self.updated_by = user
            fields.append("updated_by")
        self.save(update_fields=fields)

    def restore(self, user: Any = None) -> None:
        self.deleted_at = None
        fields = ["deleted_at", "updated_at"]
        if user is not None:
            self.updated_by = user
            fields.append("updated_by")
        self.save(update_fields=fields)
