# API documentation

The OpenAPI schema is generated from the code by drf-spectacular. With the server running:

- Swagger UI: <http://127.0.0.1:8000/api/docs/>
- ReDoc: <http://127.0.0.1:8000/api/redoc/>
- Raw schema: <http://127.0.0.1:8000/api/schema/>

Export a static copy with `python manage.py spectacular --file docs/api/schema.yml`.

Authentication: `POST /api/v1/auth/login/`, then send `Authorization: Bearer <access>`.
In Swagger UI, click **Authorize** and paste the access token.

Business rules behind each endpoint are described in [../workflows/](../workflows/).
