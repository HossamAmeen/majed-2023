from typing import Any

from rest_framework import serializers


def get_current_user(serializer: serializers.Serializer) -> object | None:  # type: ignore[type-arg]
    request = serializer.context.get("request")
    user = getattr(request, "user", None)

    if user is None or not getattr(user, "is_authenticated", False):
        return None

    return user


def assign_audit_on_create(
    serializer: serializers.Serializer,  # type: ignore[type-arg]
    validated_data: dict[str, object],
) -> dict[str, object]:
    user = get_current_user(serializer)

    if user is not None:
        validated_data.setdefault("created_by", user)
        validated_data.setdefault("updated_by", user)

    return validated_data


class BaseModelSerializer(serializers.ModelSerializer):  # type: ignore[type-arg]
    def create(self, validated_data: dict[str, object]) -> Any:
        return super().create(assign_audit_on_create(self, validated_data))

    def update(self, instance: Any, validated_data: dict[str, object]) -> Any:
        user = get_current_user(self)

        if user is not None and hasattr(instance, "updated_by"):
            validated_data["updated_by"] = user

        return super().update(instance, validated_data)


def audit_user_name_field(source: str = "created_by") -> serializers.CharField:
    """Read-only user_name of an audit user; null when the user is missing."""
    return serializers.CharField(source=f"{source}.user_name", read_only=True, allow_null=True)
