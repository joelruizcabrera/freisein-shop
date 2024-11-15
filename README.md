# Etagen Shopware 6 - Production Template

[[_TOC_]]

## Informationen zu diesem Repository
Dieses Repository/Template dient als Grundlage für alle unsere Shopware 6 Projekte, um eine einheitliche
Basis haben. 

Commits in dieses Repository müssen immer allgemeingültige Anpassungen für unseren Standard sein
und niemals projektspezifisch.
 
Nachfolgend werden alle relevanten Schritte ausführlich dokumentiert. Bitte sorgfältig lesen und immer
genau an diese Vorgehensweise halten, um Probleme zu vermeiden.

## Vorgehensweise bei einem neuen Shopware 6 Projekt
### 1. Projekt-Repository anlegen
Eigenes Repository für das (Kunden-)Projekt anlegen.

### 2. Production-Template kopieren
Alle Daten aus dem Master-Branch dieses Repositories in das Neue, in Schritt 1 erstellte, Repository 
kopieren. Hierbei ist es wichtig, dass wirklich nur die Daten kopiert und nicht das ganze Repository geklont wird.

Am einfachsten ist es daher, wenn im Root-Verzeichnis des Projekt-Repositories, also dort wo auch der `.git`-Ordner
liegt, folgender Befehl ausgeführt wird:

```console
git archive --remote git@git.die-etagen.de:etagen/standards/shopware/sw6-production-template.git --format tar master | tar xf -
```

Hierbei werden automatisch alle Dateien des angegebenen branches (`master`) des Repositories `sw6-production-template.git`
in das aktuelle Verzeichnis **kopiert**.

### 3. Readme-Datei anpassen  
Die mitgelieferte [README.md](README.md) löschen und die [PROJECT_README.md](PROJECT_README.md) in `README.md` umbenennen.

Dieser Schritt ist notwendig, da die `README.md` dieses Repositories Informationen zu unserem Standard-Template enthält
und wie die Vorgehenswiese bei neuen Projekten ist. Diese Informationen sind im Projekt-Repositoriy irrelevant.

Stattdessen sollte die `README.md` im Projekt-Repository nur relevante Informationen zu dem Projekt selbst, bzw.
der Arbeit an diesem, enthalten.

Als Basis kann daher die `PROJECT_README.md` verwendet werden. Der Inhalt kann aber nach Belieben angepasst werden,
wie es für das jeweilige Projekt am besten passt. 

### 4. Verkaufskanäle anpassen
Standardmäßig wird bei der Shopware-Installation mindestens ein Headless-Verkaufskanal angelegt. Dieser wird von uns
aber häufig nicht benötigt. Zudem ist es wichtig das in jeder Installation des Projekts die gleichen Verkaufskanäle
mit den identischen UUIDs vorhanden sind.

Aus diesem Grund wird bei unserer Vorgehensweise kein Sales-Channel automatisch bei der Installation angelegt.
Stattdessen werden diese nachträglich über `Migrations` des `HbH\ProjectConfig`-Plugins hinzugefügt.

Da es initial immer mindestens ein Verkaufskanal gibt, muss die initiale [Migration-Datei](custom/static-plugins/HbHProjectConfig/src/Migration/Migration1647268900FirstSalesChannel.php)
den Projektanforderungen angepasst werden. 

### 5. Lando konfigurieren**
Die mitgelieferte [.lando.yml](.lando.yml) muss nach den Anforderungen des Projekts angepasst werden, also
z.B. Folgende Dinge:

- Der `name` muss für jede Lando-Instanz eindeutig sein. Es bietet sich daher an, dort den Projektnamen zu hinterlegen.
- Angabe der PHP-Version. Diese sollte der Version entsprechen, die später auch auf dem Zielserver genutzt wird.
- Installation zusätzlicher php extensions

Anschließend im Projektverzeichnis den Befehl `lando start` ausführen. Der erste Start pro Projekt dauert länger, 
da hierbei zuerst die notwendigen Docker-Container erstellt werden müssen.

Danach im Projektverzeichnis den Befehl `lando ssh` ausführen, um eine SSH-Verbindung zum Lando Webserver-Container
aufzubauen. Immer wenn etwas in diesem Container ausgeführt werden muss, wird es nachfolgend mit `Im Webserver-Container` beschrieben.

> **Regeln für den Namen**  
> Um Probleme mit dem Namen zu vermeiden, sollten folgende Regeln beachtet werden:
>  - Alles klein
>  - Nur Buchstaben und Zahlen
>  - Keine Sonderzeichen/Leerzeichen/Umlaute
>  - Als Wort-Trennzeichen `-` verwenden

### 6. Composer install
Für die Installation der composer Abhängigkeiten im Webserver-Container den Befehl `./composer install` ausführen.

### 7. Anpassung der `.env`-Konfiguration
Die standardmäßig mitgelieferte [.env](.env) enthält bereits eine Basis-Konfiguration für die Nutzung in unserer
Lando-Umgebung. Es gibt aber ein paar projektspezifischen Angaben die angepasst werden müssen.

- `APP_SECRET` einkommentieren und den benötigten Wert über `lando console system:generate-app-secret` generieren lassen.
- `INSTANCE_ID` einkommentieren und den benötigten Wert über `lando console system:generate-app-secret` generieren lassen.
- `ERROR_MAIL_RECIPIENTS` muss entweder einkommentiert, oder der Mailversand im Fehlerfall deaktiviert werden. Mehr Infos dazu [hier](docs/logging.md#e-mail-benachrichtigung-im-fehlerfall)

Nach beliebigen können auch weitere projektspezifische Anpassungen in dieser Datei vorgenommen werden.
Da diese versioniert wird, dürfen aber nur allgemeingültige Angaben für das Projekt hinterlegt werden.
Für systemspezifische Anpassungen, wie z.B. den Datenbank-Zugang, muss eine `.env.local` angelegt werden.
Diese wird nicht versioniert und kann alle Angaben aus der [.env](.env) überschreiben.

### 8. Initialer commit
Das initiale Setup für das neue Projekt ist abgeschlossen, daher sollte nun ein initialer Commit mit allen Dateien
erstellt werden.

### 9. Vorbereitung zur Nutzung von Store-Plugins (Optional)
Dieser Schritt ist nur notwendig, wenn im Projekt Plugins aus dem Shopware-Store verwendet werden sollen.
Da dies ein größeres Thema ist, gibt es [hier](docs/plugin-lizenzen.md) eine eigene Seite dazu. 

### 10. Shopware installieren
Ab hier sind die initialen Schritte, die einmalig pro Projekt durchgeführt werden müssen, abgeschlossen.
Es kann nun mit den Informationen aus der [PROJECT_README](PROJECT_README.md) weitergemacht werden, 
wobei direkt mit Schritt "4. Shopware installieren" gestartet werden kann.

## Systemvoraussetzungen
Die Systemvoraussetzungen, die mindestens erfüllt sein müssen, können [hier](https://docs.shopware.com/en/shopware-6-en/first-steps/system-requirements) eingesehen werden.
Bitte immer darauf achten, dass dort die korrekte Shopware-Version ausgewählt ist.

Sollte es darüber hinaus projektspezifische Anforderungen geben, müssen diese in der `README.md` des Projekt-Repositories
angegeben werden. 
