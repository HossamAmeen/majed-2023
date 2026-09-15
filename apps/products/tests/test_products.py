from decimal import Decimal

import pytest
from django.urls import reverse
from rest_framework import status

from apps.products.models import Product
from apps.products.tests.factories import ProductFactory

pytestmark = pytest.mark.django_db


class TestProductCrud:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, master):
        self.master = master
        self.client = auth_client(master)
        self.list_url = reverse("v1:products-list")

    def test_create_generates_a_unique_five_digit_code(self):
        payload = {"name": "بنطلون", "purchasing_price": "120", "selling_price": "200", "quantity": 4}

        response = self.client.post(self.list_url, payload)

        assert response.status_code == status.HTTP_201_CREATED
        body = response.json()
        assert 10000 <= body["code"] <= 99999
        assert body["total_purchasing_price"] == 480
        assert body["total_selling_price"] == 800
        assert Product.objects.get(pk=body["id"]).created_by == self.master

    def test_code_cannot_be_set_by_client(self):
        payload = {"name": "x", "purchasing_price": "1", "selling_price": "2", "quantity": 1, "code": 5}

        response = self.client.post(self.list_url, payload)

        assert response.json()["code"] != 5

    def test_negative_values_are_rejected(self):
        payload = {"name": "x", "purchasing_price": "-1", "selling_price": "2", "quantity": -3}

        response = self.client.post(self.list_url, payload)

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        assert {"purchasing_price", "quantity"} <= set(response.json()["errors"])

    def test_update_and_soft_delete(self):
        product = ProductFactory()
        detail_url = reverse("v1:products-detail", args=[product.pk])

        updated = self.client.patch(detail_url, {"quantity": 30})
        deleted = self.client.delete(detail_url)

        assert updated.json()["quantity"] == 30
        assert deleted.status_code == status.HTTP_204_NO_CONTENT
        assert Product.all_objects.get(pk=product.pk).is_deleted
        assert self.client.get(detail_url).status_code == status.HTTP_404_NOT_FOUND

    def test_low_stock_filter_and_summary(self):
        ProductFactory(quantity=2, purchasing_price=Decimal("10"), selling_price=Decimal("15"))
        ProductFactory(quantity=20)

        rows = self.client.get(self.list_url, {"quantity_lt": 5}).json()["results"]
        summary = self.client.get(reverse("v1:products-summary"), {"quantity_lt": 5}).json()

        assert len(rows) == 1
        assert summary == {
            "products_count": 1,
            "total_quantity": 2,
            "total_purchasing_price": 20,
            "total_selling_price": 30,
            "expected_profit": 10,
        }

    def test_search_by_name_or_exact_code(self):
        ProductFactory(name="قميص ابيض", code=12121)
        ProductFactory(name="بنطلون", code=34343)

        by_name = self.client.get(self.list_url, {"search": "قميص"})
        by_code = self.client.get(self.list_url, {"search": "34343"})

        assert by_name.status_code == status.HTTP_200_OK
        assert [row["code"] for row in by_name.json()["results"]] == [12121]
        assert [row["code"] for row in by_code.json()["results"]] == [34343]

    def test_exports(self):
        ProductFactory(name="قميص ابيض")

        excel = self.client.get(reverse("v1:products-export-excel"))
        pdf = self.client.get(reverse("v1:products-export-pdf"))

        assert excel.status_code == status.HTTP_200_OK
        assert excel.content[:2] == b"PK"
        assert pdf.status_code == status.HTTP_200_OK
        assert pdf.content[:4] == b"%PDF"


class TestProductPermissions:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee):
        self.client = auth_client(employee)
        self.product = ProductFactory(code=45678, name="شورت")

    def test_employee_cannot_manage_products(self):
        response = self.client.get(reverse("v1:products-list"))

        assert response.status_code == status.HTTP_403_FORBIDDEN

    def test_employee_can_look_up_by_code(self):
        response = self.client.get(reverse("v1:products-by-code", args=["45678"]))

        assert response.status_code == status.HTTP_200_OK
        assert response.json()["name"] == "شورت"

    def test_lookup_of_unknown_or_deleted_code_is_404(self):
        self.product.soft_delete()

        response = self.client.get(reverse("v1:products-by-code", args=["45678"]))

        assert response.status_code == status.HTTP_404_NOT_FOUND
        assert response.json()["detail"] == "لا يوجد منتج بهذا الكود"
