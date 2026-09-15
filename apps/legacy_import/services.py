import logging
from collections.abc import Iterator
from contextlib import contextmanager
from datetime import UTC, date, datetime
from decimal import ROUND_HALF_UP, Decimal
from typing import Any

from django.db import models, transaction
from django.utils import timezone
from django.utils.dateparse import parse_date, parse_datetime

from apps.dashboard.models import ShopConfiguration
from apps.legacy_import.sources import LegacySource
from apps.notes.models import Note
from apps.products.models import Product
from apps.products.services import ProductCodeService
from apps.sales.models import Bill, Order
from apps.users.models import User
from utils.enums import Role

logger = logging.getLogger(__name__)

CENT = Decimal("0.01")
IMPORTED_MODELS: tuple[type[models.Model], ...] = (User, Product, Bill, Order, Note, ShopConfiguration)


class LegacyImportError(Exception):
    pass


@contextmanager
def keep_imported_timestamps(*model_classes: type[models.Model]) -> Iterator[None]:
    """Stop auto_now / auto_now_add from overwriting the timestamps we copy over."""
    patched: list[tuple[models.Field, bool, bool]] = []  # type: ignore[type-arg]
    for model in model_classes:
        for field in model._meta.fields:
            if getattr(field, "auto_now", False) or getattr(field, "auto_now_add", False):
                patched.append((field, field.auto_now, field.auto_now_add))  # type: ignore[attr-defined]
                field.auto_now = field.auto_now_add = False  # type: ignore[attr-defined]
    try:
        yield
    finally:
        for field, auto_now, auto_now_add in patched:
            field.auto_now, field.auto_now_add = auto_now, auto_now_add  # type: ignore[attr-defined]


def to_datetime(value: Any) -> datetime | None:
    """Laravel stored naive UTC timestamps (config/app.php timezone = UTC)."""
    if value in (None, ""):
        return None
    if isinstance(value, str):
        value = parse_datetime(value)
        if value is None:
            return None
    if isinstance(value, date) and not isinstance(value, datetime):
        value = datetime.combine(value, datetime.min.time())
    if timezone.is_naive(value):
        value = value.replace(tzinfo=UTC)
    return value  # type: ignore[no-any-return]


def to_date(value: Any) -> date | None:
    if value in (None, ""):
        return None
    if isinstance(value, datetime):
        return value.date()
    if isinstance(value, date):
        return value
    return parse_date(str(value)[:10])


def to_money(value: Any) -> Decimal:
    if value in (None, ""):
        return Decimal(0)
    return Decimal(str(value)).quantize(CENT, rounding=ROUND_HALF_UP)


def to_int(value: Any, default: int = 0) -> int:
    if value in (None, ""):
        return default
    return int(float(value))


def to_django_password(laravel_hash: str | None) -> str:
    """Laravel bcrypt ($2y$) hashes are standard bcrypt; Django reads them as "bcrypt$$2b$..."."""
    if laravel_hash and laravel_hash.startswith(("$2y$", "$2a$", "$2b$")):
        return f"bcrypt$$2b${laravel_hash[4:]}"
    return "!"  # unusable password; the user must reset it


