# OMO.DENT — Website (PHP/HTML/CSS/JS)

Komplett neue, eigenständige Website mit eigenem Design, eigener
Farbwelt und eigenem Dashboard — unabhängig von anderen Projekten.

## Hochladen auf Hostinger
1. Gesamten Inhalt dieses Ordners nach `public_html/` (oder einen
   Unterordner) hochladen.
2. Schreibrechte (755/775) sicherstellen für: `data/`, `uploads/`,
   `uploads/pdfs/`, `uploads/messages/`.
3. Fertig — keine Datenbank nötig, alle Inhalte liegen in
   `data/content-de.json` (+ `content-en/ar/ku.json` für Übersetzungen).

## Dashboard
- Aufrufen: `https://ihredomain.de/admin/`
- Benutzername: `admin`
- Passwort: `OmoDent-f4f30606!Zx`

**Bitte das Passwort nach dem ersten Login ändern** (neuen Hash mit
`php -r "echo password_hash('NEUES_PASSWORT', PASSWORD_DEFAULT);"`
erzeugen und in `data/users.json` eintragen).

Im Dashboard bearbeitbar:
- Praxisname, Logo-Text, Arzt-Name, Kontakt (inkl. Fax), Öffnungszeiten, Karte
- **Farbwelt** (6 Farben — Logo, Header, Footer, Icons)
- Menü-Beschriftungen
- Startseite: Hero, 3 Merkmal-Karten, Leistungen-Vorschau, „Über
  mich"-Vorschau, Kontakttexte
- Leistungen: beliebig viele Leistungen mit Bild, Titel, beliebig
  vielen FAQ-Fragen und Galerie-Bildern (alle hinzufügbar/entfernbar)
- Über mich: Foto, Biografie, Zitat, Ausbildung/Qualifikationen
  (hinzufügbar/entfernbar), 3 Merkmal-Karten
- PDF-Downloads: Button-Text/-Farben + 4 herunterladbare Dokumente
- Datenschutz & Impressum: Abschnitte mit Zeilen (kein HTML nötig)
- Beschriftungen: alle festen Texte (Knöpfe, Formular, Meldungen)
- Übersetzungen (EN / AR / KU): jedes Textfeld der Website, fehlende Felder rot markiert
- Nachrichten: eingegangene Kontaktanfragen

## Foto des Arztes
Das Foto von A. Mustermann wurde als Platzhalter angelegt (Über-mich-Seite
und Startseiten-Vorschau) — bitte im Dashboard unter „Über mich" →
„Foto" hochladen, sobald das Bild vorliegt.

## PDF-Downloads
Der Button im Hero-Bereich öffnet ein Fenster mit 4 Dokumenten —
funktioniert rein über HTML/CSS (kein JavaScript nötig, daher
besonders zuverlässig). Dateien werden im Dashboard unter
„PDF-Downloads" hochgeladen.

## Kontaktformular
`send_mail.php` nutzt PHP `mail()` und sichert jede Anfrage zusätzlich
in `data/messages.json` (sichtbar im Dashboard unter „Nachrichten").

## Sicherheit
- `data/`, `uploads/messages/`, `admin/includes/` sind per
  `.htaccess` vor direktem Zugriff geschützt.
- Dashboard-Login per Session + bcrypt-Passwort-Hash.
