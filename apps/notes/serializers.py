from apps.notes.models import Note
from utils.serializers import BaseModelSerializer, audit_user_name_field


class NoteSerializer(BaseModelSerializer):
    created_by_name = audit_user_name_field("created_by")
    updated_by_name = audit_user_name_field("updated_by")

    class Meta:
        model = Note
        fields = ["id", "note", "created_by_name", "updated_by_name", "created_at", "updated_at"]
        read_only_fields = ["id", "created_by_name", "updated_by_name", "created_at", "updated_at"]
