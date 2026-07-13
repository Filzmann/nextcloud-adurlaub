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
- Gruppen stammen aus demselben kanonischen Vertrag wie AD Kalender und AdPlaner: Fachrollen `ad-*` und getrennte Bereiche `ad-Bereich-*`; keine kombinierten Altgruppen als Datenquelle.
- Der read-only Cross-App-Vertrag ist `OCA\LocalBase\Calendar\AbsenceQueryEvent` mit `AbsenceInterval`. AD Urlaub greift niemals direkt auf Tabellen anderer Apps zu.

## Architektur und Sicherheit

- Controller bleiben dünn; Rechte liegen in `VacationAccessService`, Fachlogik in `VacationService`, Datenzugriff im Repository.
- Jeder schreibende API-Pfad prüft serverseitig Zielperson und Besitz/Adminrecht. UI-Ausblendungen sind kein Schutz.
- Urlaubsnotizen verbleiben in AD Urlaub und werden nicht an konsumierende Apps übertragen.
- App-Root und Tabellenwrapper erfüllen den Nextcloud-Scrollvertrag der Parent-`AGENTS.md`.
- Modelle nutzen `get(...)`, `get_all([...])` und `toArray()`.

## Git und Tests

- Eigenständiges Git-Repository; gezielt stagen, nie `git add .`.
- Vor Commits Status, Diff-Statistik und Dateiliste zeigen.
- Schnelle Tests: `php tests/run.php` und `node tests/run-js.mjs`.
- Migration/DI zusätzlich in DDEV prüfen.

## DDEV

Mount: `/var/www/html/html/custom_apps/adurlaub`

Migrationen laufen über `occ app:enable adurlaub` beziehungsweise `occ upgrade`.
