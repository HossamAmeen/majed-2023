import pytest
from django.urls import reverse
from rest_framework import status

from apps.notes.models import Note

pytestmark = pytest.mark.django_db


class TestNotes:
    @pytest.fixture(autouse=True)
    def setup(self, auth_client, employee, master):
        self.employee = employee
        self.employee_client = auth_client(employee)
        self.master_client = auth_client(master)
        self.list_url = reverse("v1:notes-list")

    def test_employee_can_add_and_list_notes(self):
        created = self.employee_client.post(self.list_url, {"note": "اطلب بضاعة"})
        listed = self.employee_client.get(self.list_url)

        assert created.status_code == status.HTTP_201_CREATED
        assert created.json()["created_by_name"] == self.employee.user_name
        assert listed.json()["results"][0]["note"] == "اطلب بضاعة"

    def test_only_master_can_edit_or_delete(self):
        note = Note.objects.create(note="قديم", created_by=self.employee)
        url = reverse("v1:notes-detail", args=[note.pk])

        employee_edit = self.employee_client.patch(url, {"note": "x"})
        employee_delete = self.employee_client.delete(url)
        master_edit = self.master_client.patch(url, {"note": "جديد"})
        master_delete = self.master_client.delete(url)

        assert employee_edit.status_code == status.HTTP_403_FORBIDDEN
        assert employee_delete.status_code == status.HTTP_403_FORBIDDEN
        assert master_edit.json()["note"] == "جديد"
        assert master_delete.status_code == status.HTTP_204_NO_CONTENT
