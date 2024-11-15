# Plugin Lizenzen (Einmalig pro Projekt)
Für das Stage-System nutzen wir eine Wildcard-Umgebung, um die Plugins zu lizenzieren,
diese kann auch für die lokale Entwicklung verwendet werden, auch wenn die Domain (`shopware.dev.die-etagen.de`) dort abweichend ist.

Zum einrichten der Wildcard müssen folgende Schritte durchgeführt werden:

1. [Hier](https://account.shopware.com/) mit unserem Shopware Partneraccount (`info@die-etagen.de`) einloggen.
1. Menüpunkt `Partner` anklicken
1. Menüpunkt `Wildcard-Umgebungen` anklicken
1. Auf Button `Wildcard-Instanz erstellen` klicken
1. Als Projektname nutzen wir die spätere live domain ohne die Top-Level-Domain, also z.B. `mein-shop` für `mein-shop.de`)
1. Im Select-Feld muss `.dev.hob-by-horse.de` ausgewählt werden, die alte `dev.die-etagen.de` sollte nicht mehr verwendet werden. Unsere vollständige Wildcard-Domain lautet im Beispiel also `mein-shop.dev.hob-by-horse.de`
1. Abschließend auf `Instanz erstellen` klicken.

Jedes Store-Plugin welches installiert werden soll, muss zunächst der Wildcard hinzugefügt werden, das gilt auch für kostenlose Plugins.

**Sobald der Shop live gehen soll, müssen alle Plugins für die Live-Domain lizenziert werden.** 

Wie die Plugins anschließend via Composer installiert werden können, kann [hier](plugin-installation.md#shopware-store-plugins) nachgelesen werden.

**Wichtig: Vor Launch muss für die Live-Domain ein neuer Key generiert werden und den der Wildcard ersetzen.**