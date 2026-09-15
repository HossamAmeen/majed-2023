import factory

from apps.users.models import User
from utils.enums import Role

DEFAULT_PASSWORD = "Secret-pass-123"


class UserFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = User
        skip_postgeneration_save = True

    user_name = factory.Sequence(lambda n: f"user{n}")
    full_name = factory.Faker("name")
    email = factory.Sequence(lambda n: f"user{n}@example.com")
    role = Role.EMPLOYEE
    password = factory.PostGenerationMethodCall("set_password", DEFAULT_PASSWORD)

    @factory.post_generation
    def save_password(obj: User, create: bool, extracted: object, **kwargs: object) -> None:  # type: ignore[misc]
        if create:
            obj.save()
