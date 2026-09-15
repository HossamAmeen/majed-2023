import django_filters
from django.db.models import QuerySet

from apps.sales.models import Bill, Order


class BillFilter(django_filters.FilterSet):
    year = django_filters.NumberFilter(field_name="created_at", lookup_expr="year")
    created_from = django_filters.DateFilter(field_name="created_at", lookup_expr="date__gte")
    created_to = django_filters.DateFilter(field_name="created_at", lookup_expr="date__lte")

    class Meta:
        model = Bill
        fields = ["year", "created_from", "created_to"]


class OrderFilter(django_filters.FilterSet):
    returned = django_filters.BooleanFilter(
        method="filter_returned",
        help_text="true: only returned (deleted) orders. Omitted or false: sold orders.",
    )
    date = django_filters.DateFilter(field_name="date")
    date_from = django_filters.DateFilter(field_name="date", lookup_expr="gte")
    date_to = django_filters.DateFilter(field_name="date", lookup_expr="lte")
    year = django_filters.NumberFilter(field_name="date", lookup_expr="year")
    month = django_filters.NumberFilter(field_name="date", lookup_expr="month")

    class Meta:
        model = Order
        fields = ["returned", "date", "date_from", "date_to", "year", "month", "bill"]

    def filter_returned(self, queryset: QuerySet[Order], name: str, value: bool) -> QuerySet[Order]:
        # Applied in filter_queryset so that "sold only" is also the default.
        return queryset

    def filter_queryset(self, queryset: QuerySet[Order]) -> QuerySet[Order]:
        returned = self.form.cleaned_data.get("returned")
        queryset = queryset.filter(deleted_at__isnull=not returned)
        return super().filter_queryset(queryset)
