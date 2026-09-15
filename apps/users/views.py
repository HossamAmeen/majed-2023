from typing import Any

from drf_spectacular.utils import extend_schema, extend_schema_view
from rest_framework import generics, status, viewsets
from rest_framework.permissions import AllowAny, IsAuthenticated
from rest_framework.request import Request
from rest_framework.response import Response
from rest_framework.serializers import BaseSerializer
from rest_framework.throttling import ScopedRateThrottle
from rest_framework_simplejwt.views import TokenObtainPairView

from apps.common.permissions import IsMaster
from apps.users.filters import UserFilter
from apps.users.models import User
from apps.users.selectors import UserSelector
from apps.users.serializers import (
    AccountSerializer,
    LoginResponseSerializer,
    LoginSerializer,
    LogoutSerializer,
    MessageSerializer,
    PasswordResetConfirmSerializer,
    PasswordResetRequestSerializer,
    UserSerializer,
    UserWriteSerializer,
)
from apps.users.services import AccountService, AuthService, PasswordResetService, UserService


@extend_schema(tags=["auth"], responses=LoginResponseSerializer)
class LoginView(TokenObtainPairView):
    """Sign in with a user name or an email address."""

    serializer_class = LoginSerializer  # type: ignore[assignment]
    throttle_classes = [ScopedRateThrottle]
    throttle_scope = "login"


@extend_schema(tags=["auth"], request=LogoutSerializer, responses={204: None})
class LogoutView(generics.GenericAPIView):  # type: ignore[type-arg]
    """
    Blacklist the refresh token so it cannot be used again.

    Holding the refresh token is enough, so logout still works after the access token expired.
    """

    serializer_class = LogoutSerializer
    permission_classes = [AllowAny]
    authentication_classes: list[type] = []

    def post(self, request: Request) -> Response:
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        AuthService.logout(serializer.validated_data["refresh"])
        return Response(status=status.HTTP_204_NO_CONTENT)


@extend_schema_view(
    get=extend_schema(tags=["auth"], responses=UserSerializer),
    patch=extend_schema(tags=["auth"], request=AccountSerializer, responses=UserSerializer),
    put=extend_schema(tags=["auth"], request=AccountSerializer, responses=UserSerializer),
)
class MeView(generics.RetrieveUpdateAPIView):  # type: ignore[type-arg]
    """The signed-in user's own account (replaces the old "edit account" page)."""

    permission_classes = [IsAuthenticated]

    def get_object(self) -> User:
        return self.request.user  # type: ignore[return-value]

    def get_serializer_class(self) -> type[BaseSerializer]:  # type: ignore[type-arg]
        return UserSerializer if self.request.method == "GET" else AccountSerializer

    def update(self, request: Request, *args: Any, **kwargs: Any) -> Response:
        user = self.get_object()
        serializer = AccountSerializer(
            user, data=request.data, partial=kwargs.get("partial", False), context={"request": request}
        )
        serializer.is_valid(raise_exception=True)
        user = AccountService.update_account(user=user, data=serializer.validated_data)
        return Response(UserSerializer(user).data)


@extend_schema(tags=["auth"], request=PasswordResetRequestSerializer, responses=MessageSerializer)
class PasswordResetRequestView(generics.GenericAPIView):  # type: ignore[type-arg]
    """Email a reset link. The response is the same whether or not the email exists."""

    serializer_class = PasswordResetRequestSerializer
    permission_classes = [AllowAny]
    authentication_classes: list[type] = []
    throttle_classes = [ScopedRateThrottle]
    throttle_scope = "password_reset"

    def post(self, request: Request) -> Response:
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        PasswordResetService.send_reset_email(serializer.validated_data["email"])
        return Response(
            {"detail": "اذا كان البريد مسجلا فستصلك رسالة لاعادة تعيين كلمة المرور"},
        )


@extend_schema(tags=["auth"], request=PasswordResetConfirmSerializer, responses=MessageSerializer)
class PasswordResetConfirmView(generics.GenericAPIView):  # type: ignore[type-arg]
    serializer_class = PasswordResetConfirmSerializer
    permission_classes = [AllowAny]
    authentication_classes: list[type] = []
    throttle_classes = [ScopedRateThrottle]
    throttle_scope = "password_reset"

    def post(self, request: Request) -> Response:
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        PasswordResetService.reset_password(
            uid=serializer.validated_data["uid"],
            token=serializer.validated_data["token"],
            password=serializer.validated_data["password"],
        )
        return Response({"detail": "تم تغيير كلمة المرور بنجاح"})


@extend_schema_view(
    list=extend_schema(tags=["users"]),
    retrieve=extend_schema(tags=["users"]),
    create=extend_schema(tags=["users"], request=UserWriteSerializer, responses={201: UserSerializer}),
    update=extend_schema(tags=["users"], request=UserWriteSerializer, responses=UserSerializer),
    partial_update=extend_schema(tags=["users"], request=UserWriteSerializer, responses=UserSerializer),
    destroy=extend_schema(tags=["users"]),
)
class UserViewSet(viewsets.ModelViewSet):  # type: ignore[type-arg]
    """Manage staff accounts. Master (role 1) only."""

    permission_classes = [IsMaster]
    filterset_class = UserFilter
    search_fields = ["user_name", "full_name", "email", "phone"]
    ordering_fields = ["id", "user_name", "full_name", "role", "created_at"]

    def get_queryset(self):  # type: ignore[no-untyped-def]
        return UserSelector.list()

    def get_serializer_class(self) -> type[BaseSerializer]:  # type: ignore[type-arg]
        if self.action in {"create", "update", "partial_update"}:
            return UserWriteSerializer
        return UserSerializer

    def create(self, request: Request, *args: Any, **kwargs: Any) -> Response:
        serializer = self.get_serializer(data=request.data)
        serializer.is_valid(raise_exception=True)
        user = UserService.create_user(actor=request.user, data=serializer.validated_data)  # type: ignore[arg-type]
        return Response(UserSerializer(user).data, status=status.HTTP_201_CREATED)

    def update(self, request: Request, *args: Any, **kwargs: Any) -> Response:
        instance = self.get_object()
        serializer = self.get_serializer(instance, data=request.data, partial=kwargs.pop("partial", False))
        serializer.is_valid(raise_exception=True)
        user = UserService.update_user(
            actor=request.user, user=instance, data=serializer.validated_data  # type: ignore[arg-type]
        )
        return Response(UserSerializer(user).data)

    def perform_destroy(self, instance: User) -> None:
        UserService.delete_user(actor=self.request.user, user=instance)  # type: ignore[arg-type]
