from django.db.models import QuerySet

from apps.users.models import User


class UserSelector:
    @staticmethod
    def list() -> QuerySet[User]:
        return User.objects.order_by("-id")

    @staticmethod
    def active_by_email(email: str) -> User | None:
        matches = list(User.objects.filter(email__iexact=email, is_active=True)[:2])
        return matches[0] if len(matches) == 1 else None
