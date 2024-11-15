# Plugin Installation

[[_TOC_]]

## Grundlagen
> **Keine Installation über die Administration**  
> Die Verwaltung der Shop-Plugins läuft ausschließlich über Composer. Plugins dürfen nicht über
> die Shopware-Administration installiert werden, da es ansonsten zu Problemen kommen kann.
> Es sollten niemals Plugins im Verzeichnis `custom/plugins` vorhanden sein, da diese nur in der
> lokalen Instanz verfügbar sind.

Jedes Shop-Plugin welches in der [composer.json](../composer.json) hinterlegt ist, wird automatisch als für das
Projekt benötigt angesehen und automatisch installiert und aktiviert.

Wird ein Plugin temporär nicht mehr benötigt, kann dieses in der jeweiligen Instanz über die Administration
deaktiviert werden. Ein manuell deaktiviertes Plugin wird in der Instanz nicht automatisch erneut aktiviert.

## Angabe der Plugin-Version in der composer.json (Version constraint)
Um die versehentliche Aktualisierung von composer Abhängigkeiten zu vermeiden, sollten immer fixe Versionsnummern
angegeben werden. Also statt z.B. `"shopware/core": "~v6.5.0"` lieber so `"shopware/core": "6.5.2.1"`.

Das hat den Vorteil, dass ein Versionsupdate immer ganz bewusst durchgeführt werden muss.

Einzige Ausnahme sind lokale, projektspezifische Plugins. Da diese Teil des Repositories sind, kann hier immer
`*` angegeben werden.

