from typing import Any

from drf_spectacular.utils import extend_schema, extend_schema_view
from rest_framework import generics
from rest_framework.permissions import IsAuthenticated
from rest_framework.request import Request
from rest_framework.response import Response

from apps.common.permissions import IsMasterOrAdmin
from apps.dashboard.models import ShopConfiguration
from apps.dashboard.selectors import StatisticsSelector
from apps.dashboard.serializers import ShopConfigurationSerializer, StatisticsSerializer


@extend_schema_view(
    get=extend_schema(
        tags=["dashboard"], description="Shop details (every signed-in user; used on receipts)."
    ),
    patch=extend_schema(tags=["dashboard"], description="Master or admin only."),
    put=extend_schema(tags=["dashboard"], description="Master or admin only."),
)
class ShopConfigurationView(generics.RetrieveUpdateAPIView):  # type: ignore[type-arg]
    serializer_class = ShopConfigurationSerializer

    def get_permissions(self) -> list[Any]:
        if self.request.method == "GET":
            return [IsAuthenticated()]
        return [IsMasterOrAdmin()]

    def get_object(self) -> ShopConfiguration:
        return ShopConfiguration.load()


@extend_schema(tags=["dashboard"], responses=StatisticsSerializer)
class StatisticsView(generics.GenericAPIView):  # type: ignore[type-arg]
    """Today, yesterday, month and year sales figures. Master or admin only."""

    permission_classes = [IsMasterOrAdmin]
    serializer_class = StatisticsSerializer

    def get(self, request: Request) -> Response:
        return Response(StatisticsSerializer(StatisticsSelector.overview()).data)
