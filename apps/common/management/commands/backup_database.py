from pathlib import Path
from typing import Any

from django.core.management.base import BaseCommand, CommandParser

from apps.common.services import DatabaseBackupService


class Command(BaseCommand):
    help = "Copy the SQLite database into BACKUP_DIR (runs twice a day through Celery beat)."

    def add_arguments(self, parser: CommandParser) -> None:
        parser.add_argument("--dir", type=Path, default=None, help="Override BACKUP_DIR.")

    def handle(self, *args: Any, **options: Any) -> None:
        target = DatabaseBackupService.backup(options["dir"])
        self.stdout.write(self.style.SUCCESS(f"Backup written to {target}"))
