# Kanzlei Justen – technischer Härtungs- und Abnahmeplan (16.10.2026)

## Geltungsbereich
Live: https://ra-justen.de/ · WordPress · Theme „Kanzlei Justen | ODPC“ v1.4.1.
Kein GitHub-Autodeployment für diese Domain im Repository vorhanden. Keine Liveänderung durch dieses Dokument.

## Gemessener Iststand (WPVibe Lighthouse mobil)
Performance 85, Accessibility 97, Best Practices 100, SEO 100.
LCP 4,3 s, CLS 0, TBT 0 ms, FCP 1,3 s.
Barrierefreiheitsbefunde: color-contrast, label-content-name-mismatch.
Lighthouse Best Practices ist kein Penetrationstest.

## Erfasste Oberflächen
Startseite: Kanzlei-/Fachanwaltsdarstellung, Tätigkeitsgebiete, VdK-Trennung, Kontakt, Telefon, Mail, WhatsApp, Terminanfrage, Partnerlinks.
Rechtsseiten: /impressum/ (WP ID 15), /datenschutz/ (WP ID 16), Inhalte fachlich noch nicht abschließend validiert.
Terminanfrage: Clientseitiger mailto/WhatsApp-Weg; kein serverseitiges Terminreservierungssystem; Google-Kalender-Widget verborgen.
WhatsApp: nur organisatorische Anfragen, keine Mandatsunterlagen; Warnhinweis bereits vorhanden.

## Sicherheitsfreigabe – offene Prüfungen
1. Backup und Rückspielprobe vor Änderungen; Theme v1.4.1 und Plugins inventarisieren.
2. WordPress-Core-/Plugin-Checksums, Updatebedarf und aktive Plugins prüfen; ungenutzte Plugins nach Bestätigung entfernen.
3. Admin-Konten, Rollen, 2FA, Passwort-/Recovery-Prozesse und Application Passwords prüfen, ohne Geheimnisse offenzulegen.
4. TLS, HSTS, CSP (kompatibel mit Site Kit), X-Content-Type-Options, Referrer-Policy und Frame-Schutz gegen reale Browser-Responses prüfen.
5. REST-API-/XML-RPC-Exposition bewerten, Rate-Limits und Login-Schutz; keine pauschalen Abschaltungen ohne Funktionstest.
6. WordPress Debug/Logs, Fehlerseiten, Datei- und Verzeichnisrechte, öffentlich erreichbare Backups und Verzeichnislisting prüfen.
7. Impressum: Kanzlei-/Berufsträgerdaten, Kammer, Zulassung, Berufsrecht, Berufshaftpflicht, Kontakt, Streitbeilegung und Pflichtangaben gegen Freigabe abgleichen.
8. Datenschutz: Hosting, Kontakt per E-Mail/WhatsApp, Google Site Kit/Analytics/Tag Manager, Cookies, Einwilligung, AV-Verträge, Löschfristen und Betroffenenrechte prüfen.
9. Fachanwaltstitel und Tätigkeitsangaben gegen Kanzleifreigabe verifizieren; VdK organisatorisch getrennt halten.
10. Keine echten Mandatsdaten in Helper Monkey/PraeLex-Testabläufen; schriftliche Freigabe, Rechtekonzept, AV/Geheimnisschutz, dokumentierte Tests vor Produktivbetrieb.

## Verbesserungen ohne Designbruch
- CSS-Farbkontrast gezielt an betroffenen Elementen korrigieren; Farben des bestehenden Kanzleidesigns bewahren.
- Sichtbare Label und aria-label von betroffenen Bedienelementen abgleichen, Tastaturbedienung und Fokus testen.
- Hero-Porträt (aktuell loading=lazy) für LCP prüfen; bei tatsächlich größtem sichtbaren Element loading=eager und fetchpriority=high testen; Bildkomprimierung/Abmessungen/Cache-Control prüfen.
- Terminwunsch: Datum in Vergangenheit verhindern, Formularvalidierung und tatsächliches Öffnen von Mail-/WhatsApp-App auf Android/iOS prüfen; keine Reservierung suggerieren.
- Telefon-, Mail-, WhatsApp- und Kartenlinks auf mobilen Endgeräten testen.
- Doppelte OG-Image-Metatags aus Head prüfen und ggf. Quelle gezielt bereinigen.

## Freigabekriterien
Keine Live-Freigabe ohne Backup, mobile Browserprüfung, vollständige Kontakt-/Rechtstextprüfung und dokumentierte Funktionstests. KI-Mandatsintegration separat durch Kanzlei freizugeben.

## Blocker
WPVibe Free: rolling-24h-Limit erreicht; laut Rückmeldung erneute Nutzung etwa ab 16.10.2026 08:10 UTC / 10:10 MESZ.
