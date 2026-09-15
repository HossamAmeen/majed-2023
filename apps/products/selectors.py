from collections.abc import Iterable
from typing import TypedDict

from django.db.models import Count, F, QuerySet, Sum, Value
from django.db.models.functions import Coalesce

from apps.products.models import Product
from utils.helpers import money_output_field


class ProductSummary(TypedDict):
    products_count: int
    total_quantity: int
    total_purchasing_price: object
    total_selling_price: object


class ProductSelector:
    @staticmethod
    def list() -> QuerySet[Product]:
        return Product.objects.select_related("created_by", "updated_by").order_by("-id")

    @staticmethod
    def summary(queryset: QuerySet[Product]) -> ProductSummary:
        money = money_output_field()
        totals = queryset.order_by().aggregate(
            products_count=Count("id"),
            total_quantity=Coalesce(Sum("quantity"), 0),
            total_purchasing_price=Coalesce(
                Sum(F("quantity") * F("purchasing_price"), output_field=money),
                Value(0),
                output_field=money,
            ),
            total_selling_price=Coalesce(
                Sum(F("quantity") * F("selling_price"), output_field=money),
                Value(0),
                output_field=money,
            ),
        )
        return ProductSummary(**totals)  # type: ignore[typeddict-item]

    @staticmethod
    def by_code(code: int) -> Product | None:
        return Product.objects.filter(code=code).first()

    @staticmethod
    def by_codes(codes: Iterable[int]) -> dict[int, Product]:
        return {product.code: product for product in Product.objects.filter(code__in=set(codes))}

    @staticmethod
    def created_in_year(year: int) -> QuerySet[Product]:
        return Product.objects.filter(created_at__year=year).select_related("created_by").order_by("-id")

    @staticmethod
    def code_exists(code: int) -> bool:
        return Product.all_objects.filter(code=code).exists()
