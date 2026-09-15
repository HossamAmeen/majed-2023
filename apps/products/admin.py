from django.contrib import admin

from apps.products.models import Product


@admin.register(Product)
class ProductAdmin(admin.ModelAdmin):  # type: ignore[type-arg]
    list_display = ["name", "code", "purchasing_price", "selling_price", "quantity", "updated_at"]
    search_fields = ["name", "code"]
    readonly_fields = ["code", "created_at", "updated_at", "created_by", "updated_by"]

    def get_queryset(self, request):  # type: ignore[no-untyped-def]
        return Product.all_objects.all()
