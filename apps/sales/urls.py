from rest_framework.routers import SimpleRouter

from apps.sales.views import BillViewSet, OrderViewSet

router = SimpleRouter()
router.register("bills", BillViewSet, basename="bills")
router.register("orders", OrderViewSet, basename="orders")

urlpatterns = router.urls
