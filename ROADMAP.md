# Roadmap – AD Urlaub

Diese Datei bündelt geplante Erweiterungen und offene Produktentscheidungen. Verbindliche Fach-, Sicherheits- und Architekturregeln stehen in `AGENTS.md`.

## Aktueller Fokus

- Jahresmatrix, eigene Anträge, Genehmigungshierarchie und Bereichsgrenzen auf einem realitätsnahen Staging fachlich abnehmen.
- Die Konfliktprüfung gegen AD Kalender und den gültigen Standalone-Betrieb ohne Kalender absichern.
- Sichtbarkeit und Datenschutz von Urlaubsnotizen und Organisationsansichten produktiv prüfen.
- Die additiv migrierten Pflege-, Fahrzeugverwaltungs- und Empfangsansichten samt positiven und negativen Hierarchierechten fachlich abnehmen.

## Umgesetzte Berliner Ferien und Feiertage

- Die Jahresmatrix zeigt Berliner Schulferien und gesetzliche Feiertage als getrennte flache read-only Bänder im fixierten Tabellenkopf. Namen stehen direkt im jeweiligen Farbstreifen, ohne Tagesspalten zu verbreitern; zu lange Beschriftungen werden gekürzt und bleiben per Tooltip und zugänglicher Beschriftung vollständig verfügbar. Samstage sind leicht grau, Sonntage dunkler; gesetzliche Feiertage verwenden in der vollständigen Spalte dieselbe Grauebene wie Sonntage. Wochenendart, Ferien- und Feiertagsnamen bleiben zusätzlich in der zugänglichen Tagesbeschriftung erhalten.
- Ferien und Feiertage werden nicht als Urlaub gespeichert und verändern weder Konfliktprüfung, Verfügbarkeit, Genehmigung noch Rechte. Persönliche Urlaubsstatus bleiben unabhängig sichtbar.
- Der Server lädt beide Datensätze gemeinsam für `DE-BE` und auf Deutsch aus der OpenHolidays API. Die fest hinterlegte HTTPS-Quelle ist schlüssellos; ihre auf öffentlichen Quellen beruhenden Daten werden unter ODbL genutzt.
- Normalisierte Jahresdaten werden lazy in der Nextcloud-AppConfig-Datenbank gespeichert, täglich für das aktuelle und die zwei folgenden Jahre aktualisiert und für weitere angefragte Jahre bedarfsgesteuert geladen. Bei Ausfällen bleibt der letzte erfolgreiche Cache sichtbar; Wiederholungen werden kurzzeitig gedrosselt und der Status transparent angezeigt.

## Geplante Erweiterungen

- Weitere Funktionen werden erst aufgenommen, wenn ein konkreter fachlicher Bedarf und der betroffene Rechtevertrag benannt sind.
- Optionale Integrationen bleiben read-only oder verwenden einen ausdrücklich freigegebenen kleinen LocalBase-Vertrag; fehlende Provider bleiben ein gültiger Zustand.
