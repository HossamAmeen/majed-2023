import logging
from dataclasses import dataclass
from decimal import Decimal
from typing import Any

from django.db import transaction
from django.utils import timezone

from apps.common.exceptions import BusinessRuleError
from apps.products.selectors import ProductSelector
from apps.products.services import StockService
from apps.sales.models import Bill, Order
from apps.sales.selectors import BillSelector
from apps.users.models import User

logger = logging.getLogger(__name__)

BILL_FIELDS = ("name", "phone", "discount")


@dataclass(frozen=True)
class BillLine:
    code: int
    quantity: int
    discount: Decimal = Decimal(0)
    price: Decimal | None = None


class BillService:
    @staticmethod
    @transaction.atomic
    def create_bill(*, actor: User, data: dict[str, Any], lines: list[BillLine]) -> Bill:
        """
        Sell the scanned products: take them out of stock and write one order per line.

        Runs in one transaction, so an unknown code or missing stock leaves nothing behind.
        """
        if not lines:
            raise BusinessRuleError("لا يوجد منتجات في هذه الفاتوره")

        products = ProductSelector.by_codes(line.code for line in lines)
        bill = Bill.objects.create(
            **{field: data[field] for field in BILL_FIELDS if field in data},
            created_by=actor,
            updated_by=actor,
        )

        today = timezone.localdate()
        orders: list[Order] = []
        for line in lines:
            product = products.get(line.code)
            if product is None:
                raise BusinessRuleError(f"كود هذا المنتج غير صالح {line.code}")

            price = product.selling_price if line.price is None else line.price
            if line.discount > price * line.quantity:
                raise BusinessRuleError(f"الخصم على المنتج ({product.name}) اكبر من سعره")

            StockService.take(product_id=product.pk, quantity=line.quantity, product_name=product.name)
            orders.append(
                Order(
                    bill=bill,
                    product=product,
                    product_name=product.name,
                    product_price=product.selling_price,
                    price=price,
                    quantity=line.quantity,
                    discount=line.discount,
                    date=today,
                    created_by=actor,
                    updated_by=actor,
                )
            )

        Order.objects.bulk_create(orders)
        BillService._ensure_bill_discount_fits(bill)
        logger.info("Bill %s created by %s with %s lines", bill.pk, actor.user_name, len(orders))
        return bill

    @staticmethod
    @transaction.atomic
    def update_bill(
        *,
        actor: User,
        bill: Bill,
        data: dict[str, Any],
        order_quantities: dict[int, int],
    ) -> Bill:
        """
        Edit customer data, the bill discount and line quantities.

        Raising a quantity takes more stock; lowering it returns stock. A quantity of 0
        returns the whole line, and a bill left with no lines is returned too.
        """
        for field in BILL_FIELDS:
            if field in data:
                setattr(bill, field, data[field])
        bill.updated_by = actor
        bill.save()

        orders = {order.pk: order for order in Order.objects.filter(bill=bill)}
        for order_id, new_quantity in order_quantities.items():
            order = orders.get(order_id)
            if order is None:
                raise BusinessRuleError(f"الطلب رقم {order_id} غير موجود في هذه الفاتورة")

            difference = new_quantity - order.quantity
            if difference > 0:
                StockService.take(
                    product_id=order.product_id, quantity=difference, product_name=order.product_name
                )
            elif difference < 0:
                product = StockService.give_back(
                    product_id=order.product_id,
                    quantity=-difference,
                    product_name=order.product_name,
                    selling_price=order.product_price,
                    actor=actor,
                )
                order.product = product

            if new_quantity == 0:
                order.soft_delete(user=actor)
                continue

            if order.discount > order.price * new_quantity:
                raise BusinessRuleError(f"الخصم على المنتج ({order.product_name}) اكبر من سعره")
            order.quantity = new_quantity
            order.updated_by = actor
            order.save()

        if not Order.objects.filter(bill=bill).exists():
            bill.soft_delete(user=actor)
        else:
            BillService._ensure_bill_discount_fits(bill)
        return bill

    @staticmethod
    @transaction.atomic
    def delete_bill(*, actor: User, bill: Bill) -> None:
        """Return the whole bill: every line goes back to stock."""
        for order in Order.objects.filter(bill=bill):
            OrderService.return_line(actor=actor, order=order)
        bill.soft_delete(user=actor)
        logger.info("Bill %s returned by %s", bill.pk, actor.user_name)

    @staticmethod
    def _ensure_bill_discount_fits(bill: Bill) -> None:
        if bill.discount > BillSelector.lines_total(bill):
            raise BusinessRuleError("خصم الفاتورة اكبر من اجمالي الفاتورة")


class OrderService:
    @staticmethod
    def return_line(*, actor: User, order: Order) -> None:
        StockService.give_back(
            product_id=order.product_id,
            quantity=order.quantity,
            product_name=order.product_name,
            selling_price=order.product_price,
            actor=actor,
        )
        order.soft_delete(user=actor)

    @staticmethod
    @transaction.atomic
    def return_order(*, actor: User, order: Order) -> None:
        """Return one line. If it was the bill's last line, the bill is returned too."""
        bill = order.bill
        OrderService.return_line(actor=actor, order=order)
        if bill is not None and not bill.is_deleted and not Order.objects.filter(bill=bill).exists():
            bill.soft_delete(user=actor)
        logger.info("Order %s returned by %s", order.pk, actor.user_name)
