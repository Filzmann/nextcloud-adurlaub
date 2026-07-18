# AGENTS.md - AD Urlaub

## Projekt

Nextcloud-App `adurlaub` fuer geplante und genehmigte Urlaubszeitraeume aller AD-Fachgruppen.

Lokale App-URL:

    https://nextcloud-dev.ddev.site/apps/adurlaub/

Die priorisierte Produktplanung und offene Entscheidungen stehen in `ROADMAP.md`; verbindliche Fach-, Sicherheits- und Architekturregeln bleiben in dieser Datei.

## Fachvertrag

- Urlaube sind ganztägige, inklusive Datumsbereiche je Nextcloud-UID.
- Urlaubszeiträume derselben Person dürfen sich unabhängig vom Status nicht überschneiden. Bei Änderungen wird der bearbeitete Datensatz selbst von der Prüfung ausgenommen; angrenzende Zeiträume bleiben erlaubt.
- `planned` wird als `U?` angezeigt und ist ein Hinweis ohne Schreib- oder Verfügbarkeitsblockade.
- `approved` wird als `U` angezeigt und blockiert Dienste, Termine, Standarddienste und Meetingverfügbarkeit im AD Kalender.
- Normale Nutzer*innen lesen nur Urlaubsansichten, in denen sie selbst Mitglied sind, gemeinsame Assistenzteams sowie Ansichten mit direkt oder indirekt unterstellten Personen. Bereichsgrenzen bleiben wirksam; Nextcloud-Admins sehen alle Ansichten.
- Nutzer*innen planen eigene Urlaube. Genehmigungen und die Bearbeitung genehmigter Urlaube folgen derselben Vorgesetztenhierarchie wie AD Kalender; Nextcloud-Admins dürfen alle verwalten. Direkte Kolleg*innen dürfen nach administrativer Freischaltung innerhalb derselben Fachgruppe genehmigen, bei BO/EB nur im selben Bürobereich. Eigene Genehmigung bleibt außer für Nextcloud-Admins gesperrt.
- Genehmigungen mit überschneidenden Diensten oder Terminen werden mit einer read-only Konfliktliste abgelehnt; es erfolgt keine automatische Löschung.
- Gruppen stammen aus derselben konfigurierbaren `AdOrganizationDefinition` wie AD Kalender und AdPlaner. Fachrollen, Bereiche, Assistenzteam-Präfix und Organisationssichten werden nicht zusätzlich in AD Urlaub festverdrahtet.
- AD Urlaub ist die kanonische schreibende Urlaubsquelle. Die Jahresmatrix fasst dynamische Assistenzteams und die konfigurierten Organisationssichten zusammen.
- Büro Nordost, Büro West und Büro Süd sind eigenständig auswählbare Organisationssichten. Bereichsübergreifende Leitungen erscheinen durch ihre Bereichsmitgliedschaften in jeder passenden Sicht, ohne die Büros zusammenzufassen.
- Assistenzteams verwenden dieselbe Nextcloud-Gruppe wie AdPlaner. Separate Gruppen mit einem Suffix wie `-Urlaub` sind keine unterstützte Datenquelle.
- Eigene Urlaubszeiträume werden kompakt über Von/Bis/Notiz eingetragen. Berechtigte Koordinator*innen wechseln den Tagesstatus direkt in der Jahresmatrix; Konflikte werden inline angezeigt.
- AdPlaner bindet seine Urlaubssicht ausschließlich an diese Quelle an und besitzt keine parallele Urlaubspersistenz.
- Der read-only Cross-App-Vertrag ist `OCA\LocalBase\Calendar\AbsenceQueryEvent` mit `AbsenceInterval`. AD Urlaub greift niemals direkt auf Tabellen anderer Apps zu.
- Der app-eigene Adminabschnitt bietet einen ausschließlich manuell bestätigten Demo-Pack. Er verwendet die gemeinsamen synthetischen Suite-Demokonten, niemals zufällig ausgewählte reale Gruppenmitglieder.
- Fremde oder LDAP-verwaltete Konten werden nicht als Demokonto übernommen; read-only LDAP-Gruppen brechen die Demo-Installation im Preflight vor jeder Mutation ab.
- WordPress-Bestandsdaten werden nicht importiert. Es existiert keine Legacy-Importstrecke.

## Architektur und Sicherheit

- Controller bleiben dünn; Rechte liegen in `VacationAccessService`, Fachlogik in `VacationService`, Datenzugriff im Repository.
- Jeder schreibende API-Pfad prüft serverseitig Zielperson und Besitz/Adminrecht. UI-Ausblendungen sind kein Schutz.
- Auch lesende Team-, Jahres- und Wochenendpunkte liefern nur den durch `VacationVisibilityPolicy` erlaubten Personen- und Ansichtsausschnitt; direkte Requests auf andere Teams bleiben gesperrt.
- Urlaubsnotizen verbleiben in AD Urlaub und werden nicht an konsumierende Apps übertragen.
- App-Root und Tabellenwrapper erfüllen den im lokalen Skill `work-in-nextcloud-app` vollständig beschriebenen Nextcloud-Scrollvertrag.
- Modelle nutzen `get(...)`, `get_all([...])` und `toArray()`.
- Im Frontend bleibt `main.js` ein schlanker Bootstrap. `VacationApp` orchestriert API, Zustand und Ereignisse; `VacationPlan` rendert Teamauswahl, Jahresmatrix, eigene Anträge und Konflikte ohne eigene API-Zugriffe.
- Organisationsweite Gruppen- und Genehmigungsfreigaben liegen bei einer Einzelinstallation im Adminabschnitt von AD Urlaub, ab zwei AD-Produkten im Adminabschnitt der OrgSuite. AD Urlaub besitzt derzeit keine persönlichen Dauer-Einstellungen und deshalb keinen leeren Einstellungstab.

## Gemeinsame Suite-Navigation

- Ohne aktive OrgSuite registriert AD Urlaub einen eigenen Nextcloud-Hauptnavigationseintrag. Ab zwei AD-Produkten ersetzt `orgsuite` diesen durch den gemeinsamen Einstieg `AD`.
- Das Template stellt den optionalen Menühost mit `data-suite="ad"` und `data-current-app="adurlaub"` bereit, lädt aber keine OrgSuite-Assets direkt.
- Ohne AD Kalender bleibt Urlaubsplanung möglich; lediglich die automatische Prüfung gegen Dienste und Termine entfällt. Dieser Standalone-Zustand ist kein Fehler.
- Urlaubs-, Team- und Genehmigungsrechte bleiben ausschliesslich serverseitig im AD Urlaub; Menuesichtbarkeit ist keine Berechtigung.

## Git und Tests

- Eigenständiges Git-Repository. Diese Datei und lokal referenzierte Skills bilden bei einem direkten Start die vollständige Repository-Steuerung.
- Fuer Git-, Sandbox-, DDEV-/`occ`-Sicherheit, Verifikation und Learning Candidates gilt der lokal mitgefuehrte Skill `work-in-nextcloud-app`; die folgenden Urlaubs-Pruefungen ergaenzen ihn.
- Schnelle Tests: `php tests/run.php` und `node tests/run-js.mjs`.
- Authentifizierter DOM-/CSRF-/Überschneidungs-Smoke: `ADU_BASE_URL=... ADU_USER=... ADU_PASSWORD=... tests/http-smoke.sh`.
- Selbstbereinigende DDEV-Rechtematrix: `tests/access-matrix-ddev-smoke.sh`.
- Migration/DI zusätzlich in DDEV prüfen.

## DDEV

Mount: `/var/www/html/html/custom_apps/adurlaub`
