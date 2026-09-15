from typing import Any

from django.contrib.auth.backends import ModelBackend
from django.http import HttpRequest

from apps.users.models import User


class UserNameOrEmailBackend(ModelBackend):
    """Sign in with either `user_name` or `email`, like the original login form."""

    def authenticate(
        self,
        request: HttpRequest | None,
        username: str | None = None,
        password: str | None = None,
        **kwargs: Any,
    ) -> User | None:
        login = username or kwargs.get("login") or kwargs.get(User.USERNAME_FIELD)
        if not login or password is None:
            return None

        user = User.objects.filter(user_name=login).first()
        if user is None and "@" in login:
            matches = list(User.objects.filter(email__iexact=login)[:2])
            user = matches[0] if len(matches) == 1 else None

        if user is None:
            # Run the hasher anyway so response time does not reveal whether the user exists.
            User().set_password(password)
            return None

        if user.check_password(password) and self.user_can_authenticate(user):
            return user
        return None
