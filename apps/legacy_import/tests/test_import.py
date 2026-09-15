from datetime import UTC, date, datetime
from decimal import Decimal
from typing import Any

import bcrypt
import pytest
from django.contrib.auth import authenticate

from apps.dashboard.models import ShopConfiguration
from apps.legacy_import.services import LegacyImportError, LegacyImportService
from apps.notes.models import Note
from apps.products.models import Product
from apps.sales.models import Bill, Order
from apps.users.models import User
from apps.users.tests.factories import UserFactory

pytestmark = pytest.mark.django_db

CREATED = datetime(2024, 3, 1, 9, 30)


def laravel_hash(password: str) -> str:
    return "$2y$" + bcrypt.hashpw(password.encode(), bcrypt.gensalt(4)).decode()[4:]


class InMemorySource:
    def __init__(self, tables: dict[str, list[dict[str, Any]]]) -> None:
        self.tables = tables

    def has_table(self, table: str) -> bool:
        return table in self.tables

    def rows(self, table: str) -> list[dict[str, Any]]:
        return self.tables[table]


def legacy_tables() -> dict[str, list[dict[str, Any]]]:
    return {
        "users": [
            {
                "id": 1,
                "user_name": "admin",
                "full_name": "admin",
                "email": "a@a.com",
                "phone": None,
                "password": laravel_hash("admin"),
                "role": 1,
                "user_id": 1,
                "created_at": CREATED,
                "updated_at": CREATED,
                "deleted_at": None,
            },
            {
                "id": 7,
                "user_name": "old",
                "full_name": "old",
                "email": "o@a.com",
                "phone": "010",
                "password": laravel_hash("x"),
                "role": 3,
                "user_id": 1,
                "created_at": CREATED,
                "updated_at": CREATED,
                "deleted_at": CREATED,
            },
        ],
        "products": [
            {
                "id": 3,
                "name": "قميص",
                "purchasing_price": 50.0,
                "selling_price": 99.5,
                "total_price": 0,
                "quantity": 4,
                "code": 12345,
                "discount": None,
                "user_id": 1,
                "category_id": None,
                "created_at": CREATED,
                "updated_at": CREATED,
                "deleted_at": None,
            },
            {
                "id": 4,
                "name": "مكرر",
                "purchasing_price": None,
                "selling_price": None,
                "total_price": None,
                "quantity": None,
                "code": 12345,
                "discount": None,
                "user_id": 99,
                "category_id": None,
                "created_at": CREATED,
                "updated_at": CREATED,
                "deleted_at": None,
            },
        ],
        "bills": [
            {
                "id": 10,
                "name": "عميل",
                "phone": None,
                "is_selling": 0,
                "discount": 5.0,
                "user_id": 1,
                "created_at": CREATED,
                "updated_at": CREATED,
                "deleted_at": None,
            },
        ],
        "orders": [
            {
                "id": 20,
                "product_price": 99,
                "price": 99.5,
                "quantity": 2,
                "date": date(2024, 3, 1),
                "status": "sold-out",
                "product_name": "قميص",
                "discount": 0,
                "product_id": 3,
                "bill_id": 10,
                "user_id": 1,
                "created_at": CREATED,
                "updated_at": CREATED,
                "deleted_at": None,
            },
            {
                "id": 21,
                "product_price": 10,
                "price": 10,
                "quantity": 1,
                "date": "2024-03-02",
                "status": "sold-out",
                "product_name": "محذوف",
                "discount": 0,
                "product_id": 555,
                "bill_id": 404,
                "user_id": None,
                "created_at": CREATED,
                "updated_at": CREATED,
                "deleted_at": CREATED,
            },
        ],
        "notes": [
            {"id": 1, "note": "ملحوظة", "user_id": 1, "created_at": CREATED, "updated_at": CREATED},
        ],
        "configrations": [
            {"id": 1, "title": "محل", "phone": "0100", "address": "القاهرة", "email": None},
        ],
    }


class TestLegacyImport:
    @pytest.fixture(autouse=True)
    def setup(self):
        self.service = LegacyImportService(InMemorySource(legacy_tables()))

    def test_imports_all_tables_keeping_ids_timestamps_and_soft_deletes(self):
        stats = self.service.run()

        assert stats == {"users": 2, "products": 2, "bills": 1, "orders": 2, "notes": 1, "configuration": 1}
        admin = User.objects.get(pk=1)
        assert admin.is_superuser and admin.role == 1
        assert admin.created_at == CREATED.replace(tzinfo=UTC)
        assert User.all_objects.get(pk=7).is_deleted
        assert Product.objects.get(pk=3).selling_price == Decimal("99.50")
        assert Order.all_objects.get(pk=20).bill_id == 10
        assert Bill.objects.get(pk=10).created_by_id == 1
        assert Note.objects.get(pk=1).created_by_id == 1
        assert ShopConfiguration.load().address == "القاهرة"

    def test_laravel_passwords_keep_working(self):
        self.service.run()

        assert authenticate(username="admin", password="admin") is not None
        assert authenticate(username="admin", password="wrong") is None

    def test_duplicate_codes_and_missing_relations_are_repaired(self):
        self.service.run()

        assert Product.objects.get(pk=4).code != 12345
        assert Product.objects.get(pk=4).created_by_id is None
        orphan = Order.all_objects.get(pk=21)
        assert orphan.product_id is None and orphan.bill_id is None
        assert orphan.date == date(2024, 3, 2)

    def test_refuses_to_import_into_a_database_with_data(self):
        UserFactory()

        with pytest.raises(LegacyImportError):
            self.service.run()
