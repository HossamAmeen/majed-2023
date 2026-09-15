from datetime import date, timedelta
from decimal import Decimal
from typing import Any

from django.db.models import Count, F, QuerySet, Value
from django.db.models.functions import Coalesce
from django.utils import timezone

from apps.products.models import Product
from apps.sales.models import Bill, Order
from apps.sales.selectors import line_total_expression, money_sum
from utils.helpers import money_output_field


class StatisticsSelector:
    """
    Sales figures for the dashboard.

    - "Orders money" is quantity * price - line discount, for sold (not returned) lines.
    - "Goods cost" is quantity * the product's current purchasing price.
    - Returned figures read soft-deleted orders and bills.
    - Periods use the order's sale date, and the bill's creation date for bill discounts.
    """

    @staticmethod
    def _sold_orders() -> QuerySet[Order]:
        return Order.objects.filter(status=Order.Status.SOLD_OUT)

    @staticmethod
    def _order_figures(queryset: QuerySet[Order]) -> dict[str, Any]:
        money = money_output_field()
        return queryset.order_by().aggregate(
            orders_count=Count("id"),
            orders_money=money_sum(line_total_expression()),
            orders_discount=money_sum("discount"),
            goods_cost=money_sum(
                F("quantity") * Coalesce(F("product__purchasing_price"), Value(0), output_field=money)
            ),
        )

    @staticmethod
    def _bills_discount(queryset: QuerySet[Bill], day: date) -> Decimal:
        return queryset.filter(created_at__date=day).aggregate(total=money_sum("discount"))["total"]  # type: ignore[no-any-return]

    @classmethod
    def _day(cls, day: date) -> dict[str, Any]:
        figures = cls._order_figures(cls._sold_orders().filter(date=day))
        bills_discount = cls._bills_discount(Bill.objects.all(), day)
        return {
            **figures,
            "bills_discount": bills_discount,
            "total_discount": figures["orders_discount"] + bills_discount,
            "net_profit": figures["orders_money"] - figures["goods_cost"] - bills_discount,
        }

    @classmethod
    def _period(cls, queryset: QuerySet[Order]) -> dict[str, Any]:
        figures = cls._order_figures(queryset)
        return {"orders_count": figures["orders_count"], "orders_money": figures["orders_money"]}

    @classmethod
    def overview(cls, today: date | None = None) -> dict[str, Any]:
        today = today or timezone.localdate()
        sold = cls._sold_orders()

        returned_today = cls._order_figures(
            Order.all_objects.deleted().filter(status=Order.Status.SOLD_OUT, date=today)
        )
        returned_bills_discount = cls._bills_discount(Bill.all_objects.deleted(), today)

        return {
            "date": today,
            "today": {
                **cls._day(today),
                "returned_count": returned_today["orders_count"],
                "returned_money": returned_today["orders_money"] - returned_bills_discount,
            },
            "yesterday": cls._day(today - timedelta(days=1)),
            "month": cls._period(sold.filter(date__year=today.year, date__month=today.month)),
            "year": cls._period(sold.filter(date__year=today.year)),
            "out_of_stock_products": Product.objects.filter(quantity__lte=0).count(),
        }
