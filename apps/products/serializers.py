from decimal import Decimal

from rest_framework import serializers

from apps.products.models import Product
from utils.serializers import audit_user_name_field


class ProductSerializer(serializers.ModelSerializer):  # type: ignore[type-arg]
    total_purchasing_price = serializers.SerializerMethodField()
    total_selling_price = serializers.SerializerMethodField()
    created_by_name = audit_user_name_field("created_by")
    updated_by_name = audit_user_name_field("updated_by")

    class Meta:
        model = Product
        fields = [
            "id",
            "name",
            "code",
            "purchasing_price",
            "selling_price",
            "quantity",
            "total_purchasing_price",
            "total_selling_price",
            "created_by_name",
            "updated_by_name",
            "created_at",
            "updated_at",
        ]
        read_only_fields = fields

    def get_total_purchasing_price(self, obj: Product) -> Decimal:
        return obj.purchasing_price * obj.quantity

    def get_total_selling_price(self, obj: Product) -> Decimal:
        return obj.selling_price * obj.quantity


class ProductWriteSerializer(serializers.ModelSerializer):  # type: ignore[type-arg]
    purchasing_price = serializers.DecimalField(max_digits=14, decimal_places=2, min_value=Decimal(0))
    selling_price = serializers.DecimalField(max_digits=14, decimal_places=2, min_value=Decimal(0))
    quantity = serializers.IntegerField(min_value=0)

    class Meta:
        model = Product
        fields = ["name", "purchasing_price", "selling_price", "quantity"]


class ProductLookupSerializer(serializers.ModelSerializer):  # type: ignore[type-arg]
    """What the bill screen needs after scanning a barcode."""

    class Meta:
        model = Product
        fields = ["id", "code", "name", "selling_price", "quantity"]
        read_only_fields = fields


class ProductSummarySerializer(serializers.Serializer):  # type: ignore[type-arg]
    products_count = serializers.IntegerField()
    total_quantity = serializers.IntegerField()
    total_purchasing_price = serializers.DecimalField(max_digits=18, decimal_places=2)
    total_selling_price = serializers.DecimalField(max_digits=18, decimal_places=2)
    expected_profit = serializers.DecimalField(max_digits=18, decimal_places=2)
