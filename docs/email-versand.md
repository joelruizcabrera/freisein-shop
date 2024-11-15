# E-Mail versand

[[_TOC_]]

## Lando-Entwicklungsumgebung / Catch-All
Damit alle lokal verschickten E-Mails an den `Mailhog`-Proxy weitergeleitet werden, muss in der [.env](../.env)
für `MAILER_DSN` folgender Wert angegeben werden `smtp://mailhog:1025?encryption=&auth_mode=`.

Damit der Wert berücksichtigt wird, muss zudem sichergestellt werden, dass in der Shop-Administration
unter `Einstellungen => System => Mailer` der Wert `Umgebungs-Konfiguration benutzen` ausgewählt ist.

## Production- und Stage-Umgebung
TBD