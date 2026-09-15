from celery import shared_task

from apps.common.services import DatabaseBackupService


@shared_task
def backup_database() -> str:
    return str(DatabaseBackupService.backup())
