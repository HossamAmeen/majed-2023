from typing import Any

from drf_spectacular.utils import extend_schema, extend_schema_view
from rest_framework import mixins, status, viewsets
from rest_framework.decorators import action
from rest_framework.request import Request
from rest_framework.response import Response
from rest_framework.serializers import BaseSerializer

from apps.sales.filters import BillFilter, OrderFilter
from apps.sales.models import Bill, Order
from apps.sales.selectors import BillSelector, OrderSelector
from apps.sales.serializers import (
    BillCreateSerializer,
    BillSerializer,
    BillUpdateSerializer,
    OrderSerializer,
    OrderTotalsSerializer,
)
from apps.sales.services import BillService, OrderService


@extend_schema_view(
    list=extend_schema(tags=["bills"]),
    retrieve=extend_schema(tags=["bills"]),
    create=extend_schema(
        tags=["bills"],
        request=BillCreateSerializer,
        responses={201: BillSerializer},
        description=(
            "Sell products by barcode code. Stock is taken in the same transaction; "
            "an unknown code or missing stock returns 400 and nothing is saved."
        ),
    ),
    partial_update=extend_schema(
        tags=["bills"],
        request=BillUpdateSerializer,
        responses=BillSerializer,
        description="Edit customer data, bill discount and line quantities (0 returns the line).",
    ),
    destroy=extend_schema(tags=["bills"], description="Return the whole bill to stock."),
)
class BillViewSet(viewsets.ModelViewSet):  # type: ignore[type-arg]
    http_method_names = ["get", "post", "patch", "delete", "head", "options"]
    filterset_class = BillFilter
    search_fields = ["name", "phone", "=id"]
    ordering_fields = ["id", "created_at", "updated_at", "net_total"]

    def get_queryset(self):  # type: ignore[no-untyped-def]
        if self.action in {"list", "retrieve"}:
            return BillSelector.list()
        return Bill.objects.all()

    def get_serializer_class(self) -> type[BaseSerializer]:  # type: ignore[type-arg]
        if self.action == "create":
            return BillCreateSerializer
        if self.action == "partial_update":
            return BillUpdateSerializer
        return BillSerializer

    def create(self, request: Request, *args: Any, **kwargs: Any) -> Response:
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        data = dict(serializer.validated_data)
        lines = data.pop("items")
        bill = BillService.create_bill(actor=request.user, data=data, lines=lines)  # type: ignore[arg-type]
        return Response(BillSerializer(BillSelector.detail(bill.pk)).data, status=status.HTTP_201_CREATED)

    def partial_update(self, request: Request, *args: Any, **kwargs: Any) -> Response:
        bill = self.get_object()
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        data = dict(serializer.validated_data)
        order_quantities = data.pop("orders", {})
        bill = BillService.update_bill(
            actor=request.user,  # type: ignore[arg-type]
            bill=bill,
            data=data,
            order_quantities=order_quantities,
        )
        return Response(BillSerializer(BillSelector.detail(bill.pk)).data)

    def perform_destroy(self, instance: Bill) -> None:
        BillService.delete_bill(actor=self.request.user, bill=instance)  # type: ignore[arg-type]


@extend_schema_view(
    list=extend_schema(tags=["orders"], description="Sold lines, or returned ones with ?returned=true."),
    destroy=extend_schema(tags=["orders"], description="Return one line to stock."),
)
class OrderViewSet(mixins.ListModelMixin, mixins.DestroyModelMixin, viewsets.GenericViewSet):  # type: ignore[type-arg]
    serializer_class = OrderSerializer
    filterset_class = OrderFilter
    search_fields = ["product_name", "=product__code", "bill__name"]
    ordering_fields = ["id", "date", "price", "quantity", "deleted_at"]

    def get_queryset(self):  # type: ignore[no-untyped-def]
        if self.action == "destroy":
            return OrderSelector.alive()
        return OrderSelector.all_including_returned()

    def perform_destroy(self, instance: Order) -> None:
        OrderService.return_order(actor=self.request.user, order=instance)  # type: ignore[arg-type]

    @extend_schema(tags=["orders"], responses=OrderTotalsSerializer)
    @action(detail=False, methods=["get"])
    def summary(self, request: Request) -> Response:
        """Count, quantity and money for the orders matching the list filters."""
        totals = OrderSelector.totals(self.filter_queryset(self.get_queryset()))
        return Response(OrderTotalsSerializer(totals).data)
