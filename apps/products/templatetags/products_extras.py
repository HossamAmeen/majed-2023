from decimal import Decimal

from django import template

register = template.Library()


@register.filter
def times(value: Decimal, multiplier: int) -> Decimal:
    return value * multiplier
