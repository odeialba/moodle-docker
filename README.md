# moodle-docker: Docker Containers for Moodle Developers

This repository contains Docker configuration aimed at Moodle developers and testers to easily deploy a testing environment for Moodle.

This repository more specifically is adapted to run multimple instances of Moodle. Each of them will be placed in a directory under the main `MOODLE_DOCKER_WWWROOT` directory, named with the subdomain name that later will be used to access it.

## Features:

* All supported database servers (PostgreSQL, MySQL, Micosoft SQL Server, Oracle XE)
* Behat/Selenium configuration for Firefox and Chrome
* Catch-all smtp server and web interface to messages using [MailHog](https://github.com/mailhog/MailHog/)
* All PHP Extensions enabled configured for external services (e.g. solr, ldap)
* All supported PHP versions
* Zero-configuration approach
* Backed by [automated tests](https://travis-ci.com/moodlehq/moodle-docker/branches)

## Prerequisites

* [Docker](https://docs.docker.com) and [Docker Compose](https://docs.docker.com/compose/) installed
* 3.25GB of RAM (if you choose [Microsoft SQL Server](https://docs.microsoft.com/en-us/sql/linux/sql-server-linux-setup#prerequisites) as db server)

## Quick start

By default, for the first Moodle instance `lms` directory and subdomain will be used, as well as `moodle.local` as the main domain in `MOODLE_DOCKER_WEB_HOST` enviromnent variable.

```bash
# Set up path to Moodle code
export MOODLE_DOCKER_WWWROOT=/path/to/moodle/code
# Choose a db server (Currently supported: pgsql, mariadb, mysql, mssql, oracle)
export MOODLE_DOCKER_DB=pgsql
# Set up the main domain for the local Moodle instances
export MOODLE_DOCKER_WEB_HOST=moodle.local

# Ensure customized config.php for the Docker containers is in place
cp config.docker-template.php $MOODLE_DOCKER_WWWROOT/lms/config.php

# [..] IMPORTANT: For Mac users see next point

# Start up containers
bin/moodle-docker-compose up -d

# Wait for DB to come up (important for oracle/mssql)
bin/moodle-docker-wait-for-db

# Install DB for the lms instance
bin/mbash minstall moodle -d lms
# Note: See "Custom commands" section for more info

# Work with the containers (see below)
# [..]

# Shut down and destroy containers
bin/moodle-docker-compose down
```

### Mac users
Docker is known to be really slow for Mac users. Run the next commands to fix that using NFS.

```bash
# [..] Follow the "Quick start" step until the "IMPORTANT" note. Then continue with this commands
# This script will set up a NFS link between your $MOODLE_DOCKER_WWWROOT and the containers
# We change the permissions of the script to be able to execute it
chmod +x scripts/nfs-script.sh
# Now we run the script
./scripts/nfs-script.sh
# [..] Continue with the "Quick start" from after the "IMPORTANT" note
```

## Stop and restart containers

`bin/moodle-docker-compose down` which was used above after using the containers stops and destroys the containers. If you want to use your containers continuously for manual testing or development without starting them up from scratch everytime you use them, you can also just stop without destroying them. With this approach, you can restart your containers sometime later, they will keep their data and won't be destroyed completely until you run `bin/moodle-docker-compose down`.

```bash
# Stop containers
bin/moodle-docker-compose stop

# Restart containers
bin/moodle-docker-compose start
```

## Custom commands

### moodle-docker-bash
This script was created to easily run any command inside any container. First parameter will be the container name and second one will be the command. Example:
```bash
~$ bin/moodle-docker-bash webserver php -v
PHP 7.4.23 (cli) (built: Sep  3 2021 18:14:02) ( NTS )
```
```bash
~$ bin/moodle-docker-bash db psql --version
psql (PostgreSQL) 11.13 (Debian 11.13-1.pgdg90+1)
```

### mbash
As most of the commands using the `moodle-docker-bash` script will be run on the `webserver` container, this is a shortcut of that script that runs the commands only in the `webserver` container. Example:
```bash
~$ bin/mbash php -v
PHP 7.4.23 (cli) (built: Sep  3 2021 18:14:02) ( NTS )
```

### minstall
This script was created to be automatically installed in the webserver container and to easily run any install command. First parameter will be the database to install (moodle, phpunit or behat), the second one (`-d`) the subdirectory of the Moodle instance and the rest will be all the parameters that want to be used to override the default one. Note that this script needs to be run either withing the container shell or using `moodle-docker-bash`. Examples:
```bash
~$ bin/mbash minstall moodle -d lms --fullname="Moodle first instance" --adminpass="admin"
-------------------------------------------------------------------------------
== Setting up database ==
-->System
```
```bash
~$ bin/mbash minstall phpunit -d lms
Initialising Moodle PHPUnit test environment...
```
```bash
~$ bin/mbash minstall behat -d lms
You are already using the latest available Composer version 2.1.8 (stable channel).
Installing dependencies from lock file (including require-dev)
```

### mtest
This script was created to be automatically installed in the webserver container and to easily run any test command. First parameter will be the tests to be run (phpunit or behat), the second one (`-d`) the subdirectory of the Moodle instance and the rest will be all the parameters that want to be used to override the default ones. Note that this script needs to be run either withing the container shell or using `moodle-docker-bash`. Examples:
```bash
~$ bin/mbash mtest phpunit -d lms --filter auth_manual_testcase
Moodle 3.11.3 (Build: 20210913), 8c02bd32af238dfc83727fb4260b9caf1b622fdb
Php: 7.4.23, pgsql: 11.13 (Debian 11.13-1.pgdg90+1), OS: Linux 5.10.47-linuxkit x86_64
```
```bash
~$ bin/mbash mtest behat -d lms --tags=@auth_manual
Running single behat site:
```

### mutil
This script was created to be automatically installed in the webserver container and to easily access the `util.php` files of phpunit and behat. First parameter will be the test environment (phpunit or behat), the second one (`-d`) the subdirectory of the Moodle instance and the rest will be all the parameters that want to be used to override the default ones. Note that this script needs to be run either withing the container shell or using `moodle-docker-bash`. Examples:
```bash
~$ bin/mbash mutil phpunit -d lms --drop
Purging dataroot:
Dropping tables:
```
```bash
~$ bin/mbash mutil behat -d lms --drop
Dropping tables:
```

## Use containers for running behat tests

```bash
# Initialize behat environment
~$ bin/mbash minstall behat -d lms
# Note: See "Custom commands" section for more info
# [..]

# Run behat tests
~$ bin/mbash mtest behat -d lms --tags=@auth_manual
# Note: See "Custom commands" section for more info
Running single behat site:
Moodle 3.4dev (Build: 20171006), 33a3ec7c9378e64c6f15c688a3c68a39114aa29d
Php: 7.1.9, pgsql: 9.6.5, OS: Linux 4.9.49-moby x86_64
Server OS "Linux", Browser: "firefox"
Started at 25-05-2017, 19:04
...............

2 scenarios (2 passed)
15 steps (15 passed)
1m35.32s (41.60Mb)
```

Notes:
* The behat faildump directory is exposed at http://moodle.local/_/faildumps/.

## Use containers for running phpunit tests

```bash
# Initialize phpunit environment
~$ bin/mbash minstall phpunit -d lms
# Note: See "Custom commands" section for more info
# [..]

# Run phpunit tests
~$ bin/mbash mtest phpunit -d lms auth_manual_testcase auth/manual/tests/manual_test.php
# Note: See "Custom commands" section for more info
Moodle 3.4dev (Build: 20171006), 33a3ec7c9378e64c6f15c688a3c68a39114aa29d
Php: 7.1.9, pgsql: 9.6.5, OS: Linux 4.9.49-moby x86_64
PHPUnit 5.5.7 by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: 4.45 seconds, Memory: 38.00MB

OK (2 tests, 7 assertions)
```
Notes:
* If you want to run test with coverage report, use command: `bin/moodle-docker-compose exec webserver phpdbg -qrr vendor/bin/phpunit --coverage-text auth_manual_testcase auth/manual/tests/manual_test.php`

## Use containers for manual testing

```bash
# Initialize Moodle database for manual testing
bin/mbash minstall moodle -d lms
```

Notes:
* This will automatically create a database with default values. If you wish to change those values, add any of these (or others) at the end: `--fullname="Docker moodle"`, `--shortname="docker_moodle"`, `--summary="Docker moodle site"`, `--adminemail="admin@example.com"`
* Moodle is configured to listen on `http://localhost:8000/`.
* Mailhog is listening on `http://localhost:8000/_/mail` to view emails which Moodle has sent out.
* The admin `username` you need to use for logging in is `admin` by default. You can customize it by passing `--adminuser='myusername'`
* The admin `password` you need to use for logging in is `admin` by default. You can customize it by passing `--adminpass='myusername'`

## Using VNC to view behat tests

If `MOODLE_DOCKER_SELENIUM_VNC_PORT` is defined, selenium will expose a VNC session on the port specified so behat tests can be viewed in progress.

For example, if you set `MOODLE_DOCKER_SELENIUM_VNC_PORT` to 5900..
1. Download a VNC client: https://www.realvnc.com/en/connect/download/viewer/
2. With the containers running, enter 0.0.0.0:5900 as the port in VNC Viewer. You will be prompted for a password. The password is 'secret'.
3. You should be able to see an empty Desktop. When you run any Behat tests a browser will popup and you will see the tests execute.

## Using XDebug for live debugging

The XDebug PHP Extension is included and running in this setup by default.

If you want to disable and re-enable XDebug during the lifetime of the webserver container, you can achieve this with these additional commands:

```bash
# Make the helpful script executable (Note: Run only once)
chmod +x bin/xdebug

# Disable XDebug extension in Apache and restart the webserver container
bin/xdebug webserver disable

# Enable XDebug extension in Apache and restart the webserver container
bin/xdebug webserver enable
```

Please take special care of the value of `xdebug.client_host` in `/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini` in the webserver container (`assets/web/php/xdebug.ini` in this repository), which is needed to connect from the container to the host. The given value `host.docker.internal` is a special DNS name for this purpose within Docker for Windows and Docker for Mac. If you are running on another Docker environment, you might want to try the value `localhost` instead or even set the hostname/IP of the host directly.

## Adminer

This repository will install adminer database administrator by default. To access it, simply go to http://adminer.moodle.local/ and fill in the database credentials.

## Create new Moodle instances

It might look complicated, but if you follow the steps, it is quite simple.

### Base config
To create more Moodle instances, first you need to decide the name of the directory and subdomain that instance will be placed in (in this example `wp` will be used, as it is already implemented). Create a new directory under `MOODLE_DOCKER_WWWROOT` and call it `wp`. Create the config file for that new instance.
```bash
cp config.docker-template.php $MOODLE_DOCKER_WWWROOT/wp/config.php
```

Change the value of the `$type` variable to `wp`.

By now the new instance will be accessible from http://moodle.local/wp, but as the necessary `dataroot` directories are not created yet, we will get an error.

Create the directories copying the one from the main instance and change the owner:
```bash
bin/mbash 'cp -r /var/www/lms /var/www/wp && chown -R www-data /var/www/wp'
```

To have it automatically done next time you build the container, add the next line to the `dockerfiler/Dockerfilewebserver` file:
```dockerfile
RUN cp -r /var/www/lms /var/www/wp && chown -R www-data /var/www/wp
```

By now the new instance will be accessible from http://moodle.local/wp.

### Database [Optional]
The new Moodle instance will be using the same database as the original instance with a different database prefix, so everything will work fine. But if you want to create a new database for the new instance, continue reading.

To create the new database automatically next time you build the container, add the new database name to the `POSTGRES_MULTIPLE_DATABASES` (comma separated values) environment variable of the `db` container in `base.yml` file. Then, add a new environment variable to the `webserver` container and call it `MOODLE_DOCKER_DBNAME_WP` and give it the value of the name of the new database.

### Subdomain [Optional]
If you want to access the new Moodle instance via a subdomain, copy the `assets/web/apache/lms.moodle.local.conf` file and create the `assets/web/apache/wp.moodle.local.conf` file, replacing all the `lms` occurrences with `wp` inside it.

Then, add the next two lines to `dockerfiler/Dockerfilewebserver` file:
```dockerfile
COPY assets/web/apache/wp.moodle.local.conf /etc/apache2/sites-available/wp.moodle.local.conf
RUN a2ensite wp.moodle.local
```

Now, if you destroy all your containers and it's images, and run `bin/moodle-docker-compose up -d`, all the new containers will be created correctly. And if you access http://wp.moodle.local/, you should see your new Moodle instance (run `bin/mbash minstall moodle -d wp` to easily install the database).

## Change PHP version

By default this containers will run with PHP 7.3. To change the PHP version, change the environment variable `MOODLE_DOCKER_PHP_VERSION` to the desired version and run compose up again. It might fail the first try because the container with the `webserver` name already exists, but run compose up again and it will work.

Commands:
```bash
~$ export MOODLE_DOCKER_PHP_VERSION=7.4
~$ bin/moodle-docker-compose up -d
```

Common error:
```bash
~$ bin/moodle-docker-compose up -d
[+] Running 5/5
 ⠿ Container db                     Started                    2.3s
 ⠿ Container selenium               Started                    7.2s
 ⠿ Container mailhog                Started                    2.1s
 ⠿ Container exttests               Started                    4.2s
 ⠿ Container webserver              Started                    5.6s
~$ export MOODLE_DOCKER_PHP_VERSION=7.4
~$ bin/moodle-docker-compose up -d
[+] Running 5/5
 ⠿ Container selenium               Running                    0.0s
 ⠿ Container db                     Running                    0.0s
 ⠿ Container mailhog                Running                    0.0s
 ⠿ Container exttests               Running                    0.0s
 ⠿ Container webserver              Recreated                  5.8s
Error response from daemon: Renaming a container with the same name as its current name
~$ bin/moodle-docker-compose up -d
[+] Running 5/5
 ⠿ Container selenium               Running                    0.0s
 ⠿ Container db                     Running                    0.0s
 ⠿ Container mailhog                Running                    0.0s
 ⠿ Container exttests               Running                    0.0s
 ⠿ Container webserver              Started                    0.8s
```

## Use containers for running behat tests for the Moodle App

In order to run Behat tests for the Moodle App, you need to install the [local_moodlemobileapp](https://github.com/moodlehq/moodle-local_moodlemobileapp) plugin in your Moodle site. Everything else should be the same as running standard Behat tests for Moodle. Make sure to filter tests using the `@app` tag.

The Behat tests will be run against a container serving the mobile application, you have two options here:

1. Use a Docker image that includes the application code. You need to specify the `MOODLE_DOCKER_APP_VERSION` env variable and the [moodlehq/moodleapp](https://hub.docker.com/r/moodlehq/moodleapp) image will be downloaded from Docker Hub. You can read about the available images in [Moodle App Docker Images](https://docs.moodle.org/dev/Moodle_App_Docker_Images) (for Behat, you'll want to run the ones with the `-test` suffix).

2. Use a local copy of the application code and serve it through Docker, similar to how the Moodle site is being served. Set the `MOODLE_DOCKER_APP_PATH` env variable to the codebase in you file system. This will assume that you've already initialized the app calling `npm install` and `npm run setup` locally.

For both options, you also need to set `MOODLE_DOCKER_BROWSER` to "chrome".

```bash
# Install local_moodlemobileapp plugin
~$ git clone git://github.com/moodlehq/moodle-local_moodlemobileapp "$MOODLE_DOCKER_WWWROOT/local/moodlemobileapp"

# Initialize behat environment
~$ bin/moodle-docker-compose exec webserver php admin/tool/behat/cli/init.php
# (you should see "Configured app tests for version X.X.X" here)

# Run behat tests
~$ bin/moodle-docker-compose exec -u www-data webserver php admin/tool/behat/cli/run.php --tags="@app&&@mod_login"
Running single behat site:
Moodle 4.0dev (Build: 20200615), a2b286ce176fbe361f0889abc8f30f043cd664ae
Php: 7.2.30, pgsql: 11.8 (Debian 11.8-1.pgdg90+1), OS: Linux 5.3.0-61-generic x86_64
Server OS "Linux", Browser: "chrome"
Browser specific fixes have been applied. See http://docs.moodle.org/dev/Acceptance_testing#Browser_specific_fixes
Started at 13-07-2020, 18:34
.....................................................................

4 scenarios (4 passed)
69 steps (69 passed)
3m3.17s (55.02Mb)
```

If you are going with the second option, this *can* be used for local development of the Moodle App, given that the `moodleapp` container serves the app on the local 8100 port. However, this is intended to run Behat tests that require interacting with a local Moodle environment. Normal development should be easier calling `npm start` in the host system.

By all means, if you don't want to have npm installed locally you can go full Docker executing the following commands before starting the containers:

```
docker run --volume $MOODLE_DOCKER_APP_PATH:/app --workdir /app node:14 npm install
docker run --volume $MOODLE_DOCKER_APP_PATH:/app --workdir /app node:14 npm run setup
```

You can learn more about writing tests for the app in [Acceptance testing for the Moodle App](https://docs.moodle.org/dev/Acceptance_testing_for_the_Moodle_App).

## Environment variables

You can change the configuration of the docker images by setting various environment variables before calling `bin/moodle-docker-compose up`.

| Environment Variable                      | Mandatory | Allowed values                        | Default value | Notes                                                                        |
|-------------------------------------------|-----------|---------------------------------------|---------------|------------------------------------------------------------------------------|
| `MOODLE_DOCKER_DB`                        | yes       | pgsql, mariadb, mysql, mssql, oracle  | none          | The database server to run against                                           |
| `MOODLE_DOCKER_WWWROOT`                   | yes       | path on your file system              | none          | The path to the Moodle codebase you intend to test                           |
| `MOODLE_DOCKER_PHP_VERSION`               | no        | 7.4, 7.3, 7.2, 7.1, 7.0, 5.6          | 7.3           | The php version to use                                                       |
| `MOODLE_DOCKER_BROWSER`                   | no        | firefox, chrome                       | firefox       | The browser to run Behat against                                             |
| `MOODLE_DOCKER_PHPUNIT_EXTERNAL_SERVICES` | no        | any value                             | not set       | If set, dependencies for memcached, redis, solr, and openldap are added      |
| `MOODLE_DOCKER_WEB_HOST`                  | no        | any valid hostname                    | localhost     | The hostname for web                                |
| `MOODLE_DOCKER_WEB_PORT`                  | no        | any integer value (or bind_ip:integer)| 127.0.0.1:8000| The port number for web. If set to 0, no port is used.<br/>If you want to bind to any host IP different from the default 127.0.0.1, you can specify it with the bind_ip:port format (0.0.0.0 means bind to all) |
| `MOODLE_DOCKER_SELENIUM_VNC_PORT`         | no        | any integer value (or bind_ip:integer)| not set       | If set, the selenium node will expose a vnc session on the port specified. Similar to MOODLE_DOCKER_WEB_PORT, you can optionally define the host IP to bind to. If you just set the port, VNC binds to 127.0.0.1 |
| `MOODLE_DOCKER_APP_PATH`                  | no        | path on your file system              | not set       | If set and the chrome browser is selected, it will start an instance of the Moodle app from your local codebase |
| `MOODLE_DOCKER_APP_VERSION`               | no        | a valid [app docker image version](https://docs.moodle.org/dev/Moodle_App_Docker_images) | not set       | If set will start an instance of the Moodle app if the chrome browser is selected |
| `MOODLE_DOCKER_APP_RUNTIME`               | no        | 'ionic3' or 'ionic5'                  | not set       | Set this to indicate the runtime being used in the Moodle app. In most cases, this can be ignored because the runtime is guessed automatically (except on Windows using the `.cmd` binary). In case you need to set it manually and you're not sure which one it is, versions 3.9.5 and later should be using Ionic 5. |

## Advanced usage

As can be seen in [bin/moodle-docker-compose](https://github.com/moodlehq/moodle-docker/blob/master/bin/moodle-docker-compose),
this repo is just a series of docker-compose configurations and light wrapper which make use of companion docker images. Each part
is designed to be reusable and you are encouraged to use the docker[-compose] commands as needed.

## Companion docker images

The following Moodle customised docker images are close companions of this project:

* [moodle-php-apache](https://github.com/moodlehq/moodle-php-apache): Apache/PHP Environment preconfigured for all Moodle environments
* [moodle-db-mssql](https://github.com/moodlehq/moodle-db-mssql): Microsoft SQL Server for Linux configured for Moodle
* [moodle-db-oracle](https://github.com/moodlehq/moodle-db-oracle): Oracle XE configured for Moodle

## Contributions

Are extremely welcome!
