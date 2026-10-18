# ra-justen.de: verifizierter Veröffentlichungsweg

Stand: 18.10.2026. Betriebsanweisung von Oliver Dering: bestehende autorisierte Zugänge zuerst wiederverwenden, keine wiederholten Freigabefragen für bereits beauftragte Änderungen; keine kostenpflichtigen Dienste und kein WPVibe.

## Verifizierte produktive Umgebung

- Produktive Website: https://ra-justen.de/
- WordPress-Admin: https://ra-justen.de/wp-admin/
- Hosting: STRATO. STRATO-Kundenlogin und WordPress-Login sind getrennte Konten; eine STRATO-Kundennummer ist kein WordPress-Benutzername.
- Am 18.10.2026 nach manueller Anmeldung erfolgreich geprüfte Admin-Sitzung: Dashboard zeigte „Willkommen, admin“.
- Aktives Theme: `akj-kanzlei-v1` (Kanzlei Justen | ODPC, Version 1.4.1).
- Direkter Editor: https://ra-justen.de/wp-admin/theme-editor.php?file=footer.php&theme=akj-kanzlei-v1
- Kein STRATO-Kundenlogin und kein WPVibe erforderlich, solange diese WordPress-Admin-Sitzung gültig ist und der Editor zugänglich bleibt.
- `ra-justen.odpc.de` ist eine andere Umgebung und darf nicht mit der produktiven STRATO-Website verwechselt werden.

## Vorgehen bei erneutem Auftrag

1. Vorhandenen Browser-Tab und authentifizierte WordPress-Sitzung prüfen. Einen Login nicht allein wegen eines neuen Chats als erforderlich annehmen. Erfolg anhand des aktuellen Dashboards verifizieren.
2. Aktives Theme und konkrete Datei live lesen. Vor jeder Änderung die unveränderte Datei sichern.
3. Nur die beauftragte Änderung vornehmen. Keinen Theme-Wechsel, keine Pluginänderungen und keine Änderungen an anderen Layout- oder Inhaltsbestandteilen ohne entsprechenden Auftrag.
4. CodeMirror-Editor: einfaches `fill` kann vorhandenen Code stehen lassen. Sicher funktionierte: gesamten Editorinhalt mit Strg+A auswählen, Backspace, leeren Zustand prüfen, dann neuen Inhalt einsetzen. Vor Speichern auf verdoppelten Code prüfen.
5. „Datei aktualisieren“ und WordPress-Erfolgsmeldung prüfen. Ein Klick allein ist kein Erfolgsnachweis.
6. Live-Seite frisch öffnen, Bild tatsächlich laden lassen, Linkziel und Darstellung prüfen. Falls erforderlich den vorhandenen Cache über die WordPress-Oberfläche leeren. Öffentliche Auslieferung nicht allein aus dem Editor ableiten.
7. Bei Browser-Zugriffsbeschränkungen der aktuell ausgegebenen Wiederherstellungsanweisung folgen; keine Zugangssperren umgehen. Nicht gespeicherte Zwischenstände nicht mit veröffentlichten Änderungen verwechseln.

## Erfolgreiche Änderung am 18.10.2026

- Vorlage: `site-deploy/ra-justen.de/odpc-footer-credit.php`.
- Unveränderte Originaldatei: `backups/ra-justen.de/2026-10-18-before-footer-credit/akj-kanzlei-v1/footer.php`.
- Backup-Commit: `ba1be8f4cd74a2116315d056d3f95561541f1725`.
- HTML aus der Vorlage direkt vor `<?php wp_footer(); ?>` ergänzt. Sonstiger Footer blieb erhalten.
- Hinweis: „Webdesign, Technik & KI-Integration“ / „Powered by odpc.de · Oliver Dering“.
- Profilfoto: https://odpc.de/wp-content/uploads/2026/08/1786371119667.png.webp
- Bild und Text verlinken auf https://odpc.de/ mit `target="_blank"` und `rel="noopener noreferrer"`.
- WordPress bestätigte erfolgreiche Bearbeitung. Partnerhinweis wurde live gesehen, Profilfoto lud mit naturalWidth 768. Zielseite https://odpc.de/ war erreichbar.
- Bereits vorhandener weiterer Kooperationshinweis unterhalb des neuen Blocks wurde nicht entfernt.

## Authentifizierung und Grenzen

Passwörter, Sitzungscookies, OTPs und andere Geheimnisse gehören nicht in dieses Dokument oder ins Repository. Für weitere Websites nur tatsächlich verifizierte Zugangswege dokumentieren, keine Konten oder Zugangserfolge erfinden.

Bestehende Sitzungen wiederverwenden. Falls die Website erneut eine Anmeldung verlangt, den verfügbaren sicheren Anmeldeweg nutzen. Browser-Anmeldedaten nicht über Chat oder niedrigere Browser-APIs eintragen. Eine notwendige manuelle Anmeldung/2FA kann nicht dauerhaft ausgeschlossen werden. Nur bei einem konkreten, nicht selbst lösbaren Authentifizierungsblock den Nutzer beteiligen und den genauen Grund nennen.
