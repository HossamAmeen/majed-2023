import logging
import secrets
from decimal import Decimal
from typing import Any

from django.db import transaction
from django.db.models import F
from django.utils import timezone

from apps.common.exceptions import BusinessRuleError
from apps.products.models import Product
from apps.products.selectors import ProductSelector
from apps.users.models import User
from utils.constants import PRODUCT_CODE_LENGTH

logger = logging.getLogger(__name__)

PRODUCT_FIELDS = ("name", "purchasing_price", "selling_price", "quantity")


class InsufficientStockError(BusinessRuleError):
    default_detail = "هذا المنتج اقل من الكمية المطلوبة او ناقص"


class ProductCodeService:
    MAX_ATTEMPTS = 50

    @classmethod
    def generate_unique_code(cls) -> int:
        """A random 5-digit code that no product (deleted ones included) has ever used."""
        low = 10 ** (PRODUCT_CODE_LENGTH - 1)
        high = 10**PRODUCT_CODE_LENGTH
        for _ in range(cls.MAX_ATTEMPTS):
            code = low + secrets.randbelow(high - low)
            if not ProductSelector.code_exists(code):
                return code
        raise BusinessRuleError("تعذر توليد كود جديد للمنتج، حاول مرة اخرى")


class ProductService:
    @staticmethod
    @transaction.atomic
    def create_product(*, actor: User, data: dict[str, Any]) -> Product:
        product = Product.objects.create(
            **{field: data[field] for field in PRODUCT_FIELDS if field in data},
            code=ProductCodeService.generate_unique_code(),
            created_by=actor,
            updated_by=actor,
        )
        logger.info("Product %s created by %s", product.code, actor.user_name)
        return product

    @staticmethod
    def update_product(*, actor: User, product: Product, data: dict[str, Any]) -> Product:
        for field in PRODUCT_FIELDS:
            if field in data:
                setattr(product, field, data[field])
        product.updated_by = actor
        product.save()
        return product

    @staticmethod
    def delete_product(*, actor: User, product: Product) -> None:
        product.soft_delete(user=actor)
        logger.info("Product %s deleted by %s", product.code, actor.user_name)


class StockService:
    """
    Every stock change goes through here, as a single conditional UPDATE, so two
    cashiers selling the last unit at the same time cannot push stock below zero.
    """

    @staticmethod
    def take(*, product_id: int | None, quantity: int, product_name: str) -> None:
        """Remove sold units. A soft-deleted or missing product has no stock to take."""
        if quantity <= 0:
            return
        updated = 0
        if product_id is not None:
            updated = Product.objects.filter(pk=product_id, quantity__gte=quantity).update(
                quantity=F("quantity") - quantity, updated_at=timezone.now()
            )
        if not updated:
            raise InsufficientStockError(f"هذا المنتج({product_name}) اقل من الكمية المطلوبة او ناقص")

    @staticmethod
    def give_back(
        *,
        product_id: int | None,
        quantity: int,
        product_name: str,
        selling_price: Decimal,
        actor: User,
    ) -> Product:
        """
        Return sold units to stock.

        - Product exists: add the units.
        - Product was soft-deleted: restore it holding only the returned units.
        - Product row is gone entirely: re-create it under a new code.
        """
        if product_id is not None:
            if Product.objects.filter(pk=product_id).update(
                quantity=F("quantity") + quantity, updated_at=timezone.now()
            ):
                return Product.objects.get(pk=product_id)

            deleted = Product.all_objects.filter(pk=product_id).first()
            if deleted is not None:
                deleted.deleted_at = None
                deleted.quantity = quantity
                deleted.updated_by = actor
                if ProductSelector.by_code(deleted.code) is not None:
                    deleted.code = ProductCodeService.generate_unique_code()
                deleted.save()
                logger.info("Product %s restored by a returned sale", deleted.pk)
                return deleted

        product = Product.objects.create(
            name=product_name,
            selling_price=selling_price,
            quantity=quantity,
            code=ProductCodeService.generate_unique_code(),
            created_by=actor,
            updated_by=actor,
        )
        logger.info("Product %s re-created by a returned sale", product.code)
        return product
