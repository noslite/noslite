#!/usr/bin/env python3
"""NOS lite: turns the NOS news feed into small static HTML pages (plus .gz copies for nginx's gzip_static).

Usage: noslite.py [feed-file-or-url] [site-dir]   (defaults: the NOS feed, ./site next to this script)
Only the Python standard library is used.
"""
import gzip
import html
import os
import re
import sys
import urllib.request
import xml.etree.ElementTree as ET
from datetime import datetime
from pathlib import Path
from zoneinfo import ZoneInfo

FEED = "https://feeds.nos.nl/nosnieuwsalgemeen"
ROOT = Path(__file__).resolve().parent
TEMPLATES = ROOT / "templates"
TZ = ZoneInfo("Europe/Amsterdam")

# Unicode whitespace, as matched by \s in PHP's PCRE with the /u flag (excludes \x1c-\x1f, unlike Python's \s)
WS = "\t\n\v\f\r \x85\xa0  -     　"
# Blocks whose contents must not have their whitespace collapsed; <style> contents are minified as CSS instead
RAW_BLOCK = re.compile(r"(<(style|script|pre|textarea)\b[^>]*>)(.*?)(</\2>)", re.S | re.I)


def escape(s: str) -> str:
    return html.escape(s, quote=True).replace("&#x27;", "&#039;")


def minify_css(css: str) -> str:
    css = re.sub(r"/\*.*?\*/", "", css, flags=re.S)
    css = re.sub(r"\s+", " ", css)
    css = re.sub(r" ?([{};]) ?", r"\1", css)
    css = re.sub(r"([^{};]+)\{", lambda m: re.sub(r" ?, ?", ",", m[1]) + "{", css)  # selector lists
    css = re.sub(r"(?<=[{;])([\w-]+): ", r"\1:", css)  # declarations
    return css.replace(";}", "}").strip()


def collapse_whitespace(s: str) -> str:
    s = re.sub(f"[{WS}]+", " ", s)
    s = s.replace(" >", ">").replace("< ", "<").replace("> <", "><")
    return s.strip(" \t\n\r\0\x0b")


def compress_html(doc: str) -> str:
    out, chunk, pos = [], "", 0
    for m in RAW_BLOCK.finditer(doc):
        out.append(collapse_whitespace(chunk + doc[pos:m.start()] + m[1]))
        out.append(minify_css(m[3]) if m[2].lower() == "style" else m[3])
        chunk, pos = m[4], m.end()
    out.append(collapse_whitespace(chunk + doc[pos:]))
    return "".join(out)


def render(name: str, **values) -> str:
    css = (TEMPLATES / f"{name}.css").read_text()
    return compress_html((TEMPLATES / f"{name}.html").read_text().format(css=css, **values))


def fetch(source: str) -> bytes:
    if "://" not in source:
        return Path(source).read_bytes()
    with urllib.request.urlopen(source, timeout=30) as response:
        return response.read()


def parse_feed(xml: bytes) -> list[dict]:
    items = []
    for item in ET.fromstring(xml).iterfind("channel/item"):
        link = item.findtext("link", "")
        article_id = link[len("https://nos.nl/l/"):]
        if not article_id.isascii() or not article_id.isdigit():
            raise ValueError(f"unexpected article link: {link!r}")
        items.append({
            "id": article_id,
            "external_link": link,
            "title": item.findtext("title", "").strip(" \t\n\r\0\x0b"),
            "content": item.findtext("description", ""),
        })
    return items


def persist(path: Path, text: str) -> None:
    """Write path and path.gz atomically, so nginx never serves a half-written page."""
    data = text.encode()
    for target, payload in ((path, data), (path.with_name(path.name + ".gz"), gzip.compress(data, 9, mtime=0))):
        tmp = target.with_name(f".{target.name}.tmp")
        tmp.write_bytes(payload)
        os.replace(tmp, target)


def main() -> None:
    source = sys.argv[1] if len(sys.argv) > 1 else FEED
    site = Path(sys.argv[2]) if len(sys.argv) > 2 else ROOT / "site"
    items = parse_feed(fetch(source))

    for item in items:
        persist(site / "l" / f"{item['id']}.html", render(
            "article", title=item["title"], content=item["content"], external_link=escape(item["external_link"])))

    links = "".join(f'<li><a href="/l/{item["id"]}.html">{item["title"]}</a></li>' for item in items)
    persist(site / "index.html", render("index", items=links, updated=datetime.now(TZ).strftime("%H:%M")))


if __name__ == "__main__":
    main()
