from datetime import date
from decimal import Decimal

import pytest
from django.urls import reverse
from freezegun import freeze_time
from rest_framework import status

from apps.sales.models import Bill
from apps.sales.tests.factories import BillFactory, OrderFactory

pytestmark = pytest.mark.django_db


class TestOrderList:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee):
        self.client = auth_client(employee)
        self.url = reverse("v1:orders-list")
        self.summary_url = reverse("v1:orders-summary")

    def test_lists_sold_orders_by_default(self):
        sold = OrderFactory()
        OrderFactory().soft_delete()

        response = self.client.get(self.url)

        assert response.status_code == status.HTTP_200_OK
        assert [row["id"] for row in response.json()["results"]] == [sold.pk]

    def test_returned_filter_lists_only_deleted_orders(self):
        OrderFactory()
        returned = OrderFactory()
        returned.soft_delete()

        response = self.client.get(self.url, {"returned": "true"})

        rows = response.json()["results"]
        assert [row["id"] for row in rows] == [returned.pk]
        assert rows[0]["deleted_at"] is not None

    def test_date_filters(self):
        OrderFactory(date=date(2026, 1, 10))
        in_range = OrderFactory(date=date(2026, 2, 10))

        by_month = self.client.get(self.url, {"year": 2026, "month": 2}).json()["results"]
        by_from = self.client.get(self.url, {"date_from": "2026-02-01"}).json()["results"]

        assert [row["id"] for row in by_month] == [in_range.pk]
        assert [row["id"] for row in by_from] == [in_range.pk]

    def test_summary_uses_the_same_filters(self):
        OrderFactory(price=Decimal("100"), quantity=2, discount=Decimal("20"))
        OrderFactory(price=Decimal("50"), quantity=1)
        OrderFactory(price=Decimal("999"), quantity=1).soft_delete()

        response = self.client.get(self.summary_url)

        assert response.json() == {"orders_count": 2, "total_quantity": 3, "total_money": 230}

    def test_marks_orders_of_deleted_products(self):
        order = OrderFactory()
        order.product.soft_delete()

        row = self.client.get(self.url).json()["results"][0]

        assert row["product_deleted"] is True
        assert row["product_code"] == order.product.code


class TestOrderReturn:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee):
        self.client = auth_client(employee)

    def test_returns_stock_and_keeps_bill_with_other_lines(self):
        bill = BillFactory()
        order = OrderFactory(bill=bill, quantity=2)
        OrderFactory(bill=bill)
        stock_before = order.product.quantity

        response = self.client.delete(reverse("v1:orders-detail", args=[order.pk]))

        assert response.status_code == status.HTTP_204_NO_CONTENT
        order.product.refresh_from_db()
        assert order.product.quantity == stock_before + 2
        assert not Bill.all_objects.get(pk=bill.pk).is_deleted

    def test_returning_last_line_returns_the_bill(self):
        order = OrderFactory()

        self.client.delete(reverse("v1:orders-detail", args=[order.pk]))

        assert Bill.all_objects.get(pk=order.bill_id).is_deleted

    def test_already_returned_order_is_not_found(self):
        order = OrderFactory()
        order.soft_delete()

        response = self.client.delete(reverse("v1:orders-detail", args=[order.pk]))

        assert response.status_code == status.HTTP_404_NOT_FOUND

    @freeze_time("2026-03-01 10:00:00")
    def test_returned_order_keeps_its_sale_date(self):
        order = OrderFactory(date=date(2026, 2, 27))

        self.client.delete(reverse("v1:orders-detail", args=[order.pk]))

        row = self.client.get(reverse("v1:orders-list"), {"returned": "true"}).json()["results"][0]
        assert row["date"] == "2026-02-27"
        assert row["deleted_at"].startswith("2026-03-01")
