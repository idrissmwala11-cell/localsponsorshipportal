from __future__ import annotations

import sys
from pathlib import Path


PAGE_WIDTH = 595
PAGE_HEIGHT = 842
LEFT = 50
RIGHT = 50
TOP = 60
BOTTOM = 60
FONT_SIZE = 11
LINE_HEIGHT = 15
MAX_WIDTH = PAGE_WIDTH - LEFT - RIGHT


def escape_pdf_text(value: str) -> str:
    return value.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")


def wrap_line(text: str, limit: int = 86) -> list[str]:
    if not text:
        return [""]

    result: list[str] = []
    current = ""

    for word in text.split():
        proposal = word if not current else f"{current} {word}"
        if len(proposal) <= limit:
            current = proposal
            continue

        if current:
            result.append(current)
            current = word
        else:
            result.append(word[:limit])
            remainder = word[limit:]
            while len(remainder) > limit:
                result.append(remainder[:limit])
                remainder = remainder[limit:]
            current = remainder

    if current:
        result.append(current)

    return result or [""]


def markdown_to_lines(markdown: str) -> list[tuple[str, int]]:
    lines: list[tuple[str, int]] = []
    in_code = False

    for raw in markdown.splitlines():
        stripped = raw.rstrip()

        if stripped.startswith("```"):
            in_code = not in_code
            lines.append(("", FONT_SIZE))
            continue

        if in_code:
            content = stripped if stripped else " "
            for part in wrap_line(content, 82):
                lines.append((part, FONT_SIZE))
            continue

        if not stripped:
            lines.append(("", FONT_SIZE))
            continue

        if stripped.startswith("# "):
            for part in wrap_line(stripped[2:].upper(), 70):
                lines.append((part, 16))
            lines.append(("", FONT_SIZE))
            continue

        if stripped.startswith("## "):
            for part in wrap_line(stripped[3:], 76):
                lines.append((part, 13))
            lines.append(("", FONT_SIZE))
            continue

        if stripped.startswith("### "):
            for part in wrap_line(stripped[4:], 80):
                lines.append((part, 12))
            continue

        if stripped.startswith("- "):
            wrapped = wrap_line(stripped[2:], 80)
            lines.append((f"- {wrapped[0]}", FONT_SIZE))
            for part in wrapped[1:]:
                lines.append((f"  {part}", FONT_SIZE))
            continue

        if stripped[:2].isdigit() and stripped[1:3] == ". ":
            wrapped = wrap_line(stripped, 80)
            lines.extend((part, FONT_SIZE) for part in wrapped)
            continue

        for part in wrap_line(stripped, 86):
            lines.append((part, FONT_SIZE))

    return lines


def build_content_stream(pages: list[list[tuple[str, int]]]) -> bytes:
    streams: list[bytes] = []

    for page in pages:
        parts = ["BT", "/F1 11 Tf", f"{LEFT} {PAGE_HEIGHT - TOP} Td"]
        current_y = PAGE_HEIGHT - TOP

        for text, size in page:
            if current_y != PAGE_HEIGHT - TOP:
                parts.append(f"0 -{LINE_HEIGHT} Td")
            current_y -= LINE_HEIGHT

            safe = escape_pdf_text(text)
            parts.append(f"/F1 {size} Tf")
            parts.append(f"({safe}) Tj")

        parts.append("ET")
        streams.append("\n".join(parts).encode("latin-1", errors="replace"))

    return streams


def paginate(lines: list[tuple[str, int]]) -> list[list[tuple[str, int]]]:
    max_lines = (PAGE_HEIGHT - TOP - BOTTOM) // LINE_HEIGHT
    pages: list[list[tuple[str, int]]] = []
    current: list[tuple[str, int]] = []

    for line in lines:
        if len(current) >= max_lines:
            pages.append(current)
            current = []
        current.append(line)

    if current:
        pages.append(current)

    return pages


def create_pdf(input_path: Path, output_path: Path) -> None:
    markdown = input_path.read_text(encoding="utf-8")
    lines = markdown_to_lines(markdown)
    pages = paginate(lines)
    content_streams = build_content_stream(pages)

    objects: list[bytes] = []

    def add_object(data: bytes) -> int:
        objects.append(data)
        return len(objects)

    font_id = add_object(b"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>")

    page_ids: list[int] = []
    content_ids: list[int] = []

    placeholder_pages_id = add_object(b"<< >>")

    for stream in content_streams:
        content_id = add_object(
            f"<< /Length {len(stream)} >>\nstream\n".encode("latin-1")
            + stream
            + b"\nendstream"
        )
        content_ids.append(content_id)

        page_id = add_object(
            (
                f"<< /Type /Page /Parent {placeholder_pages_id} 0 R "
                f"/MediaBox [0 0 {PAGE_WIDTH} {PAGE_HEIGHT}] "
                f"/Resources << /Font << /F1 {font_id} 0 R >> >> "
                f"/Contents {content_id} 0 R >>"
            ).encode("latin-1")
        )
        page_ids.append(page_id)

    kids = " ".join(f"{page_id} 0 R" for page_id in page_ids)
    objects[placeholder_pages_id - 1] = (
        f"<< /Type /Pages /Kids [{kids}] /Count {len(page_ids)} >>".encode("latin-1")
    )

    catalog_id = add_object(f"<< /Type /Catalog /Pages {placeholder_pages_id} 0 R >>".encode("latin-1"))

    pdf = bytearray(b"%PDF-1.4\n")
    offsets = [0]

    for index, obj in enumerate(objects, start=1):
        offsets.append(len(pdf))
        pdf.extend(f"{index} 0 obj\n".encode("latin-1"))
        pdf.extend(obj)
        pdf.extend(b"\nendobj\n")

    xref_offset = len(pdf)
    pdf.extend(f"xref\n0 {len(objects) + 1}\n".encode("latin-1"))
    pdf.extend(b"0000000000 65535 f \n")

    for offset in offsets[1:]:
        pdf.extend(f"{offset:010} 00000 n \n".encode("latin-1"))

    pdf.extend(
        (
            f"trailer\n<< /Size {len(objects) + 1} /Root {catalog_id} 0 R >>\n"
            f"startxref\n{xref_offset}\n%%EOF"
        ).encode("latin-1")
    )

    output_path.write_bytes(pdf)


def main() -> int:
    if len(sys.argv) != 3:
        print("Usage: python scripts/generate_system_structure_pdf.py <input.md> <output.pdf>")
        return 1

    input_path = Path(sys.argv[1])
    output_path = Path(sys.argv[2])
    output_path.parent.mkdir(parents=True, exist_ok=True)
    create_pdf(input_path, output_path)
    print(f"PDF created: {output_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
