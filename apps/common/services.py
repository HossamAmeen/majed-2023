import logging
import sqlite3
from pathlib import Path

from django.conf import settings
from django.db import connection
from django.utils import timezone

logger = logging.getLogger(__name__)


class DatabaseBackupService:
    @staticmethod
    def backup(target_dir: Path | None = None) -> Path:
        """
        Copy the live SQLite database to `<BACKUP_DIR>/<YYYY-MM-DD>-<HHMM>.sqlite3`.

        Uses SQLite's online backup API, so it is safe while the app is writing.
        """
        if connection.vendor != "sqlite":
            raise RuntimeError("Database backups are only implemented for SQLite.")

        target_dir = target_dir or settings.BACKUP_DIR
        target_dir.mkdir(parents=True, exist_ok=True)
        target = target_dir / f"{timezone.localtime():%Y-%m-%d-%H%M}.sqlite3"

        connection.ensure_connection()
        source = connection.connection
        with sqlite3.connect(target) as destination:
            source.backup(destination)
        destination.close()

        logger.info("Database backup written to %s", target)
        return target
