from django.db import migrations


def create_configuration(apps, schema_editor):
    ShopConfiguration = apps.get_model("dashboard", "ShopConfiguration")
    ShopConfiguration.objects.get_or_create(pk=1)


class Migration(migrations.Migration):
    dependencies = [("dashboard", "0002_initial")]

    operations = [migrations.RunPython(create_configuration, migrations.RunPython.noop)]
