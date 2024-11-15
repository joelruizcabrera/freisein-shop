# Demo Daten

[[_TOC_]]

## Offizielles Plugin
Das offizielle Demo-Daten-Plugin von Shopware darf bzw. kann nicht installiert werden,
da es nicht mit unserem Setup kompatibel ist. Das liegt daran, dass dort auf die fixe
UUID des Headless-Channel zugegriffen wird, der bei unserer Installation nicht (mehr) existiert.

Das ist zumindest der aktuellste Stand, der aber schon einige Monate alt ist.
Solange nicht geprüft wurde, ob sich das mittlerweile geändert hat, gilt diese Info weiterhin.

## Alternativen
### Unser eigenes Demo-Daten Plugin
Das Repository für unser eigenes Demo-Daten Plugin ist [hier](https://git.die-etagen.de/etagen/standards/shopware/sw6-plugins/hbhdemodata).
Dies hat gegenüber dem offiziellen Plugin zudem mehr Konfigurationsmöglichkeiten, die der [Readme](https://git.die-etagen.de/etagen/standards/shopware/sw6-plugins/hbhdemodata/-/blob/master/README.md) entnommen
werden können.

> **Kompatibilität mit Shopware 6.5**  
> Es wurde bisher nicht geprüft, ob das Plugin Kompatible mit Shopware 6.5 ist. Sobald dies
> sichergestellt ist, wird dieser Hinweis wieder entfernt.
