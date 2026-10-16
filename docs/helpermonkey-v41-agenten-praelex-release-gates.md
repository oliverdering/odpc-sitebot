# ODPC / Helper Monkey – Produkt- und Integrationsregister (Arbeitsstand 2026-10-16)

**Wichtig:** Dieses Dokument ist eine überprüfbare Inventur der im GitHub-Repository vorhandenen Artefakte, kein Nachweis über eine vollständig funktionsfähige Kundeninstallation. Nicht belegte Funktionen bleiben ausdrücklich ungeprüft.

## Markenführung
- **odpc.de:** ODPC-Dachmarke, Dunkelblau/Sand, eigenes Titelbild und Navigation. Helper Monkey hier nur als farblich integrierter Verweis.
- **ai.odpc.de:** eigenständiger grüner Helper-Monkey-Auftritt.
- Vorhandene WhatsApp-Business-Schaltflächen nicht umgestalten oder doppelt einblenden.

## Im Repository vorhandene Download-Artefakte
| Agent / Werkzeug | Vorhandene Versionspakete | Live- und Funktionstest |
|---|---|---|
| Bewerbungs-Starter | multilingual v1.0 und v3.1 ZIP | End-to-End-Bewerbung nicht nachgewiesen; Versand nur nach Freigabe |
| Connector-Starter | multilingual v1.0 und v3.1 ZIP | Kein automatischer Kontozugriff allein durch Prompt/ZIP |
| Lebenslauf-Starter | multilingual v1.0 und v3.1 ZIP | Export, Sprachen und Formatierung prüfen |
| Postfach-Starter | multilingual v1.0 und v3.1 ZIP | OAuth/Berechtigungen und sichere Schreibaktionen prüfen |
| Gesprächsübersetzer | multilingual v1.0 ZIP, v1.0 TXT | Spracherkennung und Übersetzungsqualität prüfen |
| Sprachmodul | v1.0 TXT | Browser-Kompatibilität und Mikrofonfreigabe prüfen |

Dateien unter `downloads/` sind Versionsartefakte. ZIP-Datei vorhanden bedeutet **nicht**, dass sie installiert oder als autonomer Agent betriebsbereit ist.

## Agenten-FAQ-Mindeststandard (für jede Produktseite separat)
1. Was kann der Agent **nachweislich heute** und was nicht?
2. Ist es ein Prompt/Starterpaket, ein Browserwerkzeug oder ein laufender Hintergrunddienst?
3. Welche Konten, Geräte, Apps und Berechtigungen werden benötigt?
4. Welche Daten werden wohin übertragen, gespeichert oder protokolliert?
5. Welche Aktionen sind lesend, welche verändern Daten, und wann ist eine Freigabe erforderlich?
6. Was kostet die Nutzung einschließlich externer Dienste?
7. Wie starte ich, wie erkenne ich Erfolg und wie stoppe/widerrufe ich den Zugriff?
8. Welche Fehlerbilder und Grenzen sind bekannt?
9. Wo finde ich aktuelle Version, Changelog und überprüften Download?
10. Wie erreiche ich menschliche Hilfe?

Ein generischer FAQ-Block mit drei Fragen ersetzt diese agentenspezifischen Angaben nicht.

## PraeLex.ai / Kanzlei Justen – verbindliches Release-Gate
- Produktbezeichnung **PraeLex.ai** konsistent verwenden.
- Abgrenzen: Fachkonzept / Demo / Pilot / tatsächlich produktive Kanzleianbindung.
- Vor Anbindung: dokumentierte Autorisierung der Kanzlei, Rollen- und Zugriffsmodell, getrennte Mandantendaten, Auftragsverarbeitung und Datenschutzprüfung.
- Keine Mandantenakten, E-Mail-Inhalte, Zugangsdaten, OAuth-Tokens oder interne Kanzlei-URLs in dieses **öffentliche** Repository schreiben.
- Juristische Ausgaben nur als überprüfbare Fachvorarbeit, keine autonome Rechtsberatung oder ungeprüfte Mandantenkommunikation.
- Testreihenfolge: synthetische Beispieldaten → Testumgebung → autorisierter Pilot → dokumentierte Freigabe.
- Abnahmefälle: Akten-/Dokumentenimport, Herkunftsnachweis, Rechteprüfung, Löschung, Fehlerrückgabe, Auditlog, menschliche Freigabe.
- **Aktueller Status: Integration nicht als produktiv verifiziert.**

## Release-Kriterien
- Live-URLs HTTP/Browser/Mobil geprüft; Navigation und Interview bedienbar.
- Keine doppelten WhatsApp-Buttons; ODPC Blau/Sand und Helper Monkey Grün bleiben getrennt.
- Alle ZIPs lesbar; veröffentlichte Downloadlinks führen zur korrekten Version.
- Jede beworbene Agentenfunktion hat einen dokumentierten realen Funktionstest.
- FAQs und Angebots-/Preisangaben auf allen Seiten konsistent.
- Kanzlei-Referenz nur mit Freigabe und ohne vertrauliche technische Details.
- Deployment, Backup und Rollback nachgewiesen; GitHub Actions grün.
