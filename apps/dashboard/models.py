from typing import Self

from django.db import models

from apps.common.models import BaseModel

DEFAULT_SHOP_TITLE = "جولدن فنجرز للملابس الجاهزه"
DEFAULT_RECEIPT_HEADER = "س . تجارى :49203   ب . ض : 11276   ملف ض :3/6/527/4890/5"
DEFAULT_RECEIPT_FOOTER = (
    "سياسة الإستبدال و الإسترجاع لجهاز حماية المستهلك 14 يوم المنتج فى حالته الأصليه "
    "بأصل الفاتوره و 30 يوم عند وجود عيب صناعه"
)


class ShopConfiguration(BaseModel):
    """Single row (pk=1) with the shop details printed on receipts."""

    SINGLETON_PK = 1

    title = models.CharField("shop name", max_length=255, default=DEFAULT_SHOP_TITLE)
    phone = models.CharField(max_length=50, blank=True)
    address = models.CharField(max_length=255, blank=True)
    email = models.EmailField(blank=True)
    receipt_header = models.CharField(
        max_length=255,
        blank=True,
        default=DEFAULT_RECEIPT_HEADER,
        help_text="Commercial register / tax numbers under the shop name",
    )
    receipt_footer = models.TextField(
        blank=True, default=DEFAULT_RECEIPT_FOOTER, help_text="Return policy text"
    )

    class Meta:
        verbose_name = "shop configuration"

    def __str__(self) -> str:
        return self.title

    def save(self, *args: object, **kwargs: object) -> None:
        self.pk = self.SINGLETON_PK
        super().save(*args, **kwargs)  # type: ignore[arg-type]

    @classmethod
    def load(cls) -> Self:
        instance, _ = cls.objects.get_or_create(pk=cls.SINGLETON_PK)
        return instance
