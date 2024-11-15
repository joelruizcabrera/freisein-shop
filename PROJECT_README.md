# Kunde/Projektbezeichnung (Changeme)
Zusätzlich zu den wichtigsten Projektinformationen auf dieser Seite gibt es im Verzeichnis [docs](docs)
weitere themenspezifische Dokumentationen.

[[_TOC_]]

## Initiale Einrichtung für die Arbeit am Projekt
Um an dem Projekt zu arbeiten, bitte die nachfolgenden Schritte sorgfältig lesen und genau befolgen, um Probleme zu vermeiden.
Als Entwicklungsumgebung nutzen wir [Lando](https://lando.dev/), ist dies noch nicht installiert, den Informationen [hier](docs/lando.md) folgen. 

### 1. Repository klonen  
Das Repository in ein lokales Verzeichnis auf dem PC [klonen](https://docs.gitlab.com/ee/user/project/repository/#clone-a-repository).
Dieses Verzeichnis wird nachfolgend immer `Projektverzeichnis` genannt. 

### 2. Lando Entwicklungsumgebung starten  
Im Projektverzeichnis den Befehl `lando start` ausführen. Der erste Start pro Projekt dauert länger, da hierbei zuerst die 
notwendigen Docker-Container erstellt werden müssen.

### 3. Projektabhängigkeiten über Composer installieren  
Im Projektverzeichnis den Befehl `lando ssh` ausführen, um eine SSH-Verbindung zum Lando Webserver-Container
aufzubauen. Immer wenn etwas in diesem Container ausgeführt werden muss, wird es nachfolgend mit `Im Webserver-Container` beschrieben.

Im Webserver-Container den Befehl `./composer install` ausführen.

Über diesen Weg ist sichergestellt, dass die Installation mit der mitgelieferten composer-Version erfolgt.
Außerdem wird so die PHP-Version des Lando-Containers verwendet und nicht die evtl. lokal installierte, 
was bei Abweichung zu Problemen führen kann.

### 4. Shopware installieren
Im Webserver-Container den nachfolgenden Befehl ausführen:
```console
bin/console hbh:system:install --shop-currency=EUR --shop-locale=de-DE --shop-email="test@example.com" --shop-name="Shopname" --admin-username="admin" --admin-password="shopware"
```

Die Werte für die einzelnen Optionen können hierbei nach Bedarf angepasst werden.

### 5. Ausführung zusätzlicher Scripte triggern
Nachdem der Shop installiert wurde, muss im Webserver-Container nochmal der Befehl `./composer install` ausgeführt werden.
Dies dient hauptsächlich dazu weitere Scripte anzustoßen, die erst ausgeführt werden, wenn der Shop bereits
installiert ist. Dazu zählt z.B. die automatische Installation und Aktivierung der Plugins.

### 6. In der Shopware-Administration einloggen
Die Administration über [/admin](https://shopware.dev.die-etagen.de/admin) öffnen und mit den in Schritt
4 angegebenen Login-Daten einloggen.  
(Standardmäßig: Benutzer: admin | Passwort: shopware ).

### 7. Domain im Verkaufskanal hinterlegen
Damit das Frontend erreichbar ist, in der Administration den gewünschten Verkaufskanal auswählen und bei "Domains" 
die Domain z.B. https://shopware.dev.die-etagen.de/ hinterlegen und Speichern.

## Shopware-Lizenz aktivieren
Wird für das Projekt nicht die Community-Edition verwendet, sondern eine der drei kostenpflichtigen, bitte
[hier](docs/shopware-lizenz.md) nachlesen was für die Aktivierung gemacht werden muss.

## Systemspezifische Konfigurationsanpassungen
Die mitgelieferte [.env](.env) ist Teil des Projekts und darf daher nur allgemeingültige Konfigurationen enthalten.
Für systemspezifische Anpassungen, wie z.B. den Datenbank-Zugang, muss eine `.env.local` angelegt werden.
Diese wird nicht versioniert und kann alle Angaben aus der [.env](.env) überschreiben.

## Projekt aktuell halten
Über ein `git pull` können die Daten des Repository grundsätzlich aktuell gehalten werden. Damit aber auch z.B.
von anderen hinzugefügte Plugins, oder Migrations, installiert/angewendet werden, sollte im 
Webserver-Container regelmäßig `./composer install` ausführt werden.

## Shopware Update durchführen
1. In der [composer.json](composer.json) bei allen `shopware/`-Paketen die gewünschte, neue, Versionsnummer hinterlegen.
2. Im Webserver-Container den Befehl `./composer update` ausführen.
3. Änderungen commiten und pushen

## Verwendungszweck des `HbHProjectConfig`-Plugins
Dieses Plugin ist Teil unseres Standards und ist daher automatisch bei jedem Projekt enthalten.
Hierüber werden Basis-Funktionalitäten bereitgestellt, die wir bei jedem neuen Projekt haben wollen.

Zudem kann es dafür genutzt werden, projektspezifische Anpassungen zu machen, die zu klein für ein
eigenes Plugin sind. In der [Defaults.php](custom/static-plugins/HbHProjectConfig/src/Defaults.php)
können zudem z.B. projektspezifische Konstanten definiert werden, dass ist z.B. für die UUIDs von Verkaufskanälen
sinnvoll. 

Durch diese projektspezifischen Anpassungen ist es normal, dass dieses Plugin nach Projektstart von dem im Standard-Repository abweicht.

## Umgang mit der Datenbank
Jeder Entwickler, bzw. jede Installation des Projekts, nutzt eine eigene lokale Datenbank.
Diese wird initial durch die Shop-Installation angelegt.

Gibt es ein Problem in einer Instanz, welches lokal z.B. mangels Daten nicht reproduzierbar ist,
kann auch ein Datenbank-Dump aus dieser Instanz lokal eingespielt werden.

Gibt es DB-Anpassungen die in jeder Installation des Projekts benötigt werden, muss dafür ein Migration-File 
in einem Plugin erstellt werden. Diese werden automatisch importiert und sorgen so dafür, dass alle Instanzen
gleich sind.

**Datenbank-Dumps selbst gehören aber niemals ins Repository!**

## Deployment
Siehe [hier](docs/deployment.md)

### Server/Instanz spezifische Konfigurationen
In `config/packages` gibt es die Unterordner `dev`, `prod` und `test` um ENV abhängige Konfigurationen vorzunehmen.
Da aber sowohl unser Stage- als auch Production-System auf `prod` läuft, ist diese Unterscheidung nicht ausreichend.
Symfony bietet zwar die Möglichkeit [weitere ENV-Umgebungen zu erstellen](https://symfony.com/doc/current/configuration.html#creating-a-new-environment),
das macht bei Shopware aber Probleme da an einigen stellen fixe Abfragen auf z.B. `dev` oder `prod` gemacht werden
um z.B. zu entscheiden ob bestimmte Dienste geladen, oder Einstellungen gesetzt werden sollen.

Aus diesem Grund gibt es den Ordner `config/packages/instance-specific`. Dieser wird beim deployment als Symlink
angelegt und verweist auf `shared/config/packages/instance-specific`. In der [services.yaml](config/services.yaml)
wird dafür gesorgt, dass auch alle `*.yaml`-Dateien aus diesem Ordner geladen werden.

Möchte man also z.B. Varnish für das Production-System konfiugurieren, kann die entsprechende Konfiguration
dafür auf dem Prod-Server im Ordner `shared/config/packages/instance-specific` abgelegt werden.

Der Inhalt vom Ordner `instance-specific` wird versioniert, es ist also möglich bei einem Projekt vorgefertigte 
Konfigurationstemplates mitzuliefern. Damit diese nicht automatisch geladen werden sollten, sollten diese auf
`.dist` enden. Also statt z.B. `storefront.yaml` sollte die Datei als `storefront.yaml.dist` gespeichert werden.
Wenn diese später auf dem Zielserver geladen werden soll, muss diese im `shared/config/packages/instance-specific` einfach
wieder in `storefront.yaml` umbenannt werden. Die Inhalte in diesem Ordner werden aber nur beim initial deployment
übertragen, alles was danach ergänzt wird, muss manuell hochgeladen werden. 

## Erste Schritte beim Auftreten eines Problems
Als allererstes sicherstellen, dass alle Schritte wie dokumentiert durchgeführt wurden und wenn nicht
diese wiederholen.

Außerdem gibt es [hier](docs/haeufige-fehlerursachen.md) eine Übersicht über häufig auftretende Fehler und was in diesem Fall zu tun ist.
