# AD Urlaub

Urlaubsplanung für Assistenzteams und organisatorische AD-Fachgruppen. Geplante Urlaube werden als Hinweis, genehmigte Urlaube als blockierende Abwesenheit an andere Apps geliefert.

## Staging-Kompatibilität

- Nextcloud 34
- PHP 8.3 oder neuer innerhalb des von Nextcloud 34 unterstützten Bereichs
- Laufzeitbasis: `localbase`; `orgsuite` ist ab zwei AD-Fachprodukten optional aktiv
- App-ID und Installationsordner: `adurlaub`

## Installation

Für Staging und Auslieferung das Produktbundle `ad-product-adurlaub-<release>.tar.gz` und dessen enthaltenes `install.sh` verwenden. Es prüft und installiert LocalBase automatisch; ab dem zweiten AD-Fachprodukt aktiviert es OrgSuite.

AD Urlaub funktioniert einzeln. Ohne AD Kalender bleibt die Urlaubsplanung vollständig nutzbar; die automatische Prüfung genehmigter Urlaube gegen Dienste und Termine entfällt und wird sichtbar erklärt.

Der Befehl `adurlaub:demo:seed` erzeugt synthetische Testdaten und wird nicht automatisch ausgeführt.

## Roadmap

Geplante Erweiterungen und offene Produktentscheidungen stehen in der [Roadmap](ROADMAP.md).

Installations-, Betriebs- und Abnahmeunterlagen stehen im öffentlichen [AD-Suite-Projekt](https://github.com/Filzmann/ad-suite).
