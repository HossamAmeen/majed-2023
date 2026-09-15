import re

import pytest
from django.core import mail
from django.urls import reverse
from rest_framework import status

from apps.users.tests.factories import DEFAULT_PASSWORD, UserFactory

pytestmark = pytest.mark.django_db


class TestLogin:
    @pytest.fixture(autouse=True)
    def setup(self, api_client):
        self.client = api_client
        self.url = reverse("v1:auth-login")
        self.user = UserFactory(user_name="cashier", email="cashier@example.com")

    @pytest.mark.parametrize("login", ["cashier", "cashier@example.com"])
    def test_login_with_user_name_or_email(self, login):
        response = self.client.post(self.url, {"login": login, "password": DEFAULT_PASSWORD})

        assert response.status_code == status.HTTP_200_OK
        body = response.json()
        assert body["access"] and body["refresh"]
        assert body["user"]["user_name"] == "cashier"

    def test_wrong_password(self):
        response = self.client.post(self.url, {"login": "cashier", "password": "nope"})

        assert response.status_code == status.HTTP_401_UNAUTHORIZED
        assert response.json()["detail"] == "اسم المستخدم او كلمة المرور غير صحيحة"

    def test_deleted_user_cannot_login(self):
        self.user.soft_delete()

        response = self.client.post(self.url, {"login": "cashier", "password": DEFAULT_PASSWORD})

        assert response.status_code == status.HTTP_401_UNAUTHORIZED

    def test_access_token_works_and_logout_blacklists_refresh(self):
        tokens = self.client.post(self.url, {"login": "cashier", "password": DEFAULT_PASSWORD}).json()
        self.client.credentials(HTTP_AUTHORIZATION=f"Bearer {tokens['access']}")

        me = self.client.get(reverse("v1:auth-me"))
        logout = self.client.post(reverse("v1:auth-logout"), {"refresh": tokens["refresh"]})
        refresh = self.client.post(reverse("v1:auth-refresh"), {"refresh": tokens["refresh"]})

        assert me.status_code == status.HTTP_200_OK
        assert logout.status_code == status.HTTP_204_NO_CONTENT
        assert refresh.status_code == status.HTTP_401_UNAUTHORIZED

    def test_logout_works_with_only_the_refresh_token(self):
        tokens = self.client.post(self.url, {"login": "cashier", "password": DEFAULT_PASSWORD}).json()

        logout = self.client.post(reverse("v1:auth-logout"), {"refresh": tokens["refresh"]})
        refresh = self.client.post(reverse("v1:auth-refresh"), {"refresh": tokens["refresh"]})

        assert logout.status_code == status.HTTP_204_NO_CONTENT
        assert refresh.status_code == status.HTTP_401_UNAUTHORIZED


class TestMe:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee):
        self.user = employee
        self.client = auth_client(employee)
        self.url = reverse("v1:auth-me")

    def test_update_profile_without_password(self):
        response = self.client.patch(self.url, {"full_name": "اسم جديد"})

        assert response.status_code == status.HTTP_200_OK
        self.user.refresh_from_db()
        assert self.user.full_name == "اسم جديد"
        assert self.user.check_password(DEFAULT_PASSWORD)

    def test_change_password_requires_matching_confirmation(self):
        response = self.client.patch(
            self.url, {"password": "New-pass-456", "password_confirmation": "different"}
        )

        assert response.status_code == status.HTTP_400_BAD_REQUEST
        assert "password_confirmation" in response.json()["errors"]

    def test_change_password(self):
        response = self.client.patch(
            self.url, {"password": "New-pass-456", "password_confirmation": "New-pass-456"}
        )

        assert response.status_code == status.HTTP_200_OK
        self.user.refresh_from_db()
        assert self.user.check_password("New-pass-456")

    def test_cannot_change_own_role(self):
        self.client.patch(self.url, {"role": 1})

        self.user.refresh_from_db()
        assert self.user.role == 3


class TestPasswordReset:
    @pytest.fixture(autouse=True)
    def setup(self, api_client):
        self.client = api_client
        self.user = UserFactory(email="reset@example.com")

    def test_full_reset_flow(self):
        response = self.client.post(reverse("v1:auth-password-reset"), {"email": "reset@example.com"})

        assert response.status_code == status.HTTP_200_OK
        assert len(mail.outbox) == 1
        uid, token = re.search(r"reset-password/([^/]+)/(\S+)", mail.outbox[0].body).groups()

        confirm = self.client.post(
            reverse("v1:auth-password-reset-confirm"),
            {
                "uid": uid,
                "token": token,
                "password": "Brand-new-789",
                "password_confirmation": "Brand-new-789",
            },
        )

        assert confirm.status_code == status.HTTP_200_OK
        self.user.refresh_from_db()
        assert self.user.check_password("Brand-new-789")

    def test_unknown_email_gets_same_response_and_no_mail(self):
        response = self.client.post(reverse("v1:auth-password-reset"), {"email": "ghost@example.com"})

        assert response.status_code == status.HTTP_200_OK
        assert mail.outbox == []

    def test_invalid_token_is_rejected(self):
        response = self.client.post(
            reverse("v1:auth-password-reset-confirm"),
            {
                "uid": "MQ",
                "token": "bad",
                "password": "Brand-new-789",
                "password_confirmation": "Brand-new-789",
            },
        )

        assert response.status_code == status.HTTP_400_BAD_REQUEST
