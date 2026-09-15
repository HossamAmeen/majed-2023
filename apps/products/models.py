from django.db import models
from django.db.models import Q

from apps.common.models import SoftDeleteModel
from utils.helpers import money_field


class Product(SoftDeleteModel):
    name = models.CharField(max_length=255, db_index=True)
    purchasing_price = money_field(default=0)
    selling_price = money_field(default=0)
    quantity = models.IntegerField(default=0, help_text="Units in stock")
    code = models.PositiveBigIntegerField(help_text="Barcode number, generated on create")

    class Meta:
        ordering = ["-id"]
        constraints = [
            models.UniqueConstraint(
                fields=["code"],
                condition=Q(deleted_at__isnull=True),
                name="unique_alive_product_code",
            ),
        ]

    def __str__(self) -> str:
        return f"{self.name} ({self.code})"
