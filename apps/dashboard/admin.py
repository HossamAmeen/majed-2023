from django.contrib import admin

from apps.dashboard.models import ShopConfiguration


@admin.register(ShopConfiguration)
class ShopConfigurationAdmin(admin.ModelAdmin):  # type: ignore[type-arg]
    list_display = ["title", "phone", "address"]

    def has_add_permission(self, request):  # type: ignore[no-untyped-def]
        return not ShopConfiguration.objects.exists()

    def has_delete_permission(self, request, obj=None):  # type: ignore[no-untyped-def]
        return False