Während der Entwicklung kann es zudem nützlich sein, bei eigenen externen Plugins einen [Branch statt einer
Versionsnummer](https://getcomposer.org/doc/articles/versions.md#branches) anzugeben. So können Änderungen im Plugin direkt bezogen werden, ohne immer eine neue Versionsnummer
erstellen zu müssen. Dafür kann das spezielle Prefix `dev-{branchName}` verwendet werden. Möchte man also z.B. den `develop`-Branch
nutzen, müsste folgendes angegeben werden: `dev-develop`.

Spätestens zum Livegang muss aber immer eine Versionsnummer angegeben werden.

## Projektspezifische Plugins
Alle Plugins die projektspezifisch sind, also nicht in anderen Projekten wiederverwendet werden sollen, 
müssen in `custom/static-plugins` erstellt werden. Alle Plugins in diesem Ordner sind Teil vom Repository, 
werden also versioniert.

### Plugin erstellen
Die einfachste Möglichkeit um ein projektspezifisches Plugin zu erstellen, ist die Nutzung des Befehls
`hbh:plugins:create`. Nach dem Aufruf müssen zuerst ein paar Fragen zum Plugin beantwortet werden, bevor es
im Verzeichnis `custom/static-plugins/{pluginName}` erstellt wird.

### Plugin installieren
Um ein projektspezifisches Plugin aus dem Ordner `custom/static-plugins/{pluginName}` zu installieren, 
muss folgender Befehl im Webserver-Container ausgeführt werden:

```console
./composer require "{composerPluginPackageName}:*"
```

Der `{composerPluginPackageName}` muss durch den Wert ersetzt werden, der als `name` in der `composer.json` 
des Plugins (`custom/static-plugins/{pluginName}/composer.json`) angegeben ist.

Als Version kann bei diesem Befehl immer `*` angegeben werden, da es sich ohnehin um ein lokales Plugin handelt
und somit nur eine Version vorliegt.

### Plugin aktualisieren
Auch wenn es für lokale Plugins nicht zwingend erforderlich ist, kann es Sinn machen, bei diesen eine Version 
zu pflegen. Zumindest bei größeren Anpassungen sollte die Versionsnummer in der `custom/static-plugins/{pluginName}/composer.json`
aktualisiert und anschließend im Webserver-Container `./composer update {composerPluginPackageName}` ausgeführt werden.

## Externe Plugins (Nicht aus dem Shopware-Store)
### Aus unserem Gitlab
#### Zugriffsberechtigung (Bereits erledigt)
Die nachfolgenden Schritte sind bereits Teil von unserem Standard-Template und damit
automatisch in jedem Shop-Projekt Repository umgesetzt. 

Der Zugriff auf die privaten Repositories unserer Gitlab-Instanz erfolgt über den 
[personal access token](https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html)
des Gitlab-Benutzers `Shopware6`.

Über den Token hat man Zugriff auf alle Repositories, für die der`Shopware6`-Benutzer in Gitlab
freigegeben ist. Das sind z.B. alle Plugins die im Gitlab-Verzeichnis: `etagen/standards/shopware/sw6-plugins` liegen.

Damit der Zugriff über composer funktioniert, ist der Token in der [auth.json](../auth.json) hinterlegt.
Um composer unsere Gitlab-Instanz bekannt zu machen, ist diese zudem in der [composer.json](../composer.json) hinterlegt.

```json
{
   "config": {
      "gitlab-domains": [
         "git.die-etagen.de"
      ]
   }
}
```
#### Plugin installieren
Als Erstes muss das Plugin-Repository in der [composer.json](../composer.json) unter `repositories` 
hinzugefügt werden. Als `type` muss dabei `vcs` angegeben werden und als `url` der Link zum Repository
mit der Endpunkt `.git` (In Gitlab die Adresse bei `Clone with HTTPS`).

Für die Installation anschließend im Webserver-Container folgenden Befehl ausführen:
```console
./composer require "{composerPluginPackageName}:{versionConstraint}"
```

> **Hinweise**  
> Unsere eigenen Repositories sollten in `repositories` immer vor allen anderen stehen, damit dort zuerst nach
> den packages gesucht wird. Mehr dazu [hier](https://getcomposer.org/doc/articles/repository-priorities.md).
> Für die `url` muss zudem immer die HTTP- und nicht die SSH-Variante genutzt werden, da ansonsten die
> Authentifizierung über den Token nicht funktioniert. Die URL muss also mit `https` beginnen und nicht
> mit `git@`.

**Beispiel:**  
Plugin [HbHSyncHelper](https://git.die-etagen.de/etagen/standards/shopware/sw6-plugins/hbhsynchelper).
Repository in der [composer.json](../composer.json) hinzufügen:

```json
{
   "repositories": [
      {
         "type": "vcs",
         "url": "https://git.die-etagen.de/etagen/standards/shopware/sw6-plugins/hbhsynchelper.git"
      }
   ]
}
```
Plugin installieren:
```console
./composer require "hbh/sync-helper:1.0.0"
```

> **GitLab Package Registry**  
> Das Hinzufügen des individuellen Repositories könnte zukünftig entfallen, wenn wir für unsere Gitlab-Instanz
> stattdessen eine eigene [GitLab Package Registry](https://docs.gitlab.com/ee/user/packages/)
> nutzen. Sobald dies eingerichtet ist, wird dieser Hinweis sowie der dann überflüssige Schritt, entfernt.

### Aus einem öffentlichen Repository
Hierbei geht es um öffentliche Plugins aus bekannten Registries wie z.B. `github.com`, auf die `composer` ohne
zusätzliche Konfiguration zugreifen kann.

Für die Installation muss im Webserver-Container nur folgender Befehl ausgeführt werden:
```console
./composer require "{composerPluginPackageName}:{versionConstraint}"
```

## Shopware-Store Plugins
Ähnlich zu dem Zugriff auf Plugins aus unserer eigenen Gitlab-Instanz, müssen beim Zugriff auf Plugins aus dem
Shopware-Shop zusätzliche Anpassungen gemacht werden, damit diese über composer eingebunden werden können.

Nachfolgendes gilt auch für kostenlose Plugins aus dem Store.

### Während der (initialen) Entwicklung
In dieser Phase müssen noch keine Plugins gekauft werden, da wir unsere `*.dev.hob-by-horse.de` Wildcard-Umgebung
dafür nutzen können. Dafür müssen nachfolgende Schritte befolgt werden:

#### Wildcard-Umgebung einrichten
1. [Hier](https://account.shopware.com/) mit unserem Shopware-Account einloggen.
2. Auf `Partner` => `Wildcard-Umgebungen` => `Wildcard-Instanz erstellen` klicken.
3. Den gewünschten `Projektname` eingeben und `dev.hob-by-horse.de` auswählen. Die Stage-Domain ergibt sich dann aus `{Projektname}.dev.hob-by-horse.de`.

#### Plugins zur Wildcard hinzufügen
Plugins, die in der Wildcard Umgebung verwendet werden sollen, müssen nicht direkt über den Store gekauft
werden. Stattdessen werden diese direkt der Wildcard hinzugefügt.

1. [Hier](https://account.shopware.com/) mit unserem Shopware-Account einloggen.
2. Auf `Partner` => `Wildcard-Umgebungen` klicken.
3. Die gewünschte, vorher erstellte, Wildcard-Umgebung auswählen.
4. Auf "Lizenz hinzufügen" klicken und das gewünschte Plugin auswählen und anschließend speichern.
5. Beim Plugin auf die `[...]` klicken und `Via composer installieren` auswählen.
6. Den angezeigten Anweisungen folgen, einige Schritte müssen dabei nur beim ersten Mal durchgeführt werden, damit composer auf die Store-Plugins zugreifen kann. 

### Livegang
Spätestens zum Live-Gang müssen alle Shopware Store-Plugins für die Live-Domain gekauft werden.
Anschließend muss der `AuthToken` der Wildcard-Domain für `packages.shopware.com` in der [auth.json](../auth.json) durch
den der Live-Domain ausgetauscht werden.

Dieser kann anschließend sowohl für das Stage- als auch das Production-System verwendet werden.

## Plugin aktualisieren
Um ein externes Plugin, egal aus welcher Quelle, zu aktualisieren, einfach die Versionsnummer in der [composer.json](../composer.json) anpassen
und anschließend im Webserver-Container `./composer update` ausführen.

Oder stattdessen direkt folgenden Befehl im Webserver-Container ausführen: `./composer update "{composerPluginPackageName}:{newVersion}"`
