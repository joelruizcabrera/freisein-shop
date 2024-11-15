# Shopware-Lizenz
Nachfolgendes ist relevant, wenn der Kunde im Projekt nicht die Community-Edition verwendet,
sondern einen der drei Pläne "Rise", "Evolve" oder "Beyond".

Um dessen Features nutzen zu können, muss die entsprechende Lizenz in der Shopware-Instanz aktiviert werden.

[[_TOC_]]

## Lizenz aktivieren
### Lokal / Stage
Im Shopware-Account muss bei der Projekt-Wildcard das Plugin `Shopware Commercial` hinzugefügt werden
und dieses anschließend über composer installiert werden: `composer require store.shopware.com/swagcommercial`. 

Im Shopware-Backend unter `Einstellungen => System => Shopware Account` bei `Lizenzdomain` die Wildcard-Domain hinterlegen. Anschließend unter `Erweiterungen => Shopware Account` mit unserem Partneraccount (`info@die-etagen.de` Zugang in Keeper) einlogen.

### Production
Im Shopware-Account muss für das Projekt (Live-Domain) das Plugin `Shopware Commercial` hinzugefügt werden.
Im Shopware-Backend unter `Einstellungen => System => Shopware Account` die Live-Domain hinterlegen
und dort mit dem Shopware-Account des Kunden einloggen.

## Lizenz Status prüfen
Über den Befehl `commercial:license:info` kann der Status der Lizenz geprüft werden, hier sieht man auch wann diese abläuft. 

## Abgelaufene Lizenz erneuern
Wenn der Task `swag.commercial.update_license` nicht vor Ablauf der Lizenz ausgeführt wird, läuft diese ab. Ist die Lizenz abgelaufen, kann der Task entweder über das Backend / Command ausgeführt werden, oder man nutzt direkt den Command `commercial:license:update`.

## Features verwalten
Über den Befehl `commercial:feature:list` bekommt man eine Übersicht aller verfügbaren Features und dessen aktueller Zustand. 
Standardmäßig sind alle Features des jeweiligen Plans automatisch aktiv. Bei Verwendung der Wildcard wird automatisch
das größte Paket (Beyond) verwendet, unabhängig davon welches Paket der Kunde tatsächlich besitzt.

Das ist problematisch, da es so z.B. lokal oder im Stage-System passieren kann, das Funktionen verwendet werden, die
später in Production gar nicht verfügbar sind.

Über das Command `commercial:feature:disable {Feature}` können jedoch einzelne Features deaktiviert werden.
Es wird empfohlen, alle Features die nicht im Paket des Kunden verfügbar sind, über ein Migration-File zu 
deaktivieren, damit diese gar nicht erst versehentlich benutzt werden können.

Dafür muss in der Tabelle `system_config` ein Eintrag mit dem `configuration_key` = `core.store.disabledFeatures`
angelegt werden, welches in `configuration_value` eine kommagetrennte Liste aller Features enthält, die gesperrt werden sollen.
Das sieht dann z.B. wie folgt aus: `{"_value": ["MULTI_INVENTORY", "RETURNS_MANAGEMENT"]}`.

### Features über eigene Plugins
Das `Shopware Commercial`-Plugin liefert bereits viele der Funktionen direkt mit, es gibt aber einige Features wie z.B.
[Dynamic Access](https://docs.shopware.com/en/shopware-6-en/extensions/dynamiccontent?category=shopware-6-en/extensions)
und [Social Shopping](https://docs.shopware.com/en/shopware-6-en/extensions/social-shopping?category=shopware-6-en/extensions)
die weiterhin über ein eigenständiges Plugin bereitgestellt werden.

Diese Plugins sollten entsprechend nur installiert werden, wenn das Feature im Paket des Kunden vorhanden ist und
genutzt werden soll.