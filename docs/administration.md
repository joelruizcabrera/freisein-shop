# Administration

[[_TOC_]]

## NPM Abhängigkeiten installieren (Initial notwendig)
Bevor das erste Mal mit der Administration gearbeitet werden kann, müssen die npm Abhängigkeiten über 
den Command `bin/hbh-npm-install-administration.sh` installiert werden.

**Achtung**
Aktuell scheint dies nicht mehr auszureichen, eine genauere Fehleranalyse steht noch aus.
Sollte es zu Problemen kommen, bitte stattdessen probieren `bin/build-administration.sh` aufzurufen.

## Build
Damit Änderungen in der Administration sichtbar werden, müssen diese zuvor kompiliert werden.
Um alles neu zu kompilieren, also inkl. aller Plugins und Core-Module, kann der Befehl
`bin/build-administration.sh` verwendet werden.

Möchte man aber nur Änderungen an einem Plugin kompilieren, kann stattdessen besser folgender
Befehl verwendet werden: `bin/hbh-build-administration.sh {pluginName}`.

Es können dabei auch mehrere Plugins, mit einem Leerzeichen getrennt, angegeben werden.

## Watcher
Damit der watcher mit Lando funktioniert, muss nach jedem Neustart des Containers folgendes gemacht werden:

Als `root` in den Container einloggen (`lando ssh --user root`) und folgendes ausführen
`echo "127.0.0.1 shopware.dev.die-etagen.de" >> /etc/hosts`.

Anschließend kann der watcher im Container über `bin/watch-administration.sh` gestartet werden.
Der Befehl muss so lange laufen, wie der watcher verwendet werden soll.

Für den Zugriff auf die Administration mit aktiviertem Watcher muss http://localhost:8080 statt 
http://shopware.dev.die-etagen.de/admin verwendet werden.

> **Änderungen übernehmen**  
> Der watcher erkennt Änderungen automatisch und kompiliert diese. Die Daten werden aber nur temporär im Speicher gehalten.
> Um Änderungen dauerhaft zu speichern, muss noch `bin/build-administration.sh` oder `bin/hbh-build-administration.sh {pluginName}`
> ausgeführt werden.

### Voraussetzungen
Nachfolgende Voraussetzungen sollten in unserem Standard immer automatisch erfüllt sein, der Vollständigkeitshalber
werden diese hier trotzdem aufgeführt:

- In der [.env](../.env) muss `HOST="0.0.0.0"` hinterlegt sein.
- In der [.env](../.env) muss `APP_URL=http://shopware.dev.die-etagen.de` hinterlegt sein (HTTPS funktioniert nicht).
