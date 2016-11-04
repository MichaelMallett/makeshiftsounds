
CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Usage
 * Requirements
 * Installation
 * Troubleshooting
 * FAQ
 * Maintainers


INTRODUCTION
------------

Drupal Module Upgrader is a script that scans the source of a Drupal 7 module,
flags any code that requires updating to Drupal 8, points off to any relevant
API change notices from https://www.drupal.org/list-changes/, and (where
possible) will actually attempt to *convert* the Drupal 7 code automatically to
the Drupal 8 version!

 * For a full description of the module, visit the project page:
   https://drupal.org/project/drupalmoduleupgrader
 * To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/drupalmoduleupgrader


USAGE
-----

1. Place the Drupal 7 module you wish to port into your Drupal 8 site's
   /modules directory.

2. To scan the code and get a report of code that needs updating and how, run
   the following inside the Drupal 8 root directory:

   drush dmu-analyze MODULE_NAME

   This will print a report showing any relevant change notices where you can
   read more.

3. To attempt to upgrade your Drupal 7 module's code to Drupal 8 automatically,
   run the following inside the Drupal 8 root directory:

   drush dmu-upgrade MODULE_NAME

   The script will output a few lines as it attempts various conversions. Go
   into your modules/MODULE_NAME directory and check out all of your new YAML
   files and such. ;)

4. To clear out D7 code that has been converted, run the clean command:

   drush dmu-clean MODULE_NAME

   This will do things like delete old .info files and such, so you're closer to
   your port being completed!

REQUIREMENTS
------------
This project requires the following dependencies:

 * Composer (https://getcomposer.org)
 * Drush 7+ (https://github.com/drush-ops/drush)
 * Pharborist (https://github.com/grom358/pharborist)
 * Symfony Yaml Component (https://github.com/symfony/Yaml)

Note that most dependencies are automatically downloaded by Composer during
installation.


INSTALLATION
------------

0. Download and install Composer:

   https://getcomposer.org/doc/00-intro.md#system-requirements

1. Download and install the latest version of Drush:

   https://github.com/drush-ops/drush#installupdate---composer

2. Download and install the latest Drupal 8:

   git clone --branch 8.0.x http://git.drupal.org/project/drupal.git 8.x

3. Download the latest release of drupalmoduleupgrader to your Drupal 8 site’s
   /modules directory:

   drush dl drupalmoduleupgrader

4. Run `composer install` from the drupalmoduleupgrader directory:

   cd drupalmoduleupgrader
   composer install

   You should see output as it downloads various dependencies (pharborist,
   phpcs, yaml...)

5. Finally, enable the module:

   drush en drupalmoduleupgrader -y


TROUBLESHOOTING
---------------
 * If you are getting any errors, check the following first:
   - Are you using the very latest Drupal 8 code? From the 8.x root directory,
     do:
       git pull --rebase
   - Are you using the very latest drupalmoduleupgrader code (and dependencies'
     code)? From the drupalmoduleupgrader root directory:
       git pull --rebase
       composer update
       drush pm-uninstall drupalmoduleupgrader -y
       drush en drupalmoduleupgrader


RUNNING TESTS
-------------

Drupal Module Upgrader uses Composer to install its dependencies inside the
module folder. Since it operates as a standalone project this is perfectly
fine. However if we want to run the tests we have to use a different strategy.
Drupal core itself also uses Composer, and it manages its dependencies and
namespaces inside the core/vendor/ folder. It is unaware of DMU's dependencies
and the tests will fail.

We can use Composer Manager [1] to generate a new composer.json file in the
root of the Drupal site. This will combine the dependencies of Drupal core and
all contributed and custom projects.


0. Navigate to the root folder of your Drupal installation:

   cd /path/to/drupal/site/

1. Download Composer Manager:

   drush dl composer_manager

2. Initialize Composer Manager:

   php modules/composer_manager/scripts/init.php

3. Install the combined dependencies of Drupal core and modules:

   composer drupal-install

4. Run the tests:

  ./vendor/bin/phpunit -c core --group=DMU


FAQ
---
Q: Wow, this thing is awesome! How does it work under the hood?
A: You're in luck! We have documentation describing DMU's overall architecture
   and how to contribute:
   https://www.drupal.org/documentation/modules/drupalmoduleupgrader/contributors

MAINTAINERS
-----------
Current maintainers:
 * Adam (phenaproxima) - https://www.drupal.org/u/phenaproxima
 * Angela Byron (webchick) - https://www.drupal.org/u/webchick

Past maintainers:
 * Gábor Hojtsy - https://www.drupal.org/u/gábor-hojtsy
 * Jakob Perry (japerry) - https://www.drupal.org/u/japerry
 * Jess (xjm) - https://www.drupal.org/u/xjm
 * Lisa Baker (eshta) - https://www.drupal.org/u/eshta
 * Wim Leers - https://www.drupal.org/u/wim-leers

Special thanks to:
 * Cameron Zemek (grom358) - https://www.drupal.org/u/grom358 for all the
   pharborist help!

This project has been sponsored by:
* Acquia
  Dream It. Drupal It. https://www.acquia.com

This project has been supported by:
* PreviousNext
  Australia’s premium Drupal website consulting, design and development firm.
  http://www.previousnext.com.au/


REFERENCES
----------
[1] Composer Manager: https://www.drupal.org/project/composer_manager
