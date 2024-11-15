# Coding Standards

[[_TOC_]]

## Standard automatisch anwenden
Um unsere Coding Standards automatisch anzuwenden, nutzen wir [easy coding standard](https://github.com/symplify/easy-coding-standard).
Das ist eine Kombination aus [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) und [PHP Coding Standards Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer).

Die Konfiguration wird in der [ecs.php](../ecs.php) vorgenommen, hier wird z.B. angegeben, welche Standards angewendet werden sollen.

Nachfolgend werden mehrere Möglichkeiten beschrieben `ECS` auszuführen.

### Auf das ganze Projekt anwenden
Das `ganze Projekt` bedeutet in diesem Fall auf alle Dateien in den konfigurierten Pfaden (`paths`).
Für einen Testlauf kann `vendor/bin/ecs check` ausgeführt werden. Um die Änderungen auch anzuwenden, muss
`vendor/bin/ecs check --fix` ausgeführt werden.

### Auf einen Ordner oder Datei anwenden
In diesem Fall wird zwar auch die Konfigurationsdatei berücksichtigt, die dort angegebenen Pfade werden
aber ignoriert. Für einen Testlauf kann `vendor/bin/ecs check {path/to/filename} {path/to/filename2}`
ausgeführt werden. Um die Änderungen auch anzuwenden, muss `vendor/bin/ecs check {filename} {filename2} --fix` 
ausgeführt werden.

Statt eines Dateinamens kann hierbei auch ein Ordner angegeben werden.

### Automatisch vor jedem Commit
Bei Nutzung der [Git-Hooks](git-hooks.md) werden die Coding Standards automatisch auf alle Dateien angewendet
bevor diese commited werden.
