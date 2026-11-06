# D.R.O.L. Datenbank-Anbindung – Entwicklungsstand

Die IONOS-Datenbank wurde am 06.11.2026 geprüft: zunächst keine Tabellen; danach vier D.R.O.L.-Tabellen und ein erstes Pixhawk-6C-Profil mit zwei Quellen angelegt. Das öffentliche Frontend liest diese Daten derzeit nicht aus.

catalog.php ist ein vorbereiteter, lesender JSON-Endpunkt. Keine Schreiboperationen, keine Flugsteuerung. Konfiguration ausschließlich serverseitig über DROL_DB_HOST, DROL_DB_NAME, DROL_DB_USER und DROL_DB_PASSWORD. Keine Zugangsdaten im Repository oder im Browser. Bei fehlender Konfiguration oder Fehlern wird ein allgemeiner 503-Status zurückgegeben.

Noch offen: tatsächliche Verfügbarkeit der Servervariablen und PDO-MySQL auf dem IONOS-Webspace prüfen; ausschließlich lesenden Datenbanknutzer einrichten, falls verfügbar; Secrets geschützt außerhalb öffentlich abrufbarer Pfade verwalten; Datenbank- und Fehlerfalltests ausführen; Deployment ausdrücklich für diesen PHP-Endpunkt erweitern; Frontend und sichere Fehlerbehandlung integrieren; anschließend Live-Funktion kontrollieren. Der aktuelle D.R.O.L.-Deploy überträgt nur HTML und diese Datei wird damit nicht veröffentlicht.

Nicht als fertig oder aktiv ausgeben. Die erste Fassung begrenzt die Ausgabe auf 100 Hardwareprofile; Paginierung, mehrsprachige Daten und vollständige Quellen-/Faktenversionierung sind weitere Arbeitsschritte. 
