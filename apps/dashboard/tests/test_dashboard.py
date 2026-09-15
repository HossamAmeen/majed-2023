from datetime import date
from decimal import Decimal

import pytest
from django.urls import reverse
from freezegun import freeze_time
from rest_framework import status

from apps.products.tests.factories import ProductFactory
from apps.sales.tests.factories import BillFactory, OrderFactory

pytestmark = pytest.mark.django_db

TODAY = date(2026, 5, 20)
YESTERDAY = date(2026, 5, 19)


@freeze_time("2026-05-20 12:00:00")
class TestStatistics:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, admin_user):
        self.client = auth_client(admin_user)
        self.url = reverse("v1:statistics")

    def test_figures(self):
        product = ProductFactory(purchasing_price=Decimal("40"))
        today_bill = BillFactory(discount=Decimal("10"))
        OrderFactory(
            bill=today_bill,
            product=product,
            price=Decimal("100"),
            quantity=2,
            discount=Decimal("5"),
            date=TODAY,
        )
        OrderFactory(product=product, price=Decimal("100"), quantity=1, date=TODAY).soft_delete()
        OrderFactory(product=product, price=Decimal("50"), quantity=1, date=YESTERDAY)
        OrderFactory(product=product, price=Decimal("30"), quantity=1, date=date(2026, 5, 1))
        OrderFactory(product=product, price=Decimal("70"), quantity=1, date=date(2026, 1, 1))
        ProductFactory(quantity=0)

        response = self.client.get(self.url)

        assert response.status_code == status.HTTP_200_OK
        body = response.json()
        assert body["today"] == {
            "orders_count": 1,
            "orders_money": 195,
            "orders_discount": 5,
            "bills_discount": 10,
            "total_discount": 15,
            "goods_cost": 80,
            "net_profit": 105,
            "returned_count": 1,
            "returned_money": 100,
        }
        assert body["yesterday"]["orders_count"] == 1
        assert body["yesterday"]["orders_money"] == 50
        assert body["month"] == {"orders_count": 3, "orders_money": 275}
        assert body["year"] == {"orders_count": 4, "orders_money": 345}
        assert body["out_of_stock_products"] == 1

    def test_employee_is_forbidden(self, auth_client, employee):
        response = auth_client(employee).get(self.url)

        assert response.status_code == status.HTTP_403_FORBIDDEN


class TestConfiguration:
    @pytest.fixture(autouse=True)
    def setup(self):
        self.url = reverse("v1:configuration")

    def test_every_user_can_read_it(self, auth_client, employee):
        response = auth_client(employee).get(self.url)

        assert response.status_code == status.HTTP_200_OK
        assert response.json()["title"]

    def test_admin_updates_it(self, auth_client, admin_user):
        response = auth_client(admin_user).patch(self.url, {"address": "اسيوط", "phone": "0882"})

        assert response.status_code == status.HTTP_200_OK
        assert response.json()["address"] == "اسيوط"

    def test_employee_cannot_update_it(self, auth_client, employee):
        response = auth_client(employee).patch(self.url, {"address": "x"})

        assert response.status_code == status.HTTP_403_FORBIDDEN
