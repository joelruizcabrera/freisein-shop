# Passwortschutz

## Basic-Auth
Über die [.env](../.env), bzw. die `.env.local`, kann ein Basic-Auth Passwortschutz aktiviert werden.
Dafür können dort die drei folgenden Variablen hinterlegt werden:

- `BASIC_AUTH_PASSWORD` = Das Passwort das eingegeben werden muss
- `BASIC_AUTH_USER` = Der Benutzername der eingegebene werden muss
- `BASIC_AUTH_SCOPES` = Die Bereiche die geschützt werden sollen. Mögliche Werte sind: `storefront` und `administration`

Wenn `BASIC_AUTH_PASSWORD` und/oder `BASIC_AUTH_USER` nicht angegeben sind, ist der Passwortschutz
deaktiviert. Wenn `BASIC_AUTH_SCOPES` nicht angegeben ist, wird der Passwortschutz sowohl
für `storefront` als auch `administration` aktiviert.
