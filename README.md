##VersionDB

To Use:

1. Add to your composer.json:

     "require": {
       "philwc/VersionDB": "dev-master"
     }

2. Run 
    
    composer.phar update

3. Add a settings.yml file:

    database:
      user: <DBUSER>
      password: <DBPASS>
      host: <DBHOST>
      database: <DBNAME>
      changelogtable: changelog
    file:
      sqlDir: <LOCATION OF SQL FILES>

4. Add an entry point (i.e. console)

    #!/usr/bin/env php
    <?php
    require __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/vendor/philwc/VersionDB/console';
