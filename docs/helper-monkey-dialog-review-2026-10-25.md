# Helper Monkey: sofortiger Dialog-Durchlauf, 25.10.2026

## Auftrag

Die Weiterentwicklung jetzt durchführen. Keine Verschiebung auf den nächsten
geplanten Lauf. Layout, CSS und Original-Logos unverändert erhalten.

## Festgestellte Probleme

- Die acht angebotenen Sprachen übersetzten bisher vor allem die Fragen. Viele
  Antwortschaltflächen, Agentennamen, Fortschrittsanzeigen und Übergabetexte
  blieben Deutsch oder Englisch.
- Bei fehlender Gerätestimme wurden unabhängig von Gerät und gewählter Sprache
  Samsung-Einstellungen und deutsche Sprachdaten empfohlen.
- „Neu starten“ und „Zurück“ konnten eine andere gespeicherte Sprache hinterlassen
  als die sichtbare Auswahl. Ein Sprachparameter der Einstiegs-URL überschrieb
  beim Neuladen die später bewusst gewählte Sprache.

## Umgesetzt

1. Gemeinsame Dialogtexte für Deutsch, Englisch, Spanisch, Französisch,
   Italienisch, Portugiesisch, Polnisch und Türkisch. Agententitel,
   Antwortschaltflächen, Fortschritt, Kopieren, Übergabe, Datenschutz-Hinweis,
   WhatsApp-Schrittbezug und Sprach-/Mikrofonstatus verwenden diese Texte.
   Die eigentliche Kontofreigabe bleibt beim Anbieter und die KI-Arbeit im
   vorhandenen KI-Konto. Die gesamte Webseite wird dadurch nicht übersetzt.
2. Die Hilfe bei fehlender Gerätestimme folgt dem gewählten Gerät:
   Android/Samsung, iPhone/iPad, Computer oder ein allgemeiner Hinweis, wenn
   kein Gerät bekannt ist. Keine pauschale Empfehlung deutscher Sprachdaten.
   Weiterhin keine künstliche Ersatzstimme und keine neue kostenpflichtige API.
3. Sprache bleibt beim Neustart, Zurückgehen und Neuladen erhalten. Ein explizit
   neu besuchter Sprachlink darf die Sprache initialisieren; beim Neuladen bleibt
   eine danach ausgewählte Sprache bestehen. Sichtbare übersetzte Agententitel
   lassen sich auch eintippen, ohne dass sie einem anderen Agenten zugeordnet werden.

## Technische Umsetzung

`dialog-copy.js` liefert die gemeinsamen Texte und wird als Abhängigkeit vor
dem Dialogkern geladen. Der bestehende Release sichert die zusätzliche Datei,
prüft ihre öffentliche Auslieferung bytegenau und berücksichtigt sie beim
Rollback. Keine neuen Kontozugriffe, Berechtigungen oder externen Nachrichten.

## Herstellerabgleich für die Gerätehilfe

- Samsung: https://www.samsung.com/us/support/answer/ANS10003701/
- Apple: https://support.apple.com/en-gb/guide/iphone/iph96b214f0/27/ios/27

Geprüft am 25.10.2026. Die Formulierung vermeidet starre Menüpfade, da Bezeichnungen
je nach Betriebssystemversion variieren. Für Computer wird keine konkrete
Windows- oder macOS-Version unterstellt.

## Prüfung

Vor Veröffentlichung: 53 JavaScript- und 8 Python-Prüfungen bestanden. Darunter
alle acht Sprachen im identischen Verbindungsablauf, übersetzte Agententitel,
vollständige Dialogtexte, Platzhalterersetzung und fehlende Gerätestimmen für
acht Sprachen mit vier Gerätevarianten.

Die Live-Prüfung umfasst zusätzlich auf Desktop und Mobilgerät alle acht
Sprachauswahlen, Kopiertexte, Fortschritt, Neustart/Neuladen und Sprachlinks.
Physische Mikrofon- und Hörtests bleiben separat zu prüfen. Die Arbeitsaufträge
werden weiterhin an das eigene KI-Konto übergeben; keine Behauptung eines
modellübergreifenden Tests tatsächlich generierter KI-Antworten.

Release- und Live-Nachweis werden nach der Abnahme ergänzt.
