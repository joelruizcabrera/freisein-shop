# Häufige Fehlerursachen
Nachfolgend werden Fehler beschrieben die häufiger vorkommen, was der Grund dafür ist und wie
diese behoben werden können.

[[_TOC_]]

## Allowed memory size exhausted
Beim Ausführen von Befehlen wie z.B. `bin/console hbh:system:install` oder `./composer install` kommt
es zu dem oben genannten Fehler kommen.

Dies liegt daran, dass hierbei das für PHP definierte `memory_limit` überschritten wird.

Der einfachste Weg ist, dass `memory_limit` global in der `php.ini` des Servers zu erhöhen.
Alternativ kann das `memory_limit` auch für einen einzelnen Aufruf angepasst werden.

Beim direkten Aufruf über PHP geht dies z.B. wie folgt:
```console
php -d memory_limit=-1 bin/console hbh:system:install
```

und bei composer so:
```console
php -d memory_limit=-1 {path/to/composer} install
```

> **Korrekte PHP-Version verwenden**  
> Es muss hierbei sichergestellt werden, dass die korrekte PHP-Version angegeben wird.
> Wenn der im Beispiel verwendete alias `php` nicht mit der gewünschten Version verknüpft ist,
> muss der alias/pfad angepasst werden. Mit `php --version` kann dies einfach geprüft werden.
> Eventuell weitere auf dem Server verfügbare PHP-Versionen können mit `whereis php` ermittelt werden.

## Probleme beim erstellen und/oder einspielen von Datenbank-Dumps
Leider nutzt Shopware einige Datenbank-Funktionalitäten die zwischen `MySQL` und `MariaDB` nicht identisch sind
und daher bei Abweichungen zu Problemen führen können.

Der Datenbank-Dump sollte daher immer über das `mysqldump`-Tool der Datenbank erfolgen indie er eingespielt
werden soll.

Nachfolgend eine kurze Übersicht:
- Dump einer MySQL-DB erstellen und in eine MySQL-DB einspielen => `mysqldump`-Tool von MySQL verwenden.
- Dump einer MySQL-DB erstellen und in eine Maria-DB einspielen => `mysqldump`-Tool von MariaDB verwenden.
- Dump einer Maria-DB erstellen und in einer Maria-DB einspielen => `mysqldump`-Tool von MariaDB verwenden.
- Dump einer Maria-DB erstellen und in einer MySQL-DB einspielen => `mysqldump`-Tool von MySQL verwenden.

Hierfür kann z.B. das `mysqldump`-Tool aus der Lando-Instanz verwendet werden, sofern die Zieldatenbank von
außen erreichbar ist. Alternativ kann ein SSH-Tunnel zum Server aufgebaut werden um die Einschränkung zu umgehen.

Um weitere mögliche Probleme zu vermeiden, hat sich zudem folgender Befehl zur Erstellung des Dumps bewährt:

```console
mysqldump -h {host} --no-tablespaces --column-statistics=0 --quick -C --hex-blob --single-transaction -u {user} -p  $DATABASE | LANG=C LC_CTYPE=C LC_ALL=C sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' > {filename}.sql
```

**Erklärung:**  
- `--no-tablespaces` (Optional, je nach SQL-Version) Gibt es eine entsprechende Fehlermeldung, sollte die Option gesetzt werden.
- `--column-statistics=0` (Optional, je nach SQL-Version) Gibt es eine entsprechende Fehlermeldung, sollte die Option gesetzt werden.
- `--quick` Nützlich, um große Tabellen zu dumpen. Hierbei wird Zeile für Zeile ausgelesen und geschrieben, statt alles direkt abzurufen und im Speicher zu halten bis es geschrieben wurde.
- `-C` Komprimiert alle Daten, die zwischen Client und Server gesendet werden.
- `--hex-blob` Die Daten in binary Spalten werden in hexadezimal Notation geschrieben. (Beispiel: "abc" wird 0x616263). Betroffene Spaltentypen sind BINARY, VARBINARY, BLOB Typen und BIT.
- `--single-transaction` Alle Daten werden in einer Transaktion geschrieben, damit ein valider Zustand garantiert ist.
- `sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/'` Entfernt das DEFINER Statement. Sorgt dafür, dass beim Einspielen nicht der definer aus der Quell-DB genutzt wird, sondern der standard definer der Ziel-DB.
