SWAPP
========================
<a href="https://travis-ci.org/knutschsoft/swapp" target='_blank'>
  <img title="build status of swapp" src="https://travis-ci.org/knutschsoft/swapp.svg">
</a>
[![Stories in Ready](https://badge.waffle.io/knutschsoft/swapp.png?label=ready&title=Ready)](https://waffle.io/knutschsoft/swapp)
[![Dependency Status](https://www.versioneye.com/user/projects/55a4aaac3863340023000001/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55a4aaac3863340023000001)
[![Dependency Status](https://www.versioneye.com/user/projects/55a4aaed386334002000006d/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55a4aaed386334002000006d)
[![Dependency Status](https://www.versioneye.com/user/projects/55a4ae92386334001a00001d/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55a4ae92386334001a00001d)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/09942345-a55b-410a-ab4c-809f382688d5/big.png)](https://insight.sensiolabs.com/projects/09942345-a55b-410a-ab4c-809f382688d5)

## How to start dev action??

### Install Database:

On your MySQL console:

```
CREATE DATABASE swapp;
GRANT ALL ON swapp.* TO swapp@'localhost' IDENTIFIED BY 'pa$$word';
```

### Configure ACL

How to install and configure acl: http://wiki.ubuntuusers.de/ACL
Open a terminal and type in the following commands:

```
cd /your/symfony/dir
sudo setfacl -R -m u:www-data:rwX -m u:`whoami`:rwX var/cache var/logs web/images/way_points
sudo setfacl -dR -m u:www-data:rwX -m u:`whoami`:rwX var/cache var/logs web/images/way_points
```

### Setup webserver

#### create ```/etc/apache2/sites-available/swapp.conf``` as root with the following content.

```
<VirtualHost *:80>
    ServerName swapp
    ServerAlias swapp.localhost
 
    DocumentRoot /your/symfony/dir/web
 
    <Directory />
        DirectoryIndex app.php
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    <Directory "/your/symfony/dir/web">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>
 
    ErrorLog ${APACHE_LOG_DIR}/swapp_error.log
    CustomLog ${APACHE_LOG_DIR}/swapp_access.log combined
 
</VirtualHost>
```

#### run in terminal

```
sudo a2ensite swapp.conf
sudo service apache2 reload
```

#### add in ```/etc/hosts```

```
127.0.0.1 swapp.localhost swapp
```

### Install composer and composer packages

```
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```
### Set up parameters
You have to set database and mailer credentials in your
`app/config/parameters.yml`.

```
 parameters:
     database_host: 127.0.0.1
     database_name: swapp
     database_user: swapp
     database_password: pa$$word
     ...
     mailer_user: swapp_mail
     mailer_password: swapp_mail
```
Change the values whatever database name, username and password you've used while creating the database on your mySQL console.
FOSUserBundle requires the mailer parameters to also be set.

### Execute Migrations

```php bin/console doctrine:migrations:migrate --no-interaction```

### Load data fixtures

```php bin/console hautelook_alice:doctrine:fixtures:load```

### Start unit tests

```./bin/phpunit```

### Check config

Call in terminal:
```
php app/check.php
```

### Install assets

```
nvm install v7.1.0
nvm use v7.1.0

npm install
./node_modules/.bin/bower install
```

### run webpack dev server

This one launches the webpack dev server which serves js and css assets in dev environment.
It also reloads web page on asset change.

```
node_modules/.bin/webpack-dev-server


```

### want to create assets for non dev environment?

```
node_modules/.bin/webpack


```
### Open in browser:

```
http://swapp/config.php
```

### Entrance:

 http://swapp/eadmin

 http://swapp/walks

 http://swapp/app_dev.php/eadmin

 http://swapp/app_dev.php/walks
