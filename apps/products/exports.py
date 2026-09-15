from collections.abc import Iterable
from io import BytesIO

from django.template.loader import render_to_string
from django.utils import timezone
from openpyxl import Workbook
from openpyxl.styles import Font

from apps.products.models import Product

EXCEL_HEADERS = [
    "الاسم",
    "الكود",
    "سعر الشراء",
    "سعر البيع",
    "الكمية",
    "سعر الشراء الكلى",
    "سعر البيع الكلى",
]


class ProductExcelExporter:
    @staticmethod
    def build(products: Iterable[Product]) -> bytes:
        workbook = Workbook()
        sheet = workbook.active
        sheet.title = "المنتجات"  # type: ignore[union-attr]
        sheet.sheet_view.rightToLeft = True  # type: ignore[union-attr]
        sheet.append(EXCEL_HEADERS)  # type: ignore[union-attr]
        for cell in sheet[1]:  # type: ignore[index]
            cell.font = Font(bold=True)

        for product in products:
            sheet.append(  # type: ignore[union-attr]
                [
                    product.name,
                    product.code,
                    float(product.purchasing_price),
                    float(product.selling_price),
                    product.quantity,
                    float(product.purchasing_price * product.quantity),
                    float(product.selling_price * product.quantity),
                ]
            )

        buffer = BytesIO()
        workbook.save(buffer)
        return buffer.getvalue()


class ProductPdfExporter:
    @staticmethod
    def build(products: Iterable[Product], title: str) -> bytes:
        # Imported lazily: WeasyPrint loads native libraries (Pango) at import time.
        from weasyprint import HTML

        html = render_to_string(
            "products/products_pdf.html",
            {"products": list(products), "title": title, "generated_at": timezone.localtime()},
        )
        return HTML(string=html).write_pdf()  # type: ignore[no-any-return]
