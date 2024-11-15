# Cronjobs

Nachfolgend eine Übersicht aller Cronjobs die eingerichtet werden müssen.

**Achtung**
Mit Shopware 6.6 gab es einige Änderungen an der Queue, die sich auch auf die
Cronjobs auswirken könnte. Es war noch keine Zeit dies genauer zu prüfen, daher
sind nachfolgende Informationen nicht final.

# Geplante Aufgaben abarbeiten
Zum abarbeiten der Scheduled Tasks muss folgender Befehl über einen Cronjob ausgeführt werden:

`bin/console scheduled-task:run --time-limit=60`

Dieser prüft, ob es geplante Aufgaben (DB-Tabelle: `scheduled_task`) gibt, die abgearbeitet werden müssen 
und fügt entsprechende Messages zur Tabelle `messenger_messages` hinzu.

Sofern nicht anders abgesprochen sollte der Cronjob alle 5 Minuten ausgeführt werden.

Mehr Informationen [hier](https://developer.shopware.com/docs/guides/hosting/infrastructure/scheduled-task.html#running-scheduled-tasks)

# Messages abarbeiten
Zum abarbeiten der Messages muss folgender Befehl über einen Cronjob ausgeführt werden:

`bin/console messenger:consume async --time-limit=60 --memory-limit=128M`

Mehr Informationen [hier](https://developer.shopware.com/docs/guides/hosting/infrastructure/message-queue.html#cli-worker)
