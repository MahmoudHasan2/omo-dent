# OMO.DENT — Dental Practice Website & CMS

![PHP](https://img.shields.io/badge/PHP-8-777BB4?logo=php&logoColor=white) ![i18n](https://img.shields.io/badge/i18n-DE%20%C2%B7%20EN%20%C2%B7%20AR%20%C2%B7%20KU-1565C0)

> Dental practice website in German, English, Arabic and Kurdish, with its own content dashboard.

## Overview
A production project built and deployed by **Mahmoud Hasan**. This repository holds the source code only. The client's secrets,
databases, uploaded files and personal data are not included; where the code needs them, they are replaced with placeholders.

**Tech stack:** PHP 8 · JSON storage · Vanilla JS · CSS

## Features
- Four languages, Arabic right-to-left; one JSON content file per language
- Pages: home, services with detail pages, team, about the dentist, contact, Impressum, Datenschutz
- Dashboard: block editor for page content, translations, image and PDF upload, contact messages, password change
- Contact form that sends e-mail
- `.htaccess` blocks the data folders from the web

## Project structure
```
index.php leistungen.php leistung-detail.php team.php ueber-mich.php
includes/            header, footer, loader, helpers
admin/               dashboard, block editor, translations, uploads
data/content-*.json  content per language
assets/              CSS + JS
```

## Getting started
Upload to a PHP 8 host and log in at `/admin`.

## Security & privacy
- No credentials in the repository. API keys, passwords and database settings are placeholders (`CHANGE_ME`, `YOUR_…`, `your_db_password`).
- Runtime data (customers, messages, appointments, logs, uploads) is left out.
- Personal names, phone numbers and addresses in the default content are replaced with sample data (e.g. Max Mustermann, +49 151 00000000, Musterstraße 1).

## Author
**Mahmoud Hasan** · [GitHub](https://github.com/MahmoudHasan2)
