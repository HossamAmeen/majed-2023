from decimal import Decimal
from typing import Any

from rest_framework import serializers

from apps.sales.models import Bill, Order
from apps.sales.services import BillLine
from utils.serializers import audit_user_name_field


class _OrderLineFieldsMixin(serializers.Serializer):  # type: ignore[type-arg]
    product_code = serializers.IntegerField(source="product.code", read_only=True, allow_null=True)
    line_total = serializers.SerializerMethodField()

    def get_line_total(self, obj: Order) -> Decimal:
        return obj.price * obj.quantity - obj.discount


class BillOrderSerializer(_OrderLineFieldsMixin, serializers.ModelSerializer):  # type: ignore[type-arg]
    class Meta:
        model = Order
        fields = [
            "id",
            "product_id",
            "product_code",
            "product_name",
            "product_price",
            "price",
            "quantity",
            "discount",
            "line_total",
            "date",
        ]
        read_only_fields = fields


class BillSerializer(serializers.ModelSerializer):  # type: ignore[type-arg]
    orders = BillOrderSerializer(many=True, read_only=True)
    subtotal = serializers.DecimalField(max_digits=14, decimal_places=2, read_only=True)
    orders_discount = serializers.DecimalField(max_digits=14, decimal_places=2, read_only=True)
    total_discount = serializers.DecimalField(max_digits=14, decimal_places=2, read_only=True)
    net_total = serializers.DecimalField(max_digits=14, decimal_places=2, read_only=True)
    created_by_name = audit_user_name_field("created_by")
    updated_by_name = audit_user_name_field("updated_by")

    class Meta:
        model = Bill
        fields = [
            "id",
            "name",
            "phone",
            "discount",
            "subtotal",
            "orders_discount",
            "total_discount",
            "net_total",
            "orders",
            "created_by_name",
            "updated_by_name",
            "created_at",
            "updated_at",
            "deleted_at",
        ]
        read_only_fields = fields


class BillItemSerializer(serializers.Serializer):  # type: ignore[type-arg]
    code = serializers.IntegerField(min_value=0)
    quantity = serializers.IntegerField(min_value=1, default=1)
    discount = serializers.DecimalField(
        max_digits=14, decimal_places=2, min_value=Decimal(0), default=Decimal(0)
    )
    price = serializers.DecimalField(
        max_digits=14,
        decimal_places=2,
        min_value=Decimal(0),
        required=False,
        allow_null=True,
        help_text="Override the unit price. Defaults to the product's selling price.",
    )


class BillCreateSerializer(serializers.Serializer):  # type: ignore[type-arg]
    name = serializers.CharField(max_length=255, required=False, allow_blank=True, default="")
    phone = serializers.CharField(max_length=30, required=False, allow_blank=True, default="")
    discount = serializers.DecimalField(
        max_digits=14, decimal_places=2, min_value=Decimal(0), default=Decimal(0)
    )
    items = BillItemSerializer(many=True)

    def validate_items(self, value: list[dict[str, Any]]) -> list[BillLine]:
        if not value:
            raise serializers.ValidationError("لا يوجد منتجات في هذه الفاتوره")
        return [
            BillLine(
                code=item["code"],
                quantity=item["quantity"],
                discount=item["discount"],
                price=item.get("price"),
            )
            for item in value
        ]


class BillOrderQuantitySerializer(serializers.Serializer):  # type: ignore[type-arg]
    id = serializers.IntegerField()
    quantity = serializers.IntegerField(min_value=0, help_text="0 returns the whole line.")


class BillUpdateSerializer(serializers.Serializer):  # type: ignore[type-arg]
    name = serializers.CharField(max_length=255, required=False, allow_blank=True)
    phone = serializers.CharField(max_length=30, required=False, allow_blank=True)
    discount = serializers.DecimalField(max_digits=14, decimal_places=2, min_value=Decimal(0), required=False)
    orders = BillOrderQuantitySerializer(many=True, required=False)

    def validate_orders(self, value: list[dict[str, int]]) -> dict[int, int]:
        quantities: dict[int, int] = {}
        for item in value:
            if item["id"] in quantities:
                raise serializers.ValidationError("الطلب مكرر في نفس الفاتورة")
            quantities[item["id"]] = item["quantity"]
        return quantities


class OrderSerializer(_OrderLineFieldsMixin, serializers.ModelSerializer):  # type: ignore[type-arg]
    product_deleted = serializers.SerializerMethodField()
    bill_name = serializers.CharField(source="bill.name", read_only=True, allow_null=True)
    created_by_name = audit_user_name_field("created_by")

    class Meta:
        model = Order
        fields = [
            "id",
            "bill_id",
            "bill_name",
            "product_id",
            "product_code",
            "product_deleted",
            "product_name",
            "product_price",
            "price",
            "quantity",
            "discount",
            "line_total",
            "date",
            "status",
            "created_by_name",
            "created_at",
            "deleted_at",
        ]
        read_only_fields = fields

    def get_product_deleted(self, obj: Order) -> bool:
        return obj.product is not None and obj.product.deleted_at is not None


class OrderTotalsSerializer(serializers.Serializer):  # type: ignore[type-arg]
    orders_count = serializers.IntegerField()
    total_quantity = serializers.IntegerField()
    total_money = serializers.DecimalField(max_digits=18, decimal_places=2)
