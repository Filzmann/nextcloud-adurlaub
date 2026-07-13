# AGENTS.md - AD Urlaub

## Projekt

Nextcloud-App `adurlaub` fuer geplante und genehmigte Urlaubszeitraeume aller AD-Fachgruppen.

Lokale App-URL:

    https://nextcloud-dev.ddev.site/apps/adurlaub/

## Fachvertrag

- Urlaube sind ganztägige, inklusive Datumsbereiche je Nextcloud-UID.
- `planned` wird als `U?` angezeigt und ist ein Hinweis ohne Schreib- oder Verfügbarkeitsblockade.
- `approved` wird als `U` angezeigt und blockiert Dienste, Termine, Standarddienste und Meetingverfügbarkeit im AD Kalender.
- Alle angemeldeten Nutzer*innen lesen alle Urlaube der gemeinsamen AD-Fachgruppen.
- Nutzer*innen planen eigene Urlaube. Genehmigungen und die Bearbeitung genehmigter Urlaube folgen derselben Vorgesetztenhierarchie wie AD Kalender; Nextcloud-Admins dürfen alle verwalten. Direkte Kolleg*innen dürfen nach administrativer Freischaltung innerhalb derselben Fachgruppe genehmigen, bei BO/EB nur im selben Bürobereich. Eigene Genehmigung bleibt außer für Nextcloud-Admins gesperrt.
- Genehmigungen mit überschneidenden Diensten oder Terminen werden mit einer read-only Konfliktliste abgelehnt; es erfolgt keine automatische Löschung.
- Gruppen stammen aus derselben konfigurierbaren `AdOrganizationDefinition` wie AD Kalender und AdPlaner. Fachrollen, Bereiche, Assistenzteam-Präfix und Organisationssichten werden nicht zusätzlich in AD Urlaub festverdrahtet.
- AD Urlaub ist die kanonische schreibende Urlaubsquelle. Die Jahresmatrix fasst dynamische Assistenzteams und die konfigurierten Organisationssichten zusammen.
- Assistenzteams verwenden dieselbe Nextcloud-Gruppe wie AdPlaner. Separate Gruppen mit einem Suffix wie `-Urlaub` sind keine unterstützte Datenquelle.
- Eigene Urlaubszeiträume werden kompakt über Von/Bis/Notiz eingetragen. Berechtigte Koordinator*innen wechseln den Tagesstatus direkt in der Jahresmatrix; Konflikte werden inline angezeigt.
- AdPlaner bindet seine Urlaubssicht ausschließlich an diese Quelle an und besitzt keine parallele Urlaubspersistenz.
- Der read-only Cross-App-Vertrag ist `OCA\LocalBase\Calendar\AbsenceQueryEvent` mit `AbsenceInterval`. AD Urlaub greift niemals direkt auf Tabellen anderer Apps zu.

## Architektur und Sicherheit

- Controller bleiben dünn; Rechte liegen in `VacationAccessService`, Fachlogik in `VacationService`, Datenzugriff im Repository.
- Jeder schreibende API-Pfad prüft serverseitig Zielperson und Besitz/Adminrecht. UI-Ausblendungen sind kein Schutz.
- Urlaubsnotizen verbleiben in AD Urlaub und werden nicht an konsumierende Apps übertragen.
- App-Root und Tabellenwrapper erfüllen den Nextcloud-Scrollvertrag der Parent-`AGENTS.md`.
- Modelle nutzen `get(...)`, `get_all([...])` und `toArray()`.
- Dauerhafte App- und Gruppenfreigaben liegen ausschliesslich im eigenen Tab `Einstellungen`; die Urlaubsplanung bleibt davon als Hauptansicht getrennt.

## Gemeinsame Suite-Navigation

- AD Urlaub besitzt keinen eigenen Nextcloud-Hauptnavigationseintrag. `orgsuite` stellt den gemeinsamen Einstieg `AD` bereit.
- Das Template bindet das zentrale OrgSuite-Menue mit `data-suite="ad"` und `data-current-app="adurlaub"` ein.
- Urlaubs-, Team- und Genehmigungsrechte bleiben ausschliesslich serverseitig im AD Urlaub; Menuesichtbarkeit ist keine Berechtigung.

## Git und Tests

- Eigenständiges Git-Repository; gezielt stagen, nie `git add .`.
- Vor Commits Status, Diff-Statistik und Dateiliste zeigen.
- Schnelle Tests: `php tests/run.php` und `node tests/run-js.mjs`.
- Migration/DI zusätzlich in DDEV prüfen.

## DDEV

Mount: `/var/www/html/html/custom_apps/adurlaub`

Migrationen laufen über `occ app:enable adurlaub` beziehungsweise `occ upgrade`.