class LegacyImportService:
    """
    Copies users, products, bills, orders, notes and the shop configuration from the
    old Laravel database, keeping ids, timestamps and soft deletes. Runs in a single
    transaction and refuses to run on a database that already has data.
    """

    def __init__(self, source: LegacySource) -> None:
        self.source = source
        self.stats: dict[str, int] = {}
        self._user_ids: set[int] = set()
        self._product_ids: set[int] = set()
        self._bill_ids: set[int] = set()

    def run(self) -> dict[str, int]:
        self._ensure_target_is_empty()
        with transaction.atomic(), keep_imported_timestamps(*IMPORTED_MODELS):
            self._import_users()
            self._import_products()
            self._import_bills()
            self._import_orders()
            self._import_notes()
            self._import_configuration()
        return self.stats

    def _ensure_target_is_empty(self) -> None:
        managers: dict[str, Any] = {
            "users": User.all_objects,
            "products": Product.all_objects,
            "bills": Bill.all_objects,
            "orders": Order.all_objects,
            "notes": Note.objects,
        }
        for table, manager in managers.items():
            if manager.exists():
                raise LegacyImportError(f"The {table} table is not empty. Import into a fresh database.")

    def _audit(self, row: dict[str, Any]) -> dict[str, Any]:
        user_id = to_int(row.get("user_id"), default=0) or None
        if user_id not in self._user_ids:
            user_id = None
        return {
            "created_at": to_datetime(row.get("created_at")) or timezone.now(),
            "updated_at": to_datetime(row.get("updated_at")) or timezone.now(),
            "created_by_id": user_id,
            "updated_by_id": user_id,
        }

    def _import_users(self) -> None:
        rows = self.source.rows("users")
        seen_names: set[str] = set()
        users: list[User] = []
        for row in rows:
            user_name = (row.get("user_name") or f"user{row['id']}").strip()
            if user_name.lower() in seen_names:
                logger.warning("Duplicate user_name %r renamed for legacy user %s", user_name, row["id"])
                user_name = f"{user_name}_{row['id']}"
            seen_names.add(user_name.lower())

            role = to_int(row.get("role"), default=Role.EMPLOYEE)
            users.append(
                User(
                    id=row["id"],
                    user_name=user_name,
                    full_name=row.get("full_name") or user_name,
                    email=row.get("email") or "",
                    phone=row.get("phone") or "",
                    role=role if role in Role.values else Role.EMPLOYEE,
                    is_superuser=role == Role.MASTER,
                    password=to_django_password(row.get("password")),
                    created_at=to_datetime(row.get("created_at")) or timezone.now(),
                    updated_at=to_datetime(row.get("updated_at")) or timezone.now(),
                    deleted_at=to_datetime(row.get("deleted_at")),
                )
            )
        User.all_objects.bulk_create(users)
        self._user_ids = {user.pk for user in users}

        creators = []
        for row, user in zip(rows, users, strict=True):
            creator_id = to_int(row.get("user_id"), default=0)
            if creator_id in self._user_ids:
                user.created_by_id = user.updated_by_id = creator_id
                creators.append(user)
        User.all_objects.bulk_update(creators, ["created_by_id", "updated_by_id"])
        self.stats["users"] = len(users)

    def _import_products(self) -> None:
        products: list[Product] = []
        alive_codes: set[int] = set()
        for row in self.source.rows("products"):
            deleted_at = to_datetime(row.get("deleted_at"))
            code = to_int(row.get("code"), default=0)
            if not code or (deleted_at is None and code in alive_codes):
                new_code = ProductCodeService.generate_unique_code()
                while new_code in alive_codes:
                    new_code = ProductCodeService.generate_unique_code()
                logger.warning("Legacy product %s: code %s replaced by %s", row["id"], code, new_code)
                code = new_code
            if deleted_at is None:
                alive_codes.add(code)

            products.append(
                Product(
                    id=row["id"],
                    name=row.get("name") or "",
                    purchasing_price=to_money(row.get("purchasing_price")),
                    selling_price=to_money(row.get("selling_price")),
                    quantity=to_int(row.get("quantity")),
                    code=code,
                    deleted_at=deleted_at,
                    **self._audit(row),
                )
            )
        Product.all_objects.bulk_create(products, batch_size=500)
        self._product_ids = {product.pk for product in products}
        self.stats["products"] = len(products)

    def _import_bills(self) -> None:
        bills = [
            Bill(
                id=row["id"],
                name=row.get("name") or "",
                phone=row.get("phone") or "",
                discount=to_money(row.get("discount")),
                deleted_at=to_datetime(row.get("deleted_at")),
                **self._audit(row),
            )
            for row in self.source.rows("bills")
        ]
        Bill.all_objects.bulk_create(bills, batch_size=500)
        self._bill_ids = {bill.pk for bill in bills}
        self.stats["bills"] = len(bills)

    def _import_orders(self) -> None:
        orders: list[Order] = []
        for row in self.source.rows("orders"):
            audit = self._audit(row)
            product_id = to_int(row.get("product_id"), default=0)
            bill_id = to_int(row.get("bill_id"), default=0)
            orders.append(
                Order(
                    id=row["id"],
                    bill_id=bill_id if bill_id in self._bill_ids else None,
                    product_id=product_id if product_id in self._product_ids else None,
                    product_name=row.get("product_name") or "",
                    product_price=to_money(row.get("product_price")),
                    price=to_money(row.get("price")),
                    quantity=max(to_int(row.get("quantity")), 0),
                    discount=to_money(row.get("discount")),
                    date=to_date(row.get("date")) or audit["created_at"].date(),
                    status=row.get("status") or Order.Status.SOLD_OUT,
                    deleted_at=to_datetime(row.get("deleted_at")),
                    **audit,
                )
            )
        Order.all_objects.bulk_create(orders, batch_size=500)
        self.stats["orders"] = len(orders)

    def _import_notes(self) -> None:
        if not self.source.has_table("notes"):
            self.stats["notes"] = 0
            return
        notes = [
            Note(id=row["id"], note=row.get("note") or "", **self._audit(row))
            for row in self.source.rows("notes")
        ]
        Note.objects.bulk_create(notes, batch_size=500)
        self.stats["notes"] = len(notes)

    def _import_configuration(self) -> None:
        if not self.source.has_table("configrations"):
            return
        row = next((r for r in self.source.rows("configrations") if r["id"] == 1), None)
        if row is None:
            return
        configuration = ShopConfiguration.load()
        for field in ("title", "phone", "address", "email"):
            if row.get(field):
                setattr(configuration, field, str(row[field]).strip())
        configuration.updated_at = timezone.now()
        configuration.save()
        self.stats["configuration"] = 1
