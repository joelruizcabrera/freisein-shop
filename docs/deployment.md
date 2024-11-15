# Deployment

[[_TOC_]]

## Anforderungen an den Zielserver
- Support für SSH Key Login
- Git
- Symlink-Support

## Initiales Setup pro Projekt
Die mitgelieferte `deploy.php.dist` in `deploy.php` umbenennen und nach Bedarf anpassen.

### Hoster Besonderheiten
Es lohnt sich vorab [hier](hoster.md) zu prüfen, ob es für den Hoster Besonderheiten gibt, die beim
Deployment berücksichtigt werden müssen.

### Allgemeine Projektdaten
`set('application', '');`  
Hier kann der Projektname angegeben werden. Wird aktuell nicht speziell verwendet, sollte aber
trotzdem gepflegt werden.

`set('repository', '');`  
Hier muss das Projekt-Repository angegeben werden. Dieses muss immer auf `.git` enden.
Am besten öffnet man dazu das Repository in Gitlab und klickt auf  `Clone` und kopiert den
Wert bei `Clone with SSH`.

`Opcache setting`  
Ist auf dem Zielserver `opcache` aktiv, müssen die drei nachfolgenden Zeilen einkommentiert
bleiben, eine Modifizierung der Angaben ist nicht nötig. Falls `opcache` nicht aktiv ist, 
müssen diese auskommentiert werden.
> `set('cachetool_args', '--web --web-path={{deploy_path}}/current/public --web-url={{web_url}}');`  
> `before('deploy:vendors', 'cachetool:clear:opcache');`  
> `after('deploy:symlink', 'cachetool:clear:opcache');`  

### Host spezifische Daten
Für jeden Host, also z.B. `stage` oder `production`, müssen mindestens die folgenden individuellen
Daten angegeben werden:

`->setHostname('')`  
Die Adresse über die eine SSH-Verbindung zum Server hergestellt werden kann. Kann also sowohl
der Hostname des Servers selbst, als auch z.B. die Domain des Stage/Production-Systems sein.

`->setPort(22)`  
Standardmäßig wird Port 22 verwendet. Dies muss nur angepasst werden, wenn für die SSH-Verbindung
ein abweichender Port verwendet werden muss.

`->setRemoteUser('')`  
Hier muss der SSH-Benutzer angegeben werden.

`->setDeployPath('')`  
Hier muss das Verzeichnis auf dem Server angegeben werden, in das deployed werden soll,
also z.B. `/var/www/clients/client1/web6/web/stage`. In diesem Verzeichnis werden von deployer 
beim deployment automatisch die Ordner `.dep`, `releases` und `shared`, sowie der Symlink
`current`, angelegt.

**Wichtig**  
Das DocumentRoot für die URL muss später nach `current/public` verweisen. Also im Beispiel
nach `/var/www/clients/client1/web6/web/stage/current/public`. 

`->set('branch', '')`  
Hier muss der Branch angegeben werden, der für dieses Host deployed werden soll. In der Regel
ist dies für `stage` = `develop` und für `production` = `master`.

`->set('http_user', '')`  
Hier muss der System-Benutzer angegeben werden, unter dem der webserver ausgeführt wird.
Je nach Server kann dies unterschiedlich sein. In einigen Fällen ist dieser identisch mit dem
SSH-Benutzer, aber nicht immer. Im einfachsten Fall prüft man, wen der Server als Besitzer von
Ordner/Dateien auf dem Server/Web angibt und trägt diesen hier ein.

`->set('bin/php', '')`  
Um sicherzugehen, dass immer die korrekte PHP-Version auf dem Server verwendet wird, sollte diese
Zeile einkommentiert und der entsprechende Pfad (z.B. `/opt/php-8.1/bin/php`) angegeben werden. 
Der Pfad kann von Server zu Server unterschiedlich sein.

