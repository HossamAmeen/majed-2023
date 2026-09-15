from decimal import Decimal

import pytest
from django.urls import reverse
from rest_framework import status

from apps.products.models import Product
from apps.products.tests.factories import ProductFactory
from apps.sales.models import Bill, Order
from apps.sales.tests.factories import BillFactory, OrderFactory

pytestmark = pytest.mark.django_db


class TestBillCreate:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee):
        self.user = employee
        self.client = auth_client(employee)
        self.url = reverse("v1:bills-list")
        self.shirt = ProductFactory(code=11111, quantity=5, selling_price=Decimal("100"))
        self.pants = ProductFactory(code=22222, quantity=2, selling_price=Decimal("250"))

    def test_creates_bill_orders_and_takes_stock(self):
        payload = {
            "name": "احمد",
            "phone": "0100",
            "discount": "10",
            "items": [
                {"code": 11111, "quantity": 2, "discount": "5"},
                {"code": 22222, "quantity": 1},
            ],
        }

        response = self.client.post(self.url, payload, format="json")

        assert response.status_code == status.HTTP_201_CREATED
        body = response.json()
        assert body["name"] == "احمد"
        assert body["subtotal"] == 450
        assert body["total_discount"] == 15
        assert body["net_total"] == 435
        assert body["created_by_name"] == self.user.user_name
        assert [line["product_code"] for line in body["orders"]] == [11111, 22222]
        self.shirt.refresh_from_db()
        self.pants.refresh_from_db()
        assert self.shirt.quantity == 3
        assert self.pants.quantity == 1
        order = Order.objects.get(product=self.shirt)
        assert order.price == Decimal("100")
        assert order.product_price == Decimal("100")
        assert order.created_by == self.user

    def test_price_override_is_used_for_the_line(self):
        payload = {"items": [{"code": 11111, "quantity": 1, "price": "80"}]}

        response = self.client.post(self.url, payload, format="json")

        assert response.status_code == status.HTTP_201_CREATED
        assert response.json()["orders"][0]["price"] == 80

    def test_same_code_twice_takes_stock_for_both_lines(self):
        payload = {"items": [{"code": 22222, "quantity": 1}, {"code": 22222, "quantity": 1}]}

        response = self.client.post(self.url, payload, format="json")

        assert response.status_code == status.HTTP_201_CREATED
        self.pants.refresh_from_db()
        assert self.pants.quantity == 0

    def test_unknown_code_is_rejected_and_nothing_is_saved(self):
        payload = {"items": [{"code": 11111, "quantity": 1}, {"code": 99999, "quantity": 1}]}

        response = self.client.post(self.url, payload, format="json")

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        assert response.json()["detail"] == "كود هذا المنتج غير صالح 99999"
        assert not Bill.all_objects.exists()
        assert not Order.all_objects.exists()
        self.shirt.refresh_from_db()
        assert self.shirt.quantity == 5

    def test_insufficient_stock_is_rejected_and_rolled_back(self):
        payload = {"items": [{"code": 11111, "quantity": 1}, {"code": 22222, "quantity": 3}]}

        response = self.client.post(self.url, payload, format="json")

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        assert self.pants.name in response.json()["detail"]
        assert not Bill.all_objects.exists()
        self.shirt.refresh_from_db()
        assert self.shirt.quantity == 5

    def test_deleted_product_cannot_be_sold(self):
        self.shirt.soft_delete()

        response = self.client.post(self.url, {"items": [{"code": 11111}]}, format="json")

        assert response.status_code == status.HTTP_400_BAD_REQUEST

    def test_empty_items_is_rejected(self):
        response = self.client.post(self.url, {"items": []}, format="json")

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        assert response.json()["detail"] == "لا يوجد منتجات في هذه الفاتوره"

    def test_bill_discount_larger_than_total_is_rejected(self):
        payload = {"discount": "500", "items": [{"code": 11111, "quantity": 1}]}

        response = self.client.post(self.url, payload, format="json")

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        assert not Bill.all_objects.exists()

    def test_requires_authentication(self, api_client):
        response = api_client.post(self.url, {"items": [{"code": 11111}]}, format="json")

        assert response.status_code == status.HTTP_401_UNAUTHORIZED


