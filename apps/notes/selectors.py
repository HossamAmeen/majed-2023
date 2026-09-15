from django.db.models import QuerySet

from apps.notes.models import Note


class NoteSelector:
    @staticmethod
    def list() -> QuerySet[Note]:
        return Note.objects.select_related("created_by", "updated_by").order_by("-id")
