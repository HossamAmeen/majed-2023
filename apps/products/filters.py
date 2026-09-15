import django_filters

from apps.products.models import Product


class ProductFilter(django_filters.FilterSet):
    quantity_lt = django_filters.NumberFilter(
        field_name="quantity",
        lookup_expr="lt",
        help_text="Products with stock below this number (e.g. 5 for 'running out').",
    )
    code = django_filters.NumberFilter(field_name="code")

    class Meta:
        model = Product
        fields = ["quantity_lt", "code"]
