# AD Urlaub

Urlaubsplanung für Assistenzteams und organisatorische AD-Fachgruppen. Geplante Urlaube werden als Hinweis, genehmigte Urlaube als blockierende Abwesenheit an andere Apps geliefert.

## Staging-Kompatibilität

- Nextcloud 34
- PHP 8.3 oder neuer innerhalb des von Nextcloud 34 unterstützten Bereichs
- Abhängigkeiten: `localbase`, `orgsuite`
- App-ID und Installationsordner: `adurlaub`

## Installation

```bash
sudo -u www-data php occ app:enable localbase
sudo -u www-data php occ app:enable orgsuite
sudo -u www-data php occ app:enable adurlaub
```

Der Befehl `adurlaub:demo:seed` erzeugt synthetische Testdaten und wird nicht automatisch ausgeführt.
