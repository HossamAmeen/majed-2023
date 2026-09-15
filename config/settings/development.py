from config.settings.base import *  # noqa: F403
from config.settings.base import build_mailers, env

SECRET_KEY = env("SECRET_KEY", default="dev-only-insecure-secret-key")
DEBUG = env.bool("DEBUG", default=True)
ALLOWED_HOSTS = env.list("ALLOWED_HOSTS", default=["localhost", "127.0.0.1"])

CORS_ALLOWED_ORIGINS = env.list(
    "CORS_ALLOWED_ORIGINS",
    default=["http://localhost:5173", "http://127.0.0.1:5173"],
)

MAILERS = build_mailers(env("EMAIL_BACKEND", default="django.core.mail.backends.console.EmailBackend"))
