# ODPC Veröffentlichungswege – Betriebsregister (18.10.2026)

Dieses Register dokumentiert nachgewiesene Wege und Grenzen. Keine Passwörter, Tokens oder Mandantendaten speichern.

## 1. ODPC.de – WordPress-Seiten und Beiträge (BESTÄTIGT)
- Verbundener Dienst: WPWriter, Site-ID `4f84272a-73e8-4451-9cd8-f703ecbfa0e9`.
- Suche: `search_posts` mit `site_id` und Begriff; Rückgabe von WordPress-IDs.
- Lesen: `get_post_content` mit `site_id` und `post_id`.
- Kleine Änderungen: `patch_post_content` mit **exaktem** `old_text` aus der aktuellen Seitenfassung und gezieltem `new_text`. Dieser Weg bearbeitet veröffentlichte Seiten unmittelbar. Vorher und nachher lesen; niemals ganze Seiten ohne Not überschreiben.
- Bestätigter erfolgreicher Fall: Seite `https://odpc.de/camping-runkel-lahnstein`, WordPress-ID `2827`, 18.10.2026. ODPC-Partnerhinweis mit Profilbild, Text und `https://odpc.de/` per `patch_post_content` eingefügt. Schnittstelle meldete „Content patched successfully“, genau 1 Ersetzung. Eine öffentliche Frontend-Sichtprüfung muss separat erfolgen.
- Bekannte ODPC-Seiten: `/tante-lenchen` ID 2352; `/anwaltskanzlei-anne-kathrin-justen` ID 2910; Kanzlei-Kundenreferenz ID 3107.
- Diese Methode bearbeitet **Seiteninhalt**, nicht globale Theme-Footer.

## 2. Kanzlei Justen ra-justen.de – eigenständiges STRATO-WordPress
- Separater WordPress-/STRATO-Zugang. Nicht mit ODPC-WordPress verwechseln.
- Theme in früherer Dokumentation: `akj-kanzlei-v1`; tatsächlichen aktiven Stand vor jedem Theme-Upload erneut prüfen.
- Vorbereitete ODPC-Footer-Komponente: `site-deploy/ra-justen.de/odpc-footer-credit.php` im Repository `oliverdering/odpc-sitebot`. Sie wird aus der aktiven `footer.php` unmittelbar vor `wp_footer()` eingebunden.
- Nutzer meldete am 18.10.2026, dass die Footer-Änderung über wiederhergestellten Zugriff durchgeführt wurde. **Nicht** als unabhängig durch diese GitHub-Verbindung verifiziert behandeln.
- Das öffentliche Repository hat bislang keinen nachgewiesenen automatischen Produktiv-Deploy nach ra-justen.de. Keine Vorschau-Deployment-Workflows mit Produktiv-Deploy verwechseln. Keine STRATO-Passwörter in öffentlichen GitHub-Dateien.
- Für weitere Theme-Änderungen: aktiven Footer lesen, Backup erstellen, punktuell ändern, Frontend und Link prüfen. Nur authentifizierte, tatsächlich erreichbare Verbindung verwenden.

## 3. Tante Lenchen
- ODPC-Projektseite `https://odpc.de/tante-lenchen`, WordPress-ID `2352` über den ODPC-WPWriter-Zugang.
- Eigenständige Website/Deployment-Wege nur nach separater Bestätigung. Projektseite ist nicht automatisch der eigenständige Webauftritt.

## 4. Weitere Sites/Subdomains
- `ai.odpc.de`: separates WordPress-Theme; GitHub-Datei `site-deploy/ai.odpc.de/odpc-helpermonkey-v4.php`; IONOS-SFTP-Probe und Pfadermittlung in `docs/deployment-multi-site.md`, produktiver Deploy nicht pauschal bestätigt.
- `ra-justen.odpc.de`: öffentliche Vorschau, nicht die produktive `ra-justen.de`.
- ATRIUM 191: eigenes Repository `oliverdering/atrium191-odpc-` und SFTP-Pipeline; Zugang auf `/Atrium` beschränkt, keinesfalls für andere Domains einsetzen.
- Weitere Kunden-/Subdomain-Zugriffe einzeln nachweisbar dokumentieren; nie aus bloßer Existenz eines GitHub-Entwurfs auf erfolgreiche Veröffentlichung schließen.

## Betriebsstandard bei Zuruf
1. Domain und Inhaltstyp identifizieren (ODPC-Seite, eigenständiges WordPress, Theme, statische Site).
2. Passenden **nachgewiesenen** Schreibweg wählen und vor jeder Änderung aktuellen Stand lesen.
3. Backup/Snapshot; minimalinvasive Änderung; veröffentlichen.
4. Live-URL prüfen (HTTP, sichtbarer Inhalt, Links, Bild, mobile Darstellung), Ergebnis mit präzisem Status melden.
5. Neue funktionierende Wege in diesem Register nachtragen. Keine Zugangsdaten oder Secrets in GitHub-Dokumentation speichern.
