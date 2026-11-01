# ODPC TV – isolierte Entwicklungsumgebung

Stand: 2026-11-01

Dieser Branch `feature/odpc-tv-lab` dient der Entwicklung und Prüfung. Keine produktiven Seiten ändern und keine Veröffentlichung ohne gesonderte Freigabe.

## Ziel
Eine zentrale, eigene und werbefreie Oberfläche für frei zugängliches Live-TV, Mediatheken und Favoriten. Direktwiedergabe nur bei technisch unterstützten und zulässigen Streams.

## Prüfstufen
1. Quelle recherchiert
2. HTTP/Manifest erreichbar
3. Browserwiedergabe mit Bild und Ton getestet
4. Externe Einbettung gestattet
5. Auf deutschem Endgerät getestet

Ein privater Test allein hebt DRM, Zugangskontrollen oder Anbieterbedingungen nicht auf. Keine Zugangsdaten, Cookies oder Tokens in GitHub speichern.

## Nächste Schritte
- Claude-Prototyp 0.2 unter `odpc-tv/` integrieren
- npm install, TypeScript-Build, Unit-Tests und Playwright in GitHub Actions
- Streamprüfung aus Deutschland separat durchführen
- Joyn/waipu/Zattoo nur über offiziell erlaubte Integrationen anbinden

## Status
GitHub-Branch angelegt; Anwendung noch nicht übertragen, kein Build und keine Wiedergabe in diesem Branch bestätigt.
