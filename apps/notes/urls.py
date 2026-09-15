from rest_framework.routers import SimpleRouter

from apps.notes.views import NoteViewSet

router = SimpleRouter()
router.register("notes", NoteViewSet, basename="notes")

urlpatterns = router.urls
