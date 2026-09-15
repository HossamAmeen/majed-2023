from decimal import Decimal

import factory

from apps.products.models import Product


class ProductFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = Product

    name = factory.Sequence(lambda n: f"قميص {n}")
    purchasing_price = Decimal("60.00")
    selling_price = Decimal("100.00")
    quantity = 10
    code = factory.Sequence(lambda n: 20000 + n)
