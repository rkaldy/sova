# For English visitors

Sova is a web-based team tracking system for Czech outdoor puzzlehunt games. That's why the localization and documentation are in Czech only.

# Instalace

## Instalace knihoven

Instalace PHP knihoven:

`composer install`

Instalace JS knihoven. Jsou potřeba jen pro development mód (viz níže), jinak to můžete přeskočit.

`npm install`

## Konfigurace

Upravte `config.php.sample` a přejmenujte ho na `config.php`.

- `DEVELOPMENT` - pokud je `true`, pak web sovy vypisuje podrobnější chybové hlášky, používá také 
   lokální JS knihovny, takže ji můžete spustit na lokálu i offline
- `BASE_PATH` - HTTP cesta k sově, pokud není přístupná přímo z rootu
- `DB_XXX` - přístupové údaje k databázi, stačí práva pro zápis a čtení z tabulek
- `SQL_LOG` - pokud je `true`, pak na webu se vypisují všechny použité sql dotazy. Vypadá to 
   ošklivě, ale pro debugging se to občas hodí.

## Deploy

Nahrajte na hosting všechny adresáře kromě `lib` a `tests` (pokud na hostingu nepoužijete 
development mód, můžete přeskočit i `node_modules`) + soubory `index.php`, `config,php` a 
`.htaccess`.

Spusťte inicializaci databáze na `https://<soví-doména>/install`. Ve formuláři zadejte účet
k databázi, který má právo vytvářet databáze a tabulky, a superuživatelské heslo.

Přihlašte se do adminu `http://<soví-doména>/admin` jako `superuser`, použijte heslo
z předešlého formuláře. Jako superuser máte právo vytvářet nové hry a uživatele.

Každá hra má svůj uživatelský účet, login je název hry, heslo zadáte jako superuser v adminu.

Když se přihlásíte do adminu pod účtem konkrétní hry, můžete zadávat stanoviště, šifry, 
týmy, céčka a všechno potřebné pro danou hru.

Týmy se do sovy přihlašují přes `https://<soví-doména>`, login je číslo týmu, heslo se
vygeneruje automaticky, když v adminu přidáváte nový tým.

## Deploy na lokále

Sovu samozřejmě můžete spustit i na lokále, vyžaduje Apache s `mod_rewrite`, MySQL a 
PHP 7.x s `pdo_mysql` extension. 

Alternativou je spuštění sovy v dockeru:

`docker compose up .`

Dockerizovaná sova je pak dostupná na `https://localhost:8080`, phpadmin pak na 
`https://localhost:8090`.