class TestBillList:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee):
        self.client = auth_client(employee)
        self.url = reverse("v1:bills-list")

    def test_lists_alive_bills_with_totals_of_alive_orders(self):
        bill = BillFactory(name="محمد", discount=Decimal("5"))
        OrderFactory(bill=bill, price=Decimal("100"), quantity=2, discount=Decimal("10"))
        returned = OrderFactory(bill=bill, price=Decimal("50"), quantity=1)
        returned.soft_delete()
        BillFactory().soft_delete()

        response = self.client.get(self.url)

        assert response.status_code == status.HTTP_200_OK
        body = response.json()
        assert body["count"] == 1
        row = body["results"][0]
        assert row["subtotal"] == 200
        assert row["total_discount"] == 15
        assert row["net_total"] == 185
        assert len(row["orders"]) == 1

    def test_search_by_customer_name(self):
        BillFactory(name="محمد علي")
        BillFactory(name="سارة")

        response = self.client.get(self.url, {"search": "سارة"})

        assert [row["name"] for row in response.json()["results"]] == ["سارة"]


class TestBillUpdate:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee):
        self.user = employee
        self.client = auth_client(employee)
        self.product = ProductFactory(quantity=5)
        self.bill = BillFactory()
        self.order = OrderFactory(bill=self.bill, product=self.product, quantity=3)
        self.url = reverse("v1:bills-detail", args=[self.bill.pk])

    def test_raising_quantity_takes_the_difference_from_stock(self):
        response = self.client.patch(
            self.url, {"name": "جديد", "orders": [{"id": self.order.pk, "quantity": 5}]}, format="json"
        )

        assert response.status_code == status.HTTP_200_OK
        assert response.json()["name"] == "جديد"
        self.product.refresh_from_db()
        self.order.refresh_from_db()
        assert self.product.quantity == 3
        assert self.order.quantity == 5

    def test_lowering_quantity_returns_the_difference_to_stock(self):
        response = self.client.patch(
            self.url, {"orders": [{"id": self.order.pk, "quantity": 1}]}, format="json"
        )

        assert response.status_code == status.HTTP_200_OK
        self.product.refresh_from_db()
        assert self.product.quantity == 7

    def test_raising_beyond_stock_is_rejected_without_changes(self):
        response = self.client.patch(
            self.url, {"name": "لا", "orders": [{"id": self.order.pk, "quantity": 9}]}, format="json"
        )

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        self.product.refresh_from_db()
        self.bill.refresh_from_db()
        assert self.product.quantity == 5
        assert self.bill.name != "لا"

    def test_zero_quantity_returns_line_and_empty_bill(self):
        response = self.client.patch(
            self.url, {"orders": [{"id": self.order.pk, "quantity": 0}]}, format="json"
        )

        assert response.status_code == status.HTTP_200_OK
        assert response.json()["deleted_at"] is not None
        self.product.refresh_from_db()
        assert self.product.quantity == 8
        assert Order.all_objects.get(pk=self.order.pk).is_deleted
        assert Bill.all_objects.get(pk=self.bill.pk).is_deleted

    def test_order_from_another_bill_is_rejected(self):
        other = OrderFactory()

        response = self.client.patch(self.url, {"orders": [{"id": other.pk, "quantity": 1}]}, format="json")

        assert response.status_code == status.HTTP_400_BAD_REQUEST

    def test_lowering_quantity_of_deleted_product_restores_it(self):
        self.product.soft_delete()

        response = self.client.patch(
            self.url, {"orders": [{"id": self.order.pk, "quantity": 1}]}, format="json"
        )

        assert response.status_code == status.HTTP_200_OK
        product = Product.objects.get(pk=self.product.pk)
        assert product.quantity == 2


class TestBillDestroy:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee):
        self.client = auth_client(employee)
        self.bill = BillFactory()
        self.url = reverse("v1:bills-detail", args=[self.bill.pk])

    def test_returns_every_line_to_stock(self):
        product = ProductFactory(quantity=1)
        OrderFactory(bill=self.bill, product=product, quantity=2)
        OrderFactory(bill=self.bill, product=product, quantity=3)

        response = self.client.delete(self.url)

        assert response.status_code == status.HTTP_204_NO_CONTENT
        product.refresh_from_db()
        assert product.quantity == 6
        assert Bill.all_objects.get(pk=self.bill.pk).is_deleted
        assert Order.all_objects.filter(bill=self.bill).deleted().count() == 2

    def test_restores_a_deleted_product_with_the_returned_quantity(self):
        product = ProductFactory(quantity=7)
        OrderFactory(bill=self.bill, product=product, quantity=2)
        product.soft_delete()

        self.client.delete(self.url)

        product = Product.objects.get(pk=product.pk)
        assert product.quantity == 2

    def test_recreates_a_product_that_no_longer_exists(self):
        OrderFactory(bill=self.bill, product=None, product_name="جاكيت", quantity=2)

        response = self.client.delete(self.url)

        assert response.status_code == status.HTTP_204_NO_CONTENT
        recreated = Product.objects.get(name="جاكيت")
        assert recreated.quantity == 2
