from rest_framework import serializers

from apps.dashboard.models import ShopConfiguration
from utils.serializers import BaseModelSerializer


class ShopConfigurationSerializer(BaseModelSerializer):
    class Meta:
        model = ShopConfiguration
        fields = ["title", "phone", "address", "email", "receipt_header", "receipt_footer", "updated_at"]
        read_only_fields = ["updated_at"]


class PeriodStatisticsSerializer(serializers.Serializer):  # type: ignore[type-arg]
    orders_count = serializers.IntegerField()
    orders_money = serializers.DecimalField(max_digits=18, decimal_places=2)


class DayStatisticsSerializer(PeriodStatisticsSerializer):
    orders_discount = serializers.DecimalField(max_digits=18, decimal_places=2)
    bills_discount = serializers.DecimalField(max_digits=18, decimal_places=2)
    total_discount = serializers.DecimalField(max_digits=18, decimal_places=2)
    goods_cost = serializers.DecimalField(max_digits=18, decimal_places=2)
    net_profit = serializers.DecimalField(max_digits=18, decimal_places=2)


class TodayStatisticsSerializer(DayStatisticsSerializer):
    returned_count = serializers.IntegerField()
    returned_money = serializers.DecimalField(max_digits=18, decimal_places=2)


class StatisticsSerializer(serializers.Serializer):  # type: ignore[type-arg]
    date = serializers.DateField()
    today = TodayStatisticsSerializer()
    yesterday = DayStatisticsSerializer()
    month = PeriodStatisticsSerializer()
    year = PeriodStatisticsSerializer()
    out_of_stock_products = serializers.IntegerField()
