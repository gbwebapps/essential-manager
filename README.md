# essential/manager

Applicazione Essential indipendente. Configurazione iniziale e primo amministratore: [guida Framework](https://github.com/gbwebapps/essential-framework/blob/main/CREATE-APP.md).

1. Copiare `.env.example` in `.env` e completare URL, credenziali e nuova chiave.
2. Eseguire `composer install`: la prima esecuzione risolve le dipendenze e crea `composer.lock`, da versionare. Le successive riproducono il lock.
3. Creare il database vuoto `essential_manager` e importarvi `essential_manager.ddl`. Composer non esegue SQL.
4. Generare la chiave con `php spark key:generate`, creare il primo amministratore e avviare con `php spark serve`.

La document root e `public`. Non occorre npm: gli asset pronti arrivano dal package.

L'esempio usa HTTP locale con `cookie.secure = false`; in produzione HTTPS impostare `CI_ENVIRONMENT = production` e `cookie.secure = true`.
Versionare codice, moduli, schema, configurazioni di esempio e `composer.lock`. Il `.gitignore` dell'applicazione e distinto da quello di Framework e non esclude i test personalizzati.
Aggiornare il core con `composer update essential/framework --with-dependencies`. Conservare `writable/essential-package.json`; il publisher blocca le modifiche locali ai file comuni.
Personalizzazioni nei moduli o nelle estensioni previste, mai in `vendor`. Gli aggiornamenti SQL restano manuali.
Per ripristinare un sito servono anche database, immagini e `.env` con la chiave originale.
