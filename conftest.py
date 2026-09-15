from collections.abc import Callable

import pytest

# Import before any freeze_time test runs: DRF captures `time.time` at import time,
# and a module first imported while time is frozen keeps freezegun's fake clock.
import rest_framework.throttling  # noqa: F401
from rest_framework.test import APIClient

from apps.users.models import User
from apps.users.tests.factories import UserFactory
from utils.enums import Role


@pytest.fixture
def api_client() -> APIClient:
    return APIClient()


@pytest.fixture
def auth_client() -> Callable[[User], APIClient]:
    def make(user: User) -> APIClient:
        client = APIClient()
        client.force_authenticate(user=user)
        return client

    return make


@pytest.fixture
def master(db: None) -> User:
    return UserFactory(role=Role.MASTER, user_name="master")


@pytest.fixture
def admin_user(db: None) -> User:
    return UserFactory(role=Role.ADMIN, user_name="admin2")


@pytest.fixture
def employee(db: None) -> User:
    return UserFactory(role=Role.EMPLOYEE, user_name="employee")
