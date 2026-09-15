from django.contrib import admin
from django.contrib.auth.admin import UserAdmin as DjangoUserAdmin

from apps.users.models import User


@admin.register(User)
class UserAdmin(DjangoUserAdmin):
    ordering = ["-id"]
    list_display = ["user_name", "full_name", "email", "role", "is_active", "deleted_at"]
    list_filter = ["role", "is_active", "is_superuser"]
    search_fields = ["user_name", "full_name", "email"]
    readonly_fields = ["last_login", "created_at", "updated_at"]
    fieldsets = (
        (None, {"fields": ("user_name", "password")}),
        ("البيانات", {"fields": ("full_name", "email", "phone", "role")}),
        ("الصلاحيات", {"fields": ("is_active", "is_superuser")}),
        ("التواريخ", {"fields": ("last_login", "created_at", "updated_at", "deleted_at")}),
    )
    add_fieldsets = (
        (
            None,
            {
                "classes": ("wide",),
                "fields": ("user_name", "full_name", "email", "role", "password1", "password2"),
            },
        ),
    )
    filter_horizontal = ()
