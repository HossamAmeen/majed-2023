from django.db import models

from utils.constants import MONEY_DECIMAL_PLACES, MONEY_MAX_DIGITS


def money_field(**kwargs: object) -> models.DecimalField:  # type: ignore[type-arg]
    """A DecimalField with the project's standard precision for prices and totals."""
    return models.DecimalField(
        max_digits=MONEY_MAX_DIGITS,
        decimal_places=MONEY_DECIMAL_PLACES,
        **kwargs,  # type: ignore[arg-type]
    )


def money_output_field() -> models.DecimalField:  # type: ignore[type-arg]
    return models.DecimalField(max_digits=MONEY_MAX_DIGITS, decimal_places=MONEY_DECIMAL_PLACES)
