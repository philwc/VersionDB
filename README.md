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


* Web Front End:

There is an example file ([a relative link](index.php)) for how to use the front end. The fields may be rendered by the class, using

```
$change = new \philwc\Web\AddChange();
$change->getHtml($action);
```

or by rendering manually, i.e.

```
$change = new \philwc\Web\AddChange();
$html   = '<form id="vdbAdd" method="POST">';
foreach ($change->getFields() as $field) {
    //Split the fields names into a nice title format
    $a          = preg_split('/(?<=[a-z])(?=[A-Z])/x', $field);
    $fieldTitle = ucwords(implode(' ', $a));

    $html .= '<label class="input-group" for="' . $field . '">' . $fieldTitle . ': <input class="visibleInput" type="text" name="' . $field . '" id="' . $field . '"/></label>';
}
$html .= '<input name="submit" type="submit"></form>';

echo $html;
```

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/philwc/versiondb/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

