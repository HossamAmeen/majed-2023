from decimal import Decimal
from typing import Any, TypedDict

from django.db.models import (
    Count,
    ExpressionWrapper,
    F,
    Prefetch,
    Q,
    QuerySet,
    Sum,
    Value,
)
from django.db.models.functions import Coalesce

from apps.common.models import SoftDeleteQuerySet
from apps.sales.models import Bill, Order
from utils.helpers import money_output_field


def line_total_expression(prefix: str = "") -> ExpressionWrapper:
    """quantity * price - discount for an order line (the money actually collected)."""
    return ExpressionWrapper(
        F(f"{prefix}quantity") * F(f"{prefix}price") - F(f"{prefix}discount"),
        output_field=money_output_field(),
    )


def money_sum(expression: Any, filter: Q | None = None) -> Coalesce:  # mirrors Sum(filter=)
    money = money_output_field()
    return Coalesce(Sum(expression, output_field=money, filter=filter), Value(0), output_field=money)


class OrderTotals(TypedDict):
    orders_count: int
    total_quantity: int
    total_money: object


class BillSelector:
    @staticmethod
    def list(include_deleted: bool = False) -> QuerySet[Bill]:
        """Bills with their alive orders and money totals annotated."""
        alive_orders = Q(orders__deleted_at__isnull=True)
        base = Bill.all_objects.all() if include_deleted else Bill.objects.all()
        return (
            base.select_related("created_by", "updated_by")
            .prefetch_related(
                Prefetch("orders", queryset=Order.objects.select_related("product").order_by("id"))
            )
            .annotate(
                subtotal=money_sum(F("orders__quantity") * F("orders__price"), filter=alive_orders),
                orders_discount=money_sum("orders__discount", filter=alive_orders),
            )
            .annotate(
                total_discount=ExpressionWrapper(
                    F("orders_discount") + F("discount"), output_field=money_output_field()
                ),
            )
            .annotate(
                net_total=ExpressionWrapper(
                    F("subtotal") - F("total_discount"), output_field=money_output_field()
                ),
            )
            .order_by("-id")
        )

    @classmethod
    def detail(cls, pk: int) -> Bill:
        return cls.list(include_deleted=True).get(pk=pk)

    @staticmethod
    def lines_total(bill: Bill) -> Decimal:
        totals = Order.objects.filter(bill=bill).aggregate(total=money_sum(line_total_expression()))
        return totals["total"]  # type: ignore[no-any-return]


class OrderSelector:
    @staticmethod
    def all_including_returned() -> SoftDeleteQuerySet:
        return Order.all_objects.select_related("product", "bill", "created_by").order_by("-id")

    @staticmethod
    def alive() -> QuerySet[Order]:
        return Order.objects.select_related("product", "bill")

    @staticmethod
    def totals(queryset: QuerySet[Order]) -> OrderTotals:
        return queryset.order_by().aggregate(  # type: ignore[return-value]
            orders_count=Count("id"),
            total_quantity=Coalesce(Sum("quantity"), 0),
            total_money=money_sum(line_total_expression()),
        )
