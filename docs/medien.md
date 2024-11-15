# Medien

## Bilder löschen, die nicht mehr verwendet werden
Über den Command `media:delete-unused` können alle Medien gelöscht werden, die in
der Tabelle `media` vorhanden sind, aber von keiner Entity mehr verwendet werden.

Standardmäßig werden hierbei nur Bilder berücksichtigt, die älter als 20-Tage sind,
dies kann über die Option `--grace-period-days={tage}` angepasst werden. 

Möchte man also alle Bilder berücksichtigen, ohne Rücksicht auf das Alter zu nehmen,
muss der Aufruf so aussehen: `media:delete-unused --grace-period-days=0`.

Die Bilder werden unmittelbar aus der Tabelle `media` gelöscht, bleiben aber vorerst
im Dateisystem erhalten. Zusätzlich wird eine Message vom Typ:
`Shopware\\Core\\Content\\Media\\Message\\DeleteFileMessage` in `messenger_messages` angelegt.
Erst wenn diese abgearbeitet wurde, sind die Bilder auch vom Dateisystem gelöscht.

### Vorschau
Möchte man sich nur eine Liste der betroffenen Dateien ansehen, ohne diese zu löschen, 
hat man zwei Möglichkeiten:

Direkte Ausgabe in Form einer Tabelle:
`bin/console media:delete-unused --dry-run`

Ausgabe in Textform, z.B. für Nutzung als CSV:
`bin/console media:delete-unused --report > unused_media.csv`

Mehr Informationen dazu können dem `Shopware\Core\Content\Media\Commands\DeleteNotUsedMediaCommand`
selbst entnommen werden.

## Bilder vom Dateisystem löschen die in Shopware nicht zugewiesen sind
Über den Command `hbh:media:delete-orphaned-files` können alle Files aus `public/media` (rekursiv) gelöscht
werden, die keinem Eintrag aus der Tabelle `media` mehr zugewiesen sind.

Die zu löschenden Bilder werden zuerst in einer Tabellenansicht angezeigt, erst nach einer manuellen
Bestätigung werden die Bilder anschließend gelöscht.

Um den Löschvorgang ohne manuelle Bestätigung durchzuführen, kann `hbh:media:delete-orphaned-files --force` verwendet werden.

### Vorschau
Möchte man sich nur eine Liste der betroffenen Dateien ansehen, ohne diese zu löschen, kann der folgende Aufruf
verwendet werden: `hbh:media:delete-orphaned-files --dry-run`
