from django.urls import path

from apps.dashboard.views import ShopConfigurationView, StatisticsView

urlpatterns = [
    path("configuration/", ShopConfigurationView.as_view(), name="configuration"),
    path("statistics/", StatisticsView.as_view(), name="statistics"),
]
