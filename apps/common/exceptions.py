import logging
from typing import Any

from rest_framework import status
from rest_framework.exceptions import APIException, ValidationError
from rest_framework.response import Response
from rest_framework.views import exception_handler

logger = logging.getLogger(__name__)


class BusinessRuleError(APIException):
    """A request that is well-formed but breaks a business rule (e.g. not enough stock)."""

    status_code = status.HTTP_400_BAD_REQUEST
    default_detail = "لا يمكن تنفيذ هذه العملية"
    default_code = "business_rule"


def _first_message(errors: Any) -> str:
    if isinstance(errors, dict):
        for value in errors.values():
            return _first_message(value)
    if isinstance(errors, list) and errors:
        return _first_message(errors[0])
    return str(errors)


def api_exception_handler(exc: Exception, context: dict[str, Any]) -> Response | None:
    """
    Every error body has the same shape:

        {"detail": "<one human readable message>", "errors": {<field errors>} | null}
    """
    response = exception_handler(exc, context)

    if response is None:
        logger.exception("Unhandled API error", exc_info=exc)
        return Response(
            {"detail": "حدث خطأ غير متوقع، حاول مرة أخرى", "errors": None},
            status=status.HTTP_500_INTERNAL_SERVER_ERROR,
        )

    if isinstance(exc, ValidationError):
        response.data = {"detail": _first_message(response.data), "errors": response.data}
    else:
        detail = response.data.get("detail") if isinstance(response.data, dict) else None
        response.data = {"detail": str(detail or _first_message(response.data)), "errors": None}

    return response