`->set('keep_releases', 3)`  
Hiermit kann festgelegt werden, wie viele Releases auf dem Server gespeichert werden sollen.
Deployer kümmert sich automatisch darum, dass bei Überschreitung, der älteste Release-Ordner gelöscht
wird.

`->set('web_url', '')`  
Wird nur benötigt, wenn `opcache` auf dem Server aktiv ist. In diesem Fall muss hier die URL
eingetragen werden, über die die Seite im Browser aufrufbar ist.

## Deployment

### Über lokal ausgeführten Command
#### Initiales Setup pro Entwickler
##### SSH Key Login
Kann man sich noch nicht über den eigenen Key per SSH in den Zielserver einloggen,
folgendes ausführen:   
`ssh-copy-id -i {/pfad/zum/key} {user}@{server}`.

- `{/pfad/zum/key}` Hier muss der eigene Public-Key angegeben werden also z.B. `~/.ssh/id_rsa.pub`.
- `{user}` Der Benutzer auf dem Zielserver, mit dem man sich per SSH einloggen will.
- `{server}` Der Zielserver (IP oder Hostname)

Nach Eingabe des SSH-Passworts für den angegebenen `{user}`, wird der `{/pfad/zum/key}` in die `~/.ssh/authorized_keys` des
`{user}` auf dem `{server}` eingetragen.

Anschließend sollte man sich ohne Eingabe eines Passworts per SSH mit dem Benutzer auf dem Zielserver einloggen können.

#### Deployen
Das Deployment wird lokal auf dem PC des Entwicklers angestoßen und nutzt dessen SSH-Key
sowohl für den Login auf den Zielserver, als auch für den Zugriff auf das Projektrepository in
unserer Gitlab-Instanz.

Um das deployment anzustoßen folgenden Befehl im Projektverzeichnis ausführen:
`vendor/bin/dep deploy {umgebung}`.

Der Wert `{umgebung}` muss einer in der `deploy.php` konfigurierten Umgebungen entsprechen,
also z.B. `stage` oder `production`.

### Über Gitlab Pipeline
> **Warning**
>
> Wurde bisher nicht ausführlich getestet!

Aktuell kümmert sich die Pipeline wirklich nur um das Deployment, es finden also keine Tests oder sonstigen Schritte statt.

#### Initiales Setup pro Projekt
Die mitgelieferte `.gitlab-ci.yml.dist` in `.gitlab-ci.yml` umbenennen und nach Bedarf anpassen.

##### .gitlab-ci.yml nach Bedarf anpassen
- Sollte nicht PHP 8.1 verwendet werden, muss das Docker-Image (Zeile 4) angepasst werden.
- In Zeile 31 (Stage) und 39 (Production) kann die Bedingung angepasst werden, unter denen der entsprechende Task ausgeführt wird.

##### SSH-Key erzeugen und Public-Key auf dem Zielserver hinterlegen
Einen SSH-Key generieren und den Public-Key in der `~/.ssh/authorized_keys` des Zielservers hinterlegen, 
ggf. sowohl für Stage als auch Production, wenn es verschiedene Benutzer sind. 

Das kann z.B. über `ssh-copy-id -i {/pfad/zum/key} {user}@{server}` gemacht werden. 
Hierbei muss unbedingt der zuvor generierte Public-Key angegeben werden und nicht der eigene.

##### SSH-Key Private-Key als Variable im Gitlab-Repo hinterlegen
In Gitlab im Repo auf `Settings => CI/CD => Variables` gehen und dort eine neue Variable mit der Bezeichnung `SSH_PRIVATE_KEY` anlegen. 
Dort muss der Private-Key der zuvor generiert wurde hinterlegt werden und nicht der eigene!

#### Deployen
Zum deployen in Gitlab im Repo auf `Build => Pipelines => Run pipelines` gehen und den gewünschten Branch auswählen (Im Standard: `master` für Prod deployment und `develop` für Stage-Deployment) und auf `Run pipeline` klicken.

