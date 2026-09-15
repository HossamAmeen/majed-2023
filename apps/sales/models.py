from django.db import models

from apps.common.models import SoftDeleteModel
from apps.products.models import Product
from utils.helpers import money_field


class Bill(SoftDeleteModel):
    """An invoice. Soft-deleting a bill means the whole sale was returned."""

    name = models.CharField("customer name", max_length=255, blank=True, db_index=True)
    phone = models.CharField(max_length=30, blank=True)
    discount = money_field(default=0, help_text="Discount on the whole bill")

    class Meta:
        ordering = ["-id"]

    def __str__(self) -> str:
        return f"#{self.pk} {self.name}"


class Order(SoftDeleteModel):
    """
    One bill line. Product name and prices are copied at sale time so later product
    edits do not change past bills. A soft-deleted order is a returned item.
    """

    class Status(models.TextChoices):
        SOLD_OUT = "sold-out", "مباع"

    bill = models.ForeignKey(Bill, null=True, blank=True, on_delete=models.SET_NULL, related_name="orders")
    product = models.ForeignKey(
        Product, null=True, blank=True, on_delete=models.SET_NULL, related_name="orders"
    )
    product_name = models.CharField(max_length=255)
    product_price = money_field(default=0, help_text="Product selling price at sale time")
    price = money_field(help_text="Unit price actually charged")
    quantity = models.PositiveIntegerField()
    discount = money_field(default=0, help_text="Discount on this line (not per unit)")
    date = models.DateField(db_index=True)
    status = models.CharField(max_length=20, choices=Status.choices, default=Status.SOLD_OUT)

    class Meta:
        ordering = ["-id"]
        indexes = [models.Index(fields=["status", "date"], name="order_status_date_idx")]

    def __str__(self) -> str:
        return f"{self.product_name} x{self.quantity}"
