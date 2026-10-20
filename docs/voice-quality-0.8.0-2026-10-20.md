# Sprachprofil 0.8.0 – natürliche Gerätestimmen statt eSpeak

Anlass: Die synthetische Ersatzstimme wurde vom Auftraggeber als ungeeignet abgelehnt. Anforderungen sind eine natürliche Frauenstimme, ruhiges Tempo und geduldige Erklärung, ohne neue kostenpflichtige Dienste.

## Ursache

0.7.0 akzeptierte nur Stimmnamen, denen ein Geschlecht per Namensliste zugeordnet werden konnte. Allgemeine Profile wie `Google Deutsch` wurden verworfen. Der Browser fiel dann auf eSpeak-NG zurück. Diese Rückfalllogik erzeugte zwar Audio, erfüllte aber die gewünschte Stimmqualität nicht. Ein nicht-stummes Signal belegt keine angenehme oder natürliche Stimme.

## Änderung

- Weiblich ist die Standardeinstellung.
- Passende natürliche/neuronale, erweiterte und hochwertige Gerätestimmen werden nach den vom Browser gemeldeten Profilnamen bevorzugt. Beispielsweise gewinnt eine vorhandene Katja Online (Natural) gegenüber einer klassischen Hedda. Ein bestimmter Profilname wird nicht als auf allen Geräten verfügbar zugesichert.
- Allgemeine Profile wie Google Deutsch bleiben als Systemstimme verfügbar. Der Browser liefert dafür kein verlässliches Geschlecht; dies wird angezeigt. Bekannte Männerstimmen werden bei weiblicher Auswahl ausgeschlossen.
- In den erweiterten Einstellungen kann die tatsächlich verfügbare Gerätestimme ausdrücklich gewählt und angehört werden.
- Tempo 0,94, unveränderte natürliche Tonhöhe (1,0), kurze Pausen zwischen Sätzen. Kein künstliches Hochstellen der Stimme.
- Kein automatischer eSpeak-Rückfall. Fehlen passende Gerätestimmen oder schlägt ihre Wiedergabe fehl, zeigt der Assistent den Grund und lässt Texteingabe zu.
- Verzögerte Stimmkataloge erhalten eine kurze Wartezeit. Stopp verhindert nachfolgende Sätze.
- Bisherige eSpeak-Dateien bleiben unverändert auf dem Server, werden vom neuen Sprachdialog aber nicht geladen.

## Kostenfreie Möglichkeiten und Grenzen

1. Vorhandene Browser-/Systemstimmen verwenden. Das ist die unmittelbar umgesetzte Lösung. Verfügbarkeit hängt vom Gerät und Browser ab.
2. Auf Samsung sind Samsung- und Google-Sprachmodule sowie installierbare Sprachdaten über die Geräteinstellungen verfügbar. Die Webseite kann die Smartphone-Systemeinstellungen nicht selbst ändern. Ob eine bestimmte Systemstimme dem Browser angezeigt wird, muss am Gerät geprüft werden.
3. Windows/Apple können zusätzliche hochwertige Profile bereitstellen. Im Browser werden nur die tatsächlich gemeldeten Stimmen verwendet; eine Stimme aus einer separaten Vorlesefunktion wird nicht automatisch als Web-Speech-Stimme angenommen.
4. Eine identische Premiumstimme auf jedem Browser wäre mit diesem rein gerätebasierten Weg nicht garantiert. Kein kostenpflichtiger Cloud-TTS-Dienst, kein Abo und keine nutzungsabhängige API wurden eingerichtet.

„Einfühlsam“ ist eine Wahrnehmung und keine Web-Speech-Eigenschaft. Der ruhige Ablauf unterstützt das gewünschte Verhalten; eine physische Hörprobe auf dem Zielgerät bleibt notwendig.

## Quellen, geprüft am 20.10.2026

- https://developer.mozilla.org/en-US/docs/Web/API/SpeechSynthesis/getVoices
- https://www.samsung.com/us/support/answer/ANS10003701/
- https://support.google.com/accessibility/android/answer/6006983?hl=de
- https://support.microsoft.com/en-us/edge/use-immersive-reader-in-microsoft-edge

## Qualitätssicherung

27 JavaScript-Testgruppen und acht Python-Regressionstests bestehen vor Veröffentlichung. Die Tests decken natürliche weibliche Priorisierung, Google-Profile ohne Geschlechtskennung, explizite Auswahl, verzögerte Kataloge, acht Sprachen, ruhiges Tempo, Satzpausen, Stopp und fehlende Roboterrückfälle ab.

Die Browserpipeline prüft fehlende Gerätestimmen als Verfügbarkeitsgrenze und benennt synthetische Testkataloge ausdrücklich als solche. Sie behauptet in diesem Fall keine akustische Abnahme.
