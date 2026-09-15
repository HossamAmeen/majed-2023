from django.contrib import admin

from apps.sales.models import Bill, Order


class OrderInline(admin.TabularInline):  # type: ignore[type-arg]
    model = Order
    extra = 0
    fields = ["product_name", "price", "quantity", "discount", "date", "deleted_at"]
    readonly_fields = ["product_name", "price", "quantity", "discount", "date", "deleted_at"]
    can_delete = False


@admin.register(Bill)
class BillAdmin(admin.ModelAdmin):  # type: ignore[type-arg]
    list_display = ["id", "name", "phone", "discount", "created_at", "deleted_at"]
    search_fields = ["id", "name", "phone"]
    inlines = [OrderInline]

    def get_queryset(self, request):  # type: ignore[no-untyped-def]
        return Bill.all_objects.all()


@admin.register(Order)
class OrderAdmin(admin.ModelAdmin):  # type: ignore[type-arg]
    list_display = ["id", "product_name", "price", "quantity", "discount", "date", "bill", "deleted_at"]
    list_filter = ["date"]
    search_fields = ["product_name"]

    def get_queryset(self, request):  # type: ignore[no-untyped-def]
        return Order.all_objects.all()
