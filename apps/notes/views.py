from drf_spectacular.utils import extend_schema, extend_schema_view
from rest_framework import viewsets

from apps.common.permissions import ReadOrCreateOrIsMaster
from apps.notes.selectors import NoteSelector
from apps.notes.serializers import NoteSerializer


@extend_schema_view(
    list=extend_schema(tags=["notes"]),
    retrieve=extend_schema(tags=["notes"]),
    create=extend_schema(tags=["notes"]),
    update=extend_schema(tags=["notes"], description="Master only."),
    partial_update=extend_schema(tags=["notes"], description="Master only."),
    destroy=extend_schema(tags=["notes"], description="Master only."),
)
class NoteViewSet(viewsets.ModelViewSet):  # type: ignore[type-arg]
    """Shared notes: everyone can read and add; only a master can edit or delete."""

    serializer_class = NoteSerializer
    permission_classes = [ReadOrCreateOrIsMaster]
    search_fields = ["note"]
    ordering_fields = ["id", "created_at"]

    def get_queryset(self):  # type: ignore[no-untyped-def]
        return NoteSelector.list()
