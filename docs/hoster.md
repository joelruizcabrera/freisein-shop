# Hoster

Nachfolgend werden relevante Informationen und Besonderheiten für einzelne Hostern aufgeführt.

[[_TOC_]]

## MaxCluster
### DocRoot aktualisieren
Bei MaxCluster gibt es ein fixes DocRoot: `/var/www/share/{domain}/htdocs/`, welches auch nicht direkt geändert werden
kann. Es ist aber möglich den Ordner `htdocs` durch einen Symlink zu ersetzen.

Um das DocRoot in das `current/public`-Verzeichnis zeigen zu lassen, folgendes machen:

Mit `cd /var/www/share/{domain}` in das Stammverzeichnis der Domain wechseln, `{domain}` dabei durch die
jeweilige Domain ersetzen.

Mit `rm -r htdocs` das Standard `htdocs`-Verzeichnis entfernen und mit `ln -s current/public htdocs`
ein gleichnamigen Symlink anlegen, der auf unser gewünschtes DocRoot verweist.

### OPCache nach Deployment leeren
Der [OPCache](https://maxcluster.de/knowledge-base/shopperformance/php-opcache) kann bei MaxCluster über das
[Cluster-Control](https://maxcluster.de/blog/2020/5/neues-feature-cluster-control) Command geleert werden, indem dort der Befehl `php:reload` ausgeführt wird. 
Dafür kann der tasks `maxcluster:clear:opcache` in der deploy-Konfiguration des Projekts verwendet
werden.

Wichtig: Dem Befehl müssen 3 individuelle Parameter übergeben werden, zum einen die Bezeichnung
des Clusters, dann die vom Server und zuletzt noch ein Person-Access-Token.

Letzter muss über einen MaxCluster-Account erstellt werden. Aktuell ist noch unklar, ob man
auch Account unabhängig einen Token erstellen kann.

### Optimierung
Grundsätzlich kann im `Managed Center`, unter dem Menüpunkt [ShopPerformance](https://maxcluster.de/knowledge-base/shopperformance/was-ist-shopperformance), 
ein automatischer Scan durchgeführt werden, der einem viele nützliche Optimierungsmöglichkeiten für die ausgewählte
Shopware-Instanz anzeigt.

#### OPCache-Einstellunen
In [diesem](https://maxcluster.de/knowledge-base/shopperformance/php-opcache) Artikel gibt es ein paar Tipps zur
Konfiguration der OPCache-Werte.

Dafür im `Managed Center` unter `Webserver` entweder `Apache` oder `NGINX` auswählen,
je nachdem was verwendet wird. Dann ganz oben auf `PHP GLOBALE CONFIG` klicken und die verwendete
PHP-Version auswählen. Daraufhin erscheint ein neues Fenster wo die Einstellunge gesetzt werden
können.

Nachfolgend die Werte, die wir standardmäßig nutzen sollten:
- `opcache.max_accelerated_files` = 65407
- `opcache.interned_strings_buffer` = 64MB
- `opcache.revalidate_freq` = 120s
- `opcache.memory_consumption` = 512MB

#### PHP-Worker-Prozesslimit
Je nach zu erwartender Besucheranzahl sollte das PHP-Worker Prozesslimit konfiguriert werden, damit wird festgelegt wie 
viele PHP-Anfragen parallel abgearbeitet werden können. Mehr dazu [hier](https://maxcluster.de/knowledge-base/shopperformance/php-fpm-worker).

Die `ShopPerformance`-Analyse schlägt automatisch anhand der bisherigen Besucherzahlen einen Wert vor.

Dafür im `Managed Center` unter `Webserver` entweder `Apache` oder `NGINX` auswählen, je nachdem was verwendet wird.
Die gewünschte Domain bearbeiten und im Reiter `Modus` die gewünschten Einstellungen vornehmen.

#### Varnish-Cache aktivieren
Varnish ist ein Full-Page-Cache für Webanwendungen. Seiteninhalte werden im Arbeitsspeicher gehalten, um die Ladezeit der Webanwendung 
stark zu beschleunigen, da z.B. PHP-Code nur einmalig ausgeführt werden muss und das Ergebnis bei nachfolgenden Anfragen
aus dem Cache geholt werden kann.

Wie schon der von Shopware mitgelieferte HTTP-Cache funktioniert dies standardmäßig nur für Besucher, die weder
eingeloggt sind, noch Artikel im Warenkorb haben.

Die Einrichtung bei MaxCluster wird [hier](https://maxcluster.de/blog/performance-pagespeed-boosting-varnish-cache) beschrieben.
Wichtig ist das man auch den Link zur [Shopware-Dokumentation](https://developer.shopware.com/docs/guides/hosting/infrastructure/reverse-http-cache.html#overview) 
berücksichtigt, da das dortige VCL-Template in der Varnish-Konfiguration im MaxCluster Managed Center übernommen werden muss.

### Bereitstellung einer `.env.local.php`
Shopware bzw. Symfony bietet die Möglichkeit eine [.env.local.php](https://developer.shopware.com/docs/guides/hosting/performance/performance-tweaks.html#env-local-php) 
bereitzustellen, damit die `.env`-Datei nicht bei jedem Aufruf geparsed werden muss. Dafür sollte nach jeder Änderung an
den ENV-Dateien folgender Befel ausgeführt werden: `bin/console dotenv:dump {APP_ENV}` das `{APP_ENV}` muss entsprecend
durch `prod` oder `dev` ersetzt werden.

Da eine bereits bestehende `.env.local.php` bereits gecached sein könnte, muss bei einer Änderung ggf.
einmal ein PHP-Reload über das `Cluster Control` oder `Managed Center` durchgeführt werden.

####
https://maxcluster.de/knowledge-base/shopperformance/shopware6-envlocalphp-verwenden

## Timme
### Pfad zur PHP-Version
Die PHP Versionen liegen in Verzeichnis `/opt`, pro verfügbarer PHP-Version gibt
es dort einen Unterordner z.B. `/opt/php-8.1/`. 

Um z.B. die PHP Version 8.1 für das Deployment zu nutzen, muss beim Host in der `deploy.php` 
folgendes angegeben werden: `->set('bin/php', '/opt/php-8.1/bin/php')`.

### Webspace-Einstellungen im ISPConfig
Im ISPConfig müssen bei der Webseite folgende Angaben gemacht werden:

**Im Reiter `Domain`:**  
Bei `Gewünschte Konfiguration` die Option `Shopware 6.x` auswählen.

Bei `Erweiterte Einstellungen > Abweichender Document Root` den `current` Symlink aus dem Verzeichnis 
eintragen, der in der `deploy.php` bei `setDeployPath` hinterlegt ist. Es darf nicht direkt `current/public` angegeben 
werden, da Timme durch die Verwendung der Konfiguration `Shopware 6.x` automatisch in das `Public`-Verzeichnis
des angegebenen DocRoot leitet.

Wurde in der `deploy.php` bei `setDeployPath` also z.B. `/var/www/swdeploytest.hob-by-horse.de/web/stage` angegeben,
muss bei `Abweichender Document Root` nun `stage/current` hinterlegt werden. Als DocumentRoot für die Domain
wird dann automatisch `/var/www/swdeploytest.hob-by-horse.de/web/stage/current/public` verwendet.

### SSH-User vs Web-User
Bei ISPConfig ist der SSH-User nicht der gleiche wie der Web-User. 

**Beispiel:**
- SSH-User: c683081swdeploytest
- Web-User: web6

Wenn man sich per SSH mit dem Benutzer `c683081swdeploytest` einloggt und in das Home-Verzeichnis
navigiert (`cd ~`) landet man in `/var/www/clients/client1/web6/home/c683081swdeploytest`. 
Das Home-Verzeichnis für den SSH-User ist aber `/var/www/clients/client1/web6`.

Das ist z.B. für das Depoyment relevant, weil nicht die `/var/www/clients/client1/web6/home/c683081swdeploytest/.ssh/known_hosts`
berücksichtigt wird, sondern die `/var/www/clients/client1/web6/.ssh/known_hosts`.
