# E-Mail Templates
Sofern der Kunde nicht explizit selbst die Templates über das Backend verwalten möchte, sollte stattdessen
immer das Plugin [FroshPlatformTemplateMail](https://github.com/FriendsOfShopware/FroshPlatformTemplateMail) 
verwendet werden. Dies ermöglicht es die Templates in Dateien statt dem Backend (Datenbank) zu pflegen.
Dies erleichtert die Arbeit an den E-Mail-Templates und ermöglicht uns gleichzeitig die Versionierung.

Das Plugin wird automatisch im Standard mitgeliefert, es kann also ohne weiteres direkt verwendet werden.

## Verwendung von `FroshPlatformTemplateMail`
### Template-Verzeichnis
Vereinfacht gesagt wird in allen Plugins/Bundles nach den E-Mail-Templates gesucht. Hat ein Verkaufskanal ein
Theme-Plugin zugewiesen, wird zuerst dort gesucht, bevor in den anderen Plugins/Bundles gesucht wird.

Die Templates müssen hierbei im Verzeichnis `{plugin/bundle}/src/Resources/views/email` abgelegt werden,
dabei gilt folgende Priorität (Unterordner):

1. {salesChannelId}/{languageLocale} (z.B. `151cd0aa73ac490b937692d0881f4449/de-DE`)
2. {salesChannelId} (z.B. `151cd0aa73ac490b937692d0881f4449`)
3. {languageLocale} (z.B. `de-DE`)
4. {languageId} (z.B. `2fbb5fe2e29a4d70aa5854ce7ce3e20b`)
5. global

Beispiele:
- `custom/static-plugins/HbHProjectMainTheme/src/Resources/views/email/de-DE/order_confirmation_mail/html.twig`
- `custom/static-plugins/HbHProjectMainTheme/src/Resources/views/email/de-DE/order_confirmation_mail/plain.twig`
- `custom/static-plugins/HbHProjectMainTheme/src/Resources/views/email/de-DE/order_confirmation_mail/subject.twig`

Im Detail:
- `custom/static-plugins/HbHProjectMainTheme/` Pfad zum Plugin
- `/src/Resources/views/email/` Fixer Pfad innerhalb des Plugin-Verzeichnisses
- `de-DE` Priorität. Könnte z.B. `global` oder `{languageId}` sein
- `order_confirmation_mail` Template-Name, entspricht dem Wert aus der Tabelle `mail_template_type.technical_name`

Ob eine Datei für ein Template existiert kann im Backend unter `Einstellungen => Shop => E-Mail-Templates` in der Spalte `Theme` eingesehen werden.

- Linkes Icon ist grün = Datei für dem E-Mail Betreff ist vorhanden
- Mittleres Icon ist grün = Datei für den Mail-Text im Text-Format ist vorhanden
- Rechtes Icon ist grün = Datei für den Mail-Text im HTML-Format ist vorhanden

### Unterstützte Dateiformate
Neben `twig` unterstützt das Plugin auch [mjml](https://mjml.io/) Dateien. In diesem Fall muss die Dateiendnung nicht `.twig` sondern
`.mjml` lauten, also z.B. statt `order_confirmation_mail/html.twig` so `order_confirmation_mail/html.mjml`.

Bisher wurde allerdings nur die Funktionswiese mit `twig` getestet.

### Initiale Templates-Files erstellen
Der Command `frosh:template-mail:export {pfad}` erstellt für alle aktuellen E-Mail-Templates aus der Datenbank
die entsprechenden Twig-Templates im `twig`-Format in dem als `{pfad}` angegebenen Verzeichnis. Dies ist eine gute 
Ausgangsbasis, um den Ist-Zustand abzubilden und anschließend zu erweitern.

Sollen die Templates also im `HbHProjectMainTheme` erstellt werden, muss der Befehl wie folgt aufgerufen werden:
`frosh:template-mail:export custom/static-plugins/HbHProjectMainTheme/src/Resources/views/email/`

Anschließend sind die Templates im Verzeichnis `custom/static-plugins/HbHProjectMainTheme/src/Resources/views/email/{de-DE|en-GB}/` zu finden.
