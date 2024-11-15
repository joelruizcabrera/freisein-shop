# Logging

Shopware nutzt für das Logging die [Monolog-Library](https://github.com/Seldaek/monolog).
Der Standard Logging Channel für Shopware, bzw. Symfony, lautet `app`. Jedes Bundle/Plugin kann 
weitere Channels definieren und konfigurieren.

## Projekt-Logging
### Aktuelle Konfiguration anzeigen
Über den Command `bin/console debug:config monolog` kann man sich die aktuelle Monolog-Konfiguration anzeigen
lassen, hierbei werden bereits die Konfigurationen sämtlicher geladener Konfigurationsdateien berücksichtigt.

### Konfiguration anpassen
Die Konfiguration kann an verschiedenen Stellen erfolgen und wird später zu einer großen Konfiguration zusammengeführt, 
so kann z.B. jedes Plugin seine eigene Monolog-Konfiguration mitliefern. Werden z.B. abweichende Angaben zu einem Handler 
in verschiedenen Dateien gemacht, gewinnt immer die zuletzt eingelesene Konfiguration.

Die nachfolgenden Dateien werden immer zuletzt berücksichtigt:

- [config/packages/monolog.yaml](../config/packages/monolog.yaml) = Wird unabhängig vom `ENV`-Wert eingelesen und nach allen Bundle-Konfigurationen ausgewertet.
- [config/packages/dev/monolog.yaml](../config/packages/dev/monolog.yaml) = Wird nur bei `ENV=dev` eingelesen und nach `config/packages/monolog.yaml` ausgewertet.
- [config/packages/prod/monolog.yaml](../config/packages/prod/monolog.yaml) = Wird nur bei `ENV=prod` eingelesen und nach `config/packages/monolog.yaml` ausgewertet.

Informationen zu den Konfigurationsmöglichkeiten können [hier](https://raw.githubusercontent.com/symfony/monolog-bundle/master/DependencyInjection/Configuration.php) eingesehen werden.
Eine Übersicht der verfügbaren Handler, Formatters und Processors ist [hier](https://seldaek.github.io/monolog/doc/02-handlers-formatters-processors.html) zu finden.

Da alle Dateien in `config/packages/` zu einer großen Konfigurationsdatei zusammengeführt werden spielt es theoretisch
keine Rolle ob die Konfiguraiton in einer Datei vorgenommen wir dide `monolog.yaml` oder `test123.yaml` heißt. So legt
Shopware standardmäßig ein paar eigene Monoglog-Ergänzungen in der `shopware.yaml` ab. Um dies zu vereinheitlichen packen
wir alles für das Logggin relevante in die `monolog.yaml`.

#### Plugin-Konfiguration überschreiben
Nachfolgend an einem Beispiel, wie die Logging-Konfiguration eines Plugins im Projekt überschrieben werden kann.

```YAML
# custom/static-plugins/HbHDummyPlugin/src/Resources/config/packages/monolog.yaml
monolog:
    channels: ['my_plugin_channel']

    handlers:
        myPluginLogHandler:
            # The higher the value, the earlier the handler is executed (important for e.g. bubble). Default is 0
            priority: 1
            level: debug
            # false = Records handled by this handler will not propagate to the following handlers
            bubble: false
            channels: [ "my_plugin_channel" ]
            path: "%kernel.logs_dir%/my_plugin_%kernel.environment%.log"
            max_files: 10
```

```YAML
# config/packages/monolog.yaml
monolog:
    handlers:
        myPluginLogHandler:
            level: error
```

Möchte man stattdessen die Nachrichten des Channels `my_plugin_channel` nur noch von einem eigenen Handler, statt dem 
`myPluginLogHandler`, bearbeiten lassen, könnte dieser mit einer höheren Priorität und der Angabe `bubble: false` definiert werden.

Durch die höhere Priorität würde die Nachricht zuerst vom eigenen Handler bearbeitet werden und durch die Angabe `bubble: false`
nicht mehr an `myPluginLogHandler` weitergeleitet werden.

### Ausnahme für Exceptions
In der `monolog.yaml` kann festgelegt werden, welche Exceptions nicht geloggt werden sollen.
Standardmäßig ist in unserem Standard in `config/packages/monolog.yaml` folgendes angegeben:

```YAML
shopware:
    logger:
        # Set the max number of log files before the oldest gets deleted
        file_rotation_count: 14
        # All listed exceptions are excluded from logging
        exclude_exception:
            - League\OAuth2\Server\Exception\OAuthServerException
            - Symfony\Component\HttpKernel\Exception\NotFoundHttpException
            - Shopware\Core\Checkout\Cart\Exception\LineItemNotFoundException
            - Shopware\Core\Content\Media\Exception\DuplicatedMediaFileNameException
            - Shopware\Core\Content\Media\Exception\IllegalFileNameException
```

Die Angabe kann theoretisch auch in der `config/packages/shopware.yaml` erfolgen. Damit die für das Logging relevanten
Angaben aber an einer zentralen Stelle gepflegt werden können, haben wir dies stattdessen in die `monolog.yaml` ausgelagert.

Shopware selbst ignoriert standardmäßig schon einige Exceptions, siehe: `shopware/core/Framework/Resources/config/packages/shopware.yaml`

#### Erklärung der Funktionsweise
Tritt eine Exception auf die nicht abgefangen wird, sorgt der `Symfony\Component\HttpKernel\EventListener\ErrorListener` dafür
das ein Log-Eintrag dafür geschrieben wird. Hierbei wird die Exception selbst als Context mit dem Key `exception` übergeben.

Der `Shopware\Core\Framework\Log\Monolog\ExcludeExceptionHandler` sorgt dafür, dass alle Log-Einträge die im Context
eine `exception` angegeben haben und in `exclude_exception` aufgeführt sind, übersprungen werden.

Der `ExcludeExceptionHandler` dekoriert den `monolog.handler.main`, siehe `shopware/core/Framework/DependencyInjection/services.xml`:
```XML
<service id="Shopware\Core\Framework\Log\Monolog\ExcludeExceptionHandler" decorates="monolog.handler.main" decoration-on-invalid="ignore">
    <argument type="service" id="Shopware\Core\Framework\Log\Monolog\ExcludeExceptionHandler.inner"/>
    <argument>%shopware.logger.exclude_exception%</argument>
</service>
```

### Deprecated Einträge
Die Einträge werden nur im Debug-Mode geloggt, also wenn `debug` auf `true` steht. Das ist standardmäßig nur bei `env=dev` der Fall, daher
braucht es für `env=prod` keine spezielle Konfiguration.

Um deprecated-Meldungen einfacher prüfen und/oder ignorieren zu können, loggen wir diese nicht im `dev.log`, sondern
im `dev_deprecated.log`. Dafür wird der Channel `php` in der [dev/monolog.yaml](../config/packages/dev/monolog.yaml) 
beim `main`-Handler ausgeschlossen und dafür stattdessen ein eigener Handler `deprecated` erstellt.
 
#### Erklärung der Funktionsweise
Der `Symfony\Component\ErrorHandler\DebugClassLoader` führt verschiedene Prüfungen durch und kann dabei u.a. dafür
sorgen, dass `deprecated`-Log-Einträge erstellt werden. Dieser Service ist nur aktiv, wenn `$debug = true` ist,
siehe: `Symfony\Component\Runtime\GenericRuntime::__construct()`:

```PHP
if ($debug) {
    // ...
    if (false !== $errorHandler = ($options['error_handler'] ?? BasicErrorHandler::class)) {
        $errorHandler::register($debug);
        // ...
    }
}
```

Durch den Aufruf von `Symfony\Component\Runtime\Internal\SymfonyErrorHandler::register()`, wird u.a.
der `Symfony\Component\ErrorHandler\DebugClassLoader` ergänzt.

Wenn nicht explizit gesetzt, ist `debug` bei Verwendung von `env=prod` = `false` und  ansonsten `true`. 

Siehe dazu [index.php](../public/index.php):

```PHP
$appEnv = $context['APP_ENV'] ?? 'dev';
$debug = (bool) ($context['APP_DEBUG'] ?? ($appEnv !== 'prod'));
```

und [console](../bin/console):
```PHP
$env = $input->getParameterOption(['--env', '-e'], $context['APP_ENV'] ?? 'prod', true);
$debug = ($context['APP_DEBUG'] ?? ($env !== 'prod')) && !$input->hasParameterOption('--no-debug', true);
```

### E-Mail Benachrichtigung im Fehlerfall
Standardmäßig wird in `ENV=prod` bei allen Fehlern ab Level error (400), mit Ausnahme von 404-Fehlern,
eine E-Mail verschickt. Dafür muss in der [.env](../.env) zwingend `ERROR_MAIL_RECIPIENTS` einkommentiert
und dort die entsprechenden Empfänger hinterlegt werden.

Außerdem können dort auch die Standardwerte von `ERROR_MAIL_SUBJECT` und `ERROR_MAIL_FROM` angepasst werden.
Möchte man z.B. für das Production-System einen anderen Betreff als für das Stage-System haben, kann der
Wert für `ERROR_MAIL_SUBJECT` in der jeweiligen [.env.local](../.env.local) überschrieben werden.

#### Achtung Shopware-Bug
Aufgrund eines [Bugs](https://issues.shopware.com/issues/NEXT-34417) kann der Monolog Handler `"symfony_mailer` aktuell nicht ohne weiteres
in Shopware verwendet werden. Solange dieser Bug nicht gefixed ist, nutzen wir folgenden
[CompilerPass](../src/CompilerPass/RemoveLoggerCompilerPass.php) als Workaround.

Aktuell kann die E-Mail-Benachrichtigung nur im `ENV=prod` genutzt werden, da es im `ENV=stage` noch
zu einem weiteren Fehler kommt, für den es aktuell keinen Workaround gibt.

#### E-Mail Benachrichtigung deaktivieren
Möchte man die E-Mail Benachrichtigung deaktivieren, genügt es, den `mail_handler` in der [monolog.yaml](../config/packages/prod/monolog.yaml)
auszukommentieren. Die Handler `deduplicated` und `symfony_mailer` müssen nicht zwingend auskommentiert werden.
Da dort die Angabe `nested: true` gesetzt ist, werden diese automatisch ignoriert, wenn der parent handler `mail_handler` nicht mehr existiert.

#### Eine andere Quelle als Umgebungsvariablen für Betreff, Absender und Empfänger nutzen
Möchte man Angaben wie z.B. Empfänger und Betreff aus der Datenbank beziehen, muss dafür ein Service erstellt werden der das
`Shopware\Production\Logging\ErrorMail\ErrorMailConfigInterface` implementiert. 

Anschließend muss in der [logging.xml](../src/DependencyInjection/logging.xml) die folgende Service-Definition angepasst werden:
```XML
<service id="Shopware\Production\Logging\ErrorMail\ErrorMail">
    <argument type="service" id="Shopware\Production\Logging\ErrorMail\EnvErrorMailConfig"/>
</service>
```

Statt `Shopware\Production\Logging\ErrorMail\EnvErrorMailConfig` muss hier nun der neue Service übergeben werden.

### Plugin-Logging
Jedes Plugin kann eine eigene Monolog-Konfiguration mitliefern. Dafür muss die `build`-Methode in der Plugin-Basisklasse 
entsprechend erweitert werden:

```PHP
<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

public function build(ContainerBuilder $container): void
{
        $locator = new FileLocator('Resources/config');

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
        ]);

        $configLoader = new DelegatingLoader($resolver);

        $confDir = \rtrim($this->getPath(), '/') . '/Resources/config';

        $configLoader->load($confDir . '/{packages}/*.yaml', 'glob');
}
```

Anschließend kann im Plugin unter `Resources/config/packages/` z.B. eine `monolog.yaml` angelegt werden
und dort eigene Channels und Handler definiert werden.

## Log-Level
Monolog unterstützt die nachfolgenden RFC 5424 Log-Level:

- **DEBUG** (100): Detailed debug information.
- **INFO** (200): Interesting events. Examples: User logs in, SQL logs.
- **NOTICE** (250): Normal but significant events.
- **WARNING** (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
- **ERROR** (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
- **CRITICAL** (500): Critical conditions. Example: Application component unavailable, unexpected exception.
- **ALERT** (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
- **EMERGENCY** (600): Emergency: system is unusable.

## Channels
Der Standard Logging Channel für Shopware bzw. Symfony lautet `app`. Dieser Channel wird immer dann verwendet, wenn 
`<argument type="service" id="logger"/>` als Logger an den jeweiligen Service  übergeben wird.

Die id "logger" ist ein Alias für den Default-Logger bzw. Channel "app", siehe `symfony/monolog-bundle/Resources/config/monolog.xml`:
```XML
<service id="monolog.logger" parent="monolog.logger_prototype" public="false">  
    <argument index="0">app</argument>  
    <call method="useMicrosecondTimestamps">  
        <argument>%monolog.use_microseconds%</argument>  
    </call></service>  
  
<service id="logger" alias="monolog.logger" />  
  
<service id="Psr\Log\LoggerInterface" alias="logger" public="false" />
```

### Notation für Angabe in `channels`
- `~` = Include all the channels  
- `foo` = Include only channel 'foo'
- `'!foo'` = Include all channels, except 'foo'
- `[foo, bar]` = Include only channels 'foo' and 'bar'
- `['!foo', '!bar']` = Include all channels, except 'foo' and 'bar'

## Processor
Monolog ermöglicht es jeden Datensatz vor dem Logging zu verarbeiten, indem zusätzliche Daten hinzugefügt werden. 
Dies wird über die Processors gemacht, die entweder für alle Handler/Channel, oder aber auch nur bestimmte, aktiviert
werden können. 

### Beispiel
```YAML
# config/packages/monolog.yaml
services:
    Monolog\Processor\UidProcessor:
        autoconfigure: false
        tags:
            - { name: monolog.processor, channel: 'my_plugin_channel' }
```

In diesem Fall wird der Processor [UidProcessor](https://github.com/Seldaek/monolog/blob/main/src/Monolog/Processor/UidProcessor.php) nur für den Chanel `my_plugin_channel` aktiviert.
Folgende Angaben sind möglich:

- `{ name: monolog.processor, handler: {handler} }` Nur für den Handler `{handler}` aktivieren.
- `{ name: monolog.processor, channel: {channel} }` Nur für den Channel `{channel}` aktivieren.
- `{ name: monolog.processor }` Für alle Handler und Channel aktivieren.

Es ist nicht möglich channel und handler gleichzeitig anzugeben. Wird keines von beiden angegeben, gilt es immer für alle Channels.
Siehe dazu `Symfony\Bundle\MonologBundle\DependencyInjection\Compiler\AddProcessorsPass`.

Die Angabe `autoconfigure: false` ist in diesem Fall wichtig, da es in `Symfony\Bundle\MonologBundle\DependencyInjection\MonologExtension` folgenden
Code gibt: 

```PHP
if (interface_exists(ProcessorInterface::class)) {  
    $container->registerForAutoconfiguration(ProcessorInterface::class)  
        ->addTag('monolog.processor');  
}
```

Da der **UidProcessor** das Interface `ProcessorInterface` implementiert, würde dieser bei `autoconfigure: true` automatisch 
für alle Handler und Channel aktiviert werden und die Angabe in der Service Tag-Definition ignoriert werden.

## Logger-Services im Container (DependencyInjection)
Im Container befindet sich für jeden Channel ein eigener Service z.B. `monolog.logger.hbh-simple-queue` oder `monolog.logger.my_plugin_channel`. 
Diese sind vom Typ `Symfony\Component\DependencyInjection\ChildDefinition` und haben als parent `monolog.logger_prototype` angegeben.
Diese Services werden automatisch von Symfony für jeden in der Konfiguration definierten Channel erstellt.

Entsprechend kann auch die Angabe in der Service-Definition erfolgen:

```XML
<service id="HbH\SimpleQueue\Processor\JobProcessor">  
    <argument type="service" id="event_dispatcher"/>  
    <argument type="service" id="HbH\SimpleQueue\Helper\JobHelper"/>  
    <argument type="service" id="monolog.logger.hbh-simple-queue"/>  
</service>
```
