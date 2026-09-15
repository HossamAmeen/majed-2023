import os
from typing import Any

from django.core.management.base import BaseCommand, CommandError, CommandParser

from apps.legacy_import.services import LegacyImportError, LegacyImportService
from apps.legacy_import.sources import MySQLLegacySource


class Command(BaseCommand):
    help = (
        "One-time copy of the old Laravel MySQL database into this (empty) database. "
        "Connection settings fall back to LEGACY_DB_HOST, LEGACY_DB_PORT, LEGACY_DB_USER, "
        "LEGACY_DB_PASSWORD and LEGACY_DB_NAME."
    )

    def add_arguments(self, parser: CommandParser) -> None:
        parser.add_argument("--host", default=os.environ.get("LEGACY_DB_HOST", "127.0.0.1"))
        parser.add_argument("--port", type=int, default=int(os.environ.get("LEGACY_DB_PORT", "3306")))
        parser.add_argument("--user", default=os.environ.get("LEGACY_DB_USER", "root"))
        parser.add_argument("--database", default=os.environ.get("LEGACY_DB_NAME"))

    def handle(self, *args: Any, **options: Any) -> None:
        if not options["database"]:
            raise CommandError("Pass --database or set LEGACY_DB_NAME.")

        source = MySQLLegacySource(
            host=options["host"],
            port=options["port"],
            user=options["user"],
            password=os.environ.get("LEGACY_DB_PASSWORD", ""),
            database=options["database"],
        )
        try:
            stats = LegacyImportService(source).run()
        except LegacyImportError as exc:
            raise CommandError(str(exc)) from exc
        finally:
            source.close()

        for table, count in stats.items():
            self.stdout.write(f"{table}: {count}")
        self.stdout.write(self.style.SUCCESS("Legacy import finished."))
