from django.db import models

from apps.common.models import BaseModel


class Note(BaseModel):
    note = models.TextField()

    class Meta:
        ordering = ["-id"]

    def __str__(self) -> str:
        return self.note[:50]
