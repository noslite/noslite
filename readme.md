# NOS lite
Tekstversie van nos.nl. Geen plaatjes, video's, liveblogs, scripts, trackers en cookies.

`noslite.py` (Python 3.9+, alleen de standaardbibliotheek) haalt elke minuut de NOS-feed op en schrijft
`site/index.html` en `site/l/<id>.html`, plus `.gz`-versies voor nginx `gzip_static`.
