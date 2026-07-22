# Roadmap – AD Urlaub

Diese Datei bündelt geplante Erweiterungen und offene Produktentscheidungen. Verbindliche Fach-, Sicherheits- und Architekturregeln stehen in `AGENTS.md`.

## Aktueller Fokus

- Jahresmatrix, eigene Anträge, Genehmigungshierarchie und Bereichsgrenzen auf einem realitätsnahen Staging fachlich abnehmen.
- Die Konfliktprüfung gegen AD Kalender und den gültigen Standalone-Betrieb ohne Kalender absichern.
- Sichtbarkeit und Datenschutz von Urlaubsnotizen und Organisationsansichten produktiv prüfen.
- Die additiv migrierten Pflege-, Fahrzeugverwaltungs- und Empfangsansichten samt positiven und negativen Hierarchierechten fachlich abnehmen.

## Geplante Erweiterungen

- Die Jahresmatrix soll Berliner Schulferien als read-only Hintergrundebene anzeigen. Ferien werden klar von persönlichen Urlaubszeiträumen unterschieden, nicht als Urlaub gespeichert und verändern weder Konfliktprüfung, Verfügbarkeit noch Genehmigungsrechte.
- Ferienzeiten müssen zusätzlich zu einer visuellen Hervorhebung mit Namen beziehungsweise zugänglicher Textkennzeichnung erkennbar sein. Die Zuordnung zwischen Person, Datum und Urlaubsstatus muss beim Scrollen weiterhin sichtbar bleiben.
- Weitere Funktionen werden erst aufgenommen, wenn ein konkreter fachlicher Bedarf und der betroffene Rechtevertrag benannt sind.
- Optionale Integrationen bleiben read-only oder verwenden einen ausdrücklich freigegebenen kleinen LocalBase-Vertrag; fehlende Provider bleiben ein gültiger Zustand.

## Vor der Umsetzung zu klären

- Verbindliche, nachvollziehbare Quelle und Aktualisierungsrhythmus für die Berliner Schulferien sowie das Verhalten bei ausbleibenden oder widersprüchlichen Quelldaten.
- Ob Schulferien immer sichtbar sind oder persönlich ein- und ausgeblendet werden können; die Entscheidung darf keine personenbezogenen Daten oder zusätzlichen Urlaubsrechte erzeugen.
- Abgrenzung von Schulferien, gesetzlichen Feiertagen und sonstigen schulfreien Tagen sowie der benötigte Planungshorizont.
- Sichtbarkeit, Bearbeitungsrechte und Datenschutzumfang jeder neuen Urlaubsansicht oder Integration.
- Positive und negative Rechtefälle sowie das Verhalten bei fehlenden optionalen Fachapps.
