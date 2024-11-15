# Datenbank Migrations
Die offizielle Dokumentation dazu ist [hier](https://docs.shopware.com/en/shopware-platform-dev-en/how-to/plugin-migrations) und
[hier](https://docs.shopware.com/en/shopware-platform-dev-en/developer-guide/migrations) zu finden.

[[_TOC_]]
## Was ist ein Migration-File?
Einfach gesagt handelt es sich bei Migration-Files um normale PHP-Dateien, die Anweisungen für die Datenbank
enthalten, um z.B. Änderungen vorzunehmen oder eine neue Tabelle anzulegen. Diese Dateien werden z.B. über ein Script, 
welches automatisch bei einem `composer install` ausgeführt wird, auf die Shopware-Instanz angewendet und abgespeichert. 
So ist sichergestellt, dass jedes Migration-File nur einmal pro Instanz angewendet wird.

Jedes Shopware-Plugins kann Migrations mitliefern. Diese müssen im Ordner `src/Migration`
des Plugin-Verzeichnisses abgelegt werden.

**Beispiel:**  
Die Migrations für das Plugin `HbHProjectConfig` müssen in `custom/static-plugins/HbHProjectConfig/src/Migration` abgelegt werden.

## Migration-File erstellen
Ein Migration-File kann entweder von Hand erstellt werden, oder auch über folgenden Befehl:
```console
database:create-migration -p {PluginName} --name {MigrationSuffix}
```

Der `{PluginName}` muss durch den Namen des Plugins, also z.B. `HbHProjectConfig`, ersetzt werden.
Über die optionale Angabe von `--name` kann dem Dateinamen des Migration-File noch ein Suffix mitgegeben werden.
Dies ist sinnvoll, da der Dateiname ansonsten nur aus dem Zeitstempel besteht. 

Gibt es im angegebenen Plugin noch keinen Ordner `src/Migration` wird dieser zudem automatisch angelegt.

In der `update`-Methode des erstellen Migration-Files müssen nun die gewünschten Anweisungen für die Datenbank
hinterlegt werden.

## Alle Migration-Files eines Plugins anwenden
Vor allem während der Entwicklung kann es hilfreich sein, die Anwendung aller Migration-Files eines Plugins anzustoßen.
Dafür kann der folgende Befehl verwendet werden:
```console
database:migrate {PluginName} --all
```

Migrations die bereits importiert wurden, werden dabei automatisch übersprungen, sodass es unproblematisch ist, wenn
dieser Befehl mehrfach ausgeführt wird.
