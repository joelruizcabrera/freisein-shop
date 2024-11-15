# Statische Code-Analyse

[[_TOC_]]

## PHPStan
Das Projekt wird mit einer Basis-Konfiguration für [PHPStan](https://phpstan.org/) ausgeliefert.
Um eine Analyse unter Berücksichtigung der [phpstan.dist.neon](../phpstan.dist.neon) durchzuführen, 
im Webserver-Container den Befehl `vendor/bin/phpstan` ausführen.

Soll nur ein bestimmter Ordner, oder Datei, analysiert werden, kann der Befehl wie folgt angepasst werden:
`vendor/bin/phpstan analyse {path/to/file/or/directory}`.

Die Konfiguration kann im Projekt nach eigenen Bedürfnissen angepasst werden und sollte immer versioniert werden. 
Für lokale Anpassungen kann stattdessen eine [phpstan.neon](../phpstan.neon) angelegt werden, diese bereits in 
der [.gitignore](../.gitignore) hinterlegt ist. 

Mit der Option `-c` kann beim Aufruf die Konfigurationsdatei angegeben werden, die für die Analyse verwendet werden soll.
Die Priorität zur Ermittlung der zu verwendenden Konfigurationsdatei ist wie folgt:

1. Mit Option `c` angegebene Konfigurationsdatei.
1. `phpstan.neon`-Datei im aktuellen Verzeichnis (Aufruf), sofern vorhanden.
1. `phpstan.neon.dist`-Datei im aktuellen Verzeichnis (Aufruf), sofern vorhanden.
1. `phpstan.dist.neon`-Datei im aktuellen Verzeichnis (Aufruf), sofern vorhanden.
1. Trifft nichts zu, wird keine Konfigurationsdatei verwendet.

## Pslam
Das Projekt wird mit einer Basis-Konfiguration für [Psalm](https://psalm.dev/) ausgeliefert.
Um eine Analyse unter Berücksichtigung der [psalm.xml](../psalm.xml) durchzuführen,
im Webserver-Container den Befehl `vendor/bin/psalm.phar` ausführen.

Die Konfiguration kann im Projekt nach eigenen Bedürfnissen angepasst werden und sollte immer versioniert werden.