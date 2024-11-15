# Storefront

[[_TOC_]]

## NPM Abhängigkeiten installieren (Initial notwendig)
Bevor das erste Mal mit der Storefront gearbeitet werden kann, müssen die npm Abhängigkeiten über
den Command `bin/hbh-npm-install-storefront.sh` installiert werden.

**Achtung**
Aktuell scheint dies nicht mehr auszureichen, eine genauere Fehleranalyse steht noch aus.
Sollte es zu Problemen kommen, bitte stattdessen probieren `build-storefront.sh` aufzurufen.

## Build
Damit CSS/JS-Änderungen in der Storefront sichtbar werden, müssen diese zuvor kompiliert werden.
Um alles neu zu kompilieren, also inkl. aller Plugins und Core-Module, kann der Befehl
`bin/build-storefront.sh` verwendet werden.

Möchte man aber nur Änderungen an einem Plugin kompilieren, kann stattdessen besser folgender
Befehl verwendet werden: `bin/hbh-build-storefront.sh {pluginName}`.

Es können dabei auch mehrere Plugins, mit einem Leerzeichen getrennt, angegeben werden.

## Theme eines Verkaufskanals kompilieren
Der Befehl `theme:compile` kompiliert die Storefront aller Verkaufskanäle. Möchte man stattdessen nur
einen Verkaufskanal kompilieren, kann der Befehl `hbh:theme:compile {salesChannelId}` verwendet werden.

Der Aufruf ohne Parameter `{salesChannelId}` sorgt dafür, dass man einen Verkaufskanal aus einer Liste
auswählen kann.

## Watcher
Damit der watcher mit Lando funktioniert, muss nach jedem Neustart des Containers folgendes gemacht werden:

Als `root` in den Container einloggen (`lando ssh --user root`) und folgendes ausführen
`echo "127.0.0.1 shopware.dev.die-etagen.de" >> /etc/hosts`.

Anschließend kann der watcher im Container über `bin/watch-storefront.sh` gestartet werden.
Der Befehl muss so lange laufen, wie der watcher verwendet werden soll.

Für den Zugriff auf die Storefront mit aktiviertem Watcher muss http://shopware.dev.die-etagen.de:9998 statt
http://shopware.dev.die-etagen.de verwendet werden. Es wird dabei immer der Verkaufskanal angezeigt, der mit
der in `APP_URL` definierten Domain verknüpft ist, also `http://shopware.dev.die-etagen.de.

> **Änderungen übernehmen**  
> Der watcher erkennt Änderungen automatisch und kompiliert diese. Die Daten werden aber nur temporär im Speicher gehalten.
> Um Änderungen dauerhaft zu speichern, muss noch `bin/build-storefront.sh` oder `bin/hbh-build-storefront.sh {pluginName}`
> ausgeführt werden.

### Voraussetzungen
Nachfolgende Voraussetzungen sollten in unserem Standard immer automatisch erfüllt sein, der Vollständigkeitshalber
werden diese hier trotzdem aufgeführt:

- In der [.env](../.env) muss `HOST="0.0.0.0"` hinterlegt sein.
- In der [.env](../.env) muss `APP_URL=http://shopware.dev.die-etagen.de` hinterlegt sein (HTTPS funktioniert nicht).
