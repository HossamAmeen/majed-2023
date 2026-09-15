from decimal import Decimal

import factory
from django.utils import timezone

from apps.products.tests.factories import ProductFactory
from apps.sales.models import Bill, Order


class BillFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = Bill

    name = factory.Faker("name")
    phone = "01000000000"
    discount = Decimal("0")


class OrderFactory(factory.django.DjangoModelFactory):
    class Meta:
        model = Order

    bill = factory.SubFactory(BillFactory)
    product = factory.SubFactory(ProductFactory)
    product_name = factory.LazyAttribute(lambda o: o.product.name if o.product else "منتج")
    product_price = factory.LazyAttribute(lambda o: o.product.selling_price if o.product else Decimal(100))
    price = factory.LazyAttribute(lambda o: o.product_price)
    quantity = 1
    discount = Decimal("0")
    date = factory.LazyFunction(timezone.localdate)
