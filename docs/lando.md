# Lando

[[_TOC_]]

## Was ist Lando
[Lando](https://lando.dev/) ist eine Art Wrapper für `Docker`, mit dessen Hilfe schnell lokale Entwicklungsumgebungen (Docker-Container)
erstellt werden können. Hierbei stellt Lando eine Abstraktionsschicht bereit, um die Einrichtung zu vereinfachen, 
ohne das spezifische Docker Kenntnisse notwendig sind. Mehr Informationen dazu in der [offiziellen Dokumentation](https://docs.lando.dev/).

## Lando installieren
Für die Installation bitte der [Anleitung](https://docs.lando.dev/getting-started/installation.html) folgen.

## Projekt-Konfiguration lokal überschreiben 
Müssen lokal Konfigurationen der [.lando.yml](../.lando.yml) überschrieben werden, kann dafür eine
[.lando.local.yml](../.lando.local.yml) angelegt werden. Diese ist speziell für lokale Änderungen gedacht
und wird nicht versioniert. 

## Hinweise zu unserem Standard Lando-Setting
### Container-Name
Der "name" in der [.lando.yml](../.lando.yml) wird als Container-Prefix verwendet und muss daher
für alle Projekte eindeutig sein. Es ist nicht möglich auf einem PC zwei Projekte mit dem gleichen
Namen und eigenen Containern zu verwenden.

Das sollte normalerweise automatisch gegeben sein, da hier die Projektbezeichnung hinterlegt werden soll.

**Beispiel:**  
In Projekt-A ist der Name "dummy" hinterlegt und es wird `lando start` ausgeführt. Hierbei werden
alle benötigten Container mit dem Prefix `dummy` angelegt. 

Wäre als Name in Projekt-B nun ebenfalls "dummy" hinterlegt, würde ein `lando start` keine neuen
Container für Projekt-B erstellen, sondern einfach die zuvor für Projekt-A erstellten starten.

### Datenbank-Zugang
Benutzer: `homestead`  
Passwort: `secret`  
Datenbank: `lando_sw`  
Host: `database`  

Es darf nicht `localhost` als Host angegeben werden, da die Datenbank in einem anderen Container mit der Bezeichnung
`database` liegt.

### Domain
Standardmäßig wird die Domain `shopware.dev.die-etagen.de` verwendet. Damit dies funktioniert, muss in der
lokalen `hosts`-Datei folgender Eintrag hinterlegt werden:

```
127.0.0.1 shopware.dev.die-etagen.de
```

#### SSL-Zertifikat vertrauen
Für die Domain wird automatisch ein SSL-Zertifikat ausgestellt, dem der Browser/Betriebssystem standardmäßig aber nicht vertraut.
Um das Problem zu lösen, die [hier](https://docs.lando.dev/core/v3/security.html#trusting-the-ca) beschriebenen Maßnahmen durchführen.
