import sqlite3

import pytest
from django.core.management import call_command

from apps.common.tasks import backup_database
from apps.products.tests.factories import ProductFactory


@pytest.mark.django_db(transaction=True)
class TestDatabaseBackup:
    @pytest.fixture(autouse=True)
    def setup(self, settings, tmp_path):
        settings.BACKUP_DIR = tmp_path
        self.backup_dir = tmp_path
        ProductFactory(name="نسخة")

    def test_command_writes_a_readable_copy(self):
        call_command("backup_database")

        [backup] = self.backup_dir.glob("*.sqlite3")
        with sqlite3.connect(backup) as connection:
            names = connection.execute("SELECT name FROM products_product").fetchall()
        assert names == [("نسخة",)]

    def test_celery_task_returns_the_backup_path(self):
        path = backup_database.delay().get()

        assert path.endswith(".sqlite3")