Danach wird das Deployment ausgeführt und man landet in einer Übersicht, wo der Status eingesehen werden kann.
Für das Deployment selbst wird weiterhin die `deploy.php` verwendet, diese muss also im Projekt vorhanden sein.

## Spezialfall initiales Deployment
Nach dem initialen deployment müssen noch ein paar Schritte manuell auf dem Zielserver durchgeführt werden,
um den Shop zu installieren.

### Konfiguration in `.env`-Datei anpassen
Auf dem Server, in der Datei `shared/.env`, die Konfiguration nach Bedarf anpassen.
Was in der Regel immer geändert werden muss, da pro Server/Instanz abweichend, sind die folgenden Angaben:

- `MAILER_DSN` Angabe `native://default` um `sendmail` binary zu verwenden. Wird nur berücksichtigt, wenn in der SW-Administration bei Mailer `Umgebungs-Konfiguration benutzen` ausgewählt wird.
- `APP_ENV` Im Stage und Production sollte im Normalfall immer `prod` verwendet werden.
- `APP_URL` Haupt-URL der Shopware-Instanz.
- `APP_SECRET` Wert mit folgendem Befehl generieren `system:generate-app-secret` ("Failed to load plugins."-Warnung ignorieren)
- `INSTANCE_ID` Wert mit folgendem Befehl generieren `system:generate-app-secret` ("Failed to load plugins."-Warnung ignorieren)
- `DATABASE_URL` Angaben zur Datenbank

### Shop installieren
Auf dem Server im Projektverzeichnis (`current`, nicht `current/public`), den nachfolgenden Befehl ausführen:

```console
bin/console hbh:system:install --shop-currency=EUR --shop-locale=de-DE --shop-email="test@example.com" --shop-name="Shopname" --admin-username="admin" --admin-password="shopware"
```

Die Werte für die einzelnen Optionen können hierbei nach Bedarf angepasst werden.

### Ausführung zusätzlicher Scripte triggern
Nachdem der Shop installiert wurde, auf dem Server im Projektverzeichnis (`current`, nicht `current/public`), noch
`./composer install` ausführen. Dies dient hauptsächlich dazu weitere Scripte anzustoßen, die erst ausgeführt werden, wenn der Shop bereits
installiert ist. Dazu zählt z.B. die automatische Installation und Aktivierung der Plugins.

### Domain im Verkaufskanal hinterlegen
Damit das Frontend erreichbar ist, in der Administration einloggen und den gewünschten Verkaufskanal auswählen und bei 
"Domains" die entsprechende Domain hinterlegen und Speichern.

## Mögliche Fehler beim Deployment
### Host key verification schlägt fehl
Dieses Problem tritt auf, wenn der Zielserver noch keinen Kontakt mit unserer Gitlab-Instanz hatte.
Unsere Gitlab-Instanz muss in der `~/.ssh/known_hosts` hinterlegt sein, damit das deployment funktioniert.

Dafür per SSH mit dem Zielserver verbinden und dort folgendes ausführen:  
**Wenn Hoster nicht Timme bzw. nicht ISPConfig verwendet wird:**  
`ssh-keyscan -H git.die-etagen.de >> ~/.ssh/known_hosts`.  

**Wenn Hoster Timme bzw. ISPConfig verwendet wird:**  
`ssh-keyscan -H git.die-etagen.de >> ~/../../.ssh/known_hosts`.  
Erklärung [hier](hoster.md#ssh-user-vs-web-user).

### Kein Zugriff auf das Projekt-Repository möglich
Sicherstellen, dass das [ssh-agent-forwarding](https://developer.github.com/v3/guides/using-ssh-agent-forwarding/) auf
dem eigenen PC aktiviert ist. Nur dann ist es dem Zielserver möglich, die Verbindung zu unserer Gitlab-Instanz über
den eigenen/lokalen SSH-Key herzustellen.
