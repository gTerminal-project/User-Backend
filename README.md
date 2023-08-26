# User-Backend

### This repository contains the source code of the user backend, which is needed to provide the login feature and to store information about installed repos and modules

#### Setup
1. You need a MySQL database which is named `gTerminal` (the tables will be automatically generated)
2. You need a webserver with PHP intalled
3. Then put all the files (except the `README.md` and `LICENCE`) in your webroot
4. Edit the `$GLOBALS['']` variables in `api.php` such as `$GLOBALS['host']` (the domain where the api is hosted), `$GLOBALS['mySQLHost']`, `$GLOBALS['mySQLUsername']` and `$GLOBALS['mySQLPassword']`.
5. If you want you can also edit the username and password regex defaults.

#### API documentation
The API documentation is available at `/swagger-ui/`
