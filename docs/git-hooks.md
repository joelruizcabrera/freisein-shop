# Git Hooks

[[_TOC_]]

## Erklärung
Das Projekt wird standardmäßig mit einem [.githooks](../.githooks) Ordner ausgeliefert, der bereits einige
Scripte enthält und um weitere ergänzt werden kann.

Da der Ordner Teil des Repository ist, stehen die Scripte jedem zur Verfügung, der am Projekt arbeitet.

Diese werden aber nicht automatisch von Git berücksichtigt/angewendet, da standardmäßig nur Scripte
im lokalen Ordner `.git/hooks` berücksichtigt werden.

## Mitgelieferte Scripte/Hooks installieren
### Manuell (Kopieren)
Möchte man nur einzelne Scripte verwenden, können diese aus dem Ordner [.githooks](../.githooks) in den
Ordner `.git/hooks` kopiert werden.

In diesem Fall muss man selbständig prüfen, ob ein Script aktualisiert wurde und das lokale entsprechend
aktualisieren.

### Automatisch
Möchte man alle Projekt-Hooks automatisch berücksichtigen, muss der Befehl `git config core.hooksPath .githooks`
im Projektverzeichnis ausgeführt werden. Dieser sorgt dafür, dass Git bei diesem Projekt nicht mehr im Ordner
`.git/hooks`, sondern [.githooks](../.githooks), nach den Hook-Scripten schaut.

Der Vorteil ist, dass man sich fortan nicht mehr darum kümmern muss und immer automatisch
die aktuellsten Scripte angewendet werden.

## Mitgelieferte Scripte/Hooks
### PRE-COMMIT
#### Coding-Standard automatishc anwenden
Vor jedem commit wird automatisch das [ECS-Script](coding-standards.md) ausgeführt, was dafür sorgt, dass alle
im commit enthaltenen Dateien gemäß des coding-standards angepasst werden.

In den allermeisten Fällen passiert das im Hintergrund, ohne das man davon etwas mitbekommt.
In seltenen Fällen kann ein Fehler auftreten, der nicht automatisch behoben werden kann. 
In diesem Fall wird der Commit abgebrochen und der Fehler muss manuell behoben werden.
