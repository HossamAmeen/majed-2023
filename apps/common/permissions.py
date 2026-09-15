from typing import Any

from rest_framework.permissions import SAFE_METHODS, BasePermission
from rest_framework.request import Request

from utils.enums import Role


def _has_role(request: Request, *roles: Role) -> bool:
    user = request.user
    return bool(user and user.is_authenticated and getattr(user, "role", None) in roles)


class IsMaster(BasePermission):
    """Role 1 only: users and products management."""

    message = "هذه الصفحة متاحة للمدير العام فقط"

    def has_permission(self, request: Request, view: Any) -> bool:
        return _has_role(request, Role.MASTER)


class IsMasterOrAdmin(BasePermission):
    """Roles 1 and 2: system statistics and shop configuration."""

    message = "هذه الصفحة متاحة للمديرين فقط"

    def has_permission(self, request: Request, view: Any) -> bool:
        return _has_role(request, Role.MASTER, Role.ADMIN)


class ReadOrCreateOrIsMaster(BasePermission):
    """Any signed-in user may list, view and create; only a master may edit or delete."""

    message = "التعديل والحذف متاح للمدير العام فقط"

    def has_permission(self, request: Request, view: Any) -> bool:
        if not (request.user and request.user.is_authenticated):
            return False
        if request.method in SAFE_METHODS or request.method == "POST":
            return True
        return _has_role(request, Role.MASTER)
