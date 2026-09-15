import pytest
from django.urls import reverse
from rest_framework import status

from apps.users.models import User
from apps.users.tests.factories import UserFactory
from utils.enums import Role

pytestmark = pytest.mark.django_db


class TestUserManagement:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, master):
        self.master = master
        self.client = auth_client(master)
        self.list_url = reverse("v1:users-list")
        self.payload = {
            "user_name": "new_cashier",
            "full_name": "كاشير جديد",
            "email": "new@example.com",
            "role": Role.EMPLOYEE,
            "password": "Strong-pass-1",
            "password_confirmation": "Strong-pass-1",
        }

    def test_master_creates_user_with_hashed_password(self):
        response = self.client.post(self.list_url, self.payload)

        assert response.status_code == status.HTTP_201_CREATED
        user = User.objects.get(user_name="new_cashier")
        assert user.check_password("Strong-pass-1")
        assert user.created_by == self.master
        assert "password" not in response.json()

    def test_cannot_create_another_master(self):
        response = self.client.post(self.list_url, {**self.payload, "role": Role.MASTER})

        assert response.status_code == status.HTTP_400_BAD_REQUEST

    def test_password_is_required_on_create(self):
        payload = {k: v for k, v in self.payload.items() if not k.startswith("password")}

        response = self.client.post(self.list_url, payload)

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        assert "password" in response.json()["errors"]

    def test_user_name_of_deleted_user_is_still_taken(self):
        UserFactory(user_name="new_cashier").soft_delete()

        response = self.client.post(self.list_url, self.payload)

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        assert "user_name" in response.json()["errors"]

    def test_update_role_of_other_user(self):
        user = UserFactory(role=Role.EMPLOYEE)

        response = self.client.patch(reverse("v1:users-detail", args=[user.pk]), {"role": Role.ADMIN})

        assert response.status_code == status.HTTP_200_OK
        user.refresh_from_db()
        assert user.role == Role.ADMIN

    def test_cannot_change_own_role(self):
        response = self.client.patch(
            reverse("v1:users-detail", args=[self.master.pk]), {"role": Role.EMPLOYEE}
        )

        assert response.status_code == status.HTTP_400_BAD_REQUEST

    def test_delete_is_soft_and_self_delete_is_blocked(self):
        user = UserFactory()

        deleted = self.client.delete(reverse("v1:users-detail", args=[user.pk]))
        self_delete = self.client.delete(reverse("v1:users-detail", args=[self.master.pk]))

        assert deleted.status_code == status.HTTP_204_NO_CONTENT
        assert User.all_objects.get(pk=user.pk).is_deleted
        assert self_delete.status_code == status.HTTP_400_BAD_REQUEST

    @pytest.mark.parametrize("role_fixture", ["admin_user", "employee"])
    def test_non_masters_are_forbidden(self, request, auth_client, role_fixture):
        client = auth_client(request.getfixturevalue(role_fixture))

        response = client.get(self.list_url)

        assert response.status_code == status.HTTP_403_FORBIDDEN
