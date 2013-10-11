#VersionDB

##To Use:

* Add to your composer.json:

```
"require": {
     "philwc/VersionDB": "dev-master"
}
```

* Run 
    

```
composer.phar update
```

* Add a settings.yml file:

```
database:
  user: <DBUSER>
  password: <DBPASS>
  host: <DBHOST>
  database: <DBNAME>
  changelogtable: changelog
file:
  sqlDir: <LOCATION OF SQL FILES>
```

* Add an entry point (i.e. console)

```php
#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/philwc/VersionDB/console';
```
