from typing import Any, Protocol


class LegacySource(Protocol):
    def has_table(self, table: str) -> bool: ...

    def rows(self, table: str) -> list[dict[str, Any]]: ...


class MySQLLegacySource:
    """Reads the old Laravel tables straight from MySQL / MariaDB."""

    ALLOWED_TABLES = frozenset({"users", "products", "bills", "orders", "notes", "configrations"})

    def __init__(self, *, host: str, port: int, user: str, password: str, database: str) -> None:
        import pymysql
        from pymysql.cursors import DictCursor

        self._connection = pymysql.connect(
            host=host,
            port=port,
            user=user,
            password=password,
            database=database,
            charset="utf8mb4",
            cursorclass=DictCursor,
        )

    def has_table(self, table: str) -> bool:
        with self._connection.cursor() as cursor:
            cursor.execute("SHOW TABLES LIKE %s", (table,))
            return cursor.fetchone() is not None

    def rows(self, table: str) -> list[dict[str, Any]]:
        if table not in self.ALLOWED_TABLES:
            raise ValueError(f"Unexpected legacy table {table!r}")
        with self._connection.cursor() as cursor:
            cursor.execute(f"SELECT * FROM `{table}` ORDER BY id")  # noqa: S608  # nosec B608
            return list(cursor.fetchall())

    def close(self) -> None:
        self._connection.close()
