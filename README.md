#VersionDB

##To Install:

* Add to your composer.json:

```
"require": {
     "philwc/version-db": "dev-master"
}
```

* Run 
    

```
composer.phar update
```

* Add a settings.yml file:

```
parameters:
    database:
      user: <DBUSER>
      password: <DBPASS>
      host: <DBHOST>
      name: <DBNAME>
      changelogtable: changelog
    file:
      sqlDir: <LOCATION OF SQL FILES>
```

* Create a new file in the project root called console, with the following contents: 

```php
#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/philwc/version-db/console';
```

##To Use:

* Add A Revision:

```
php console add
```

You will be prompted to fill in the required fields

* Upgrade Database:

```
php console upgrade
```

This will read the SQL Dir (From settings.yml) and apply the update SQL scripts in date order

* Downgrade Database:

```
php console downgrade
```

This will read the changelog table and allow you to select where to downgrade to. 
It will then apply the downgrade SQL scripts in date descending order until it hits the record specified.


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/philwc/versiondb/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

