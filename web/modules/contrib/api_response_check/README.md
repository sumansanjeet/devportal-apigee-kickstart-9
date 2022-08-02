CONTENTS OF THIS FILE
---------------------

 * INTRODUCTION
 * REQUIREMENTS
 * INSTALLATION
 * CONFIGURATION


INTRODUCTION
------------

This module helps to check the API Response status.
By default, API response status is updated for every cron run.
Will get instant API response status by triggering the button
"Get Instant Response" in the View result page. If the 
reponse status is 200 then it will be considered as Succe

 * For a full description of the module visit:
   https://www.drupal.org/project/api_response_check

 * To submit bug reports and feature suggestions, or to track changes visit:
   https://www.drupal.org/project/issues/api_response_check


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

 * Install the module as you would normally install a contributed
   Drupal module. Visit https://www.drupal.org/node/1897420 for further
   information.
 * Recommended: Install with Composer: 
   composer require 'drupal/api_response_check'

CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate to Administration > Configuration > System > API Response
       check configuration
    3. Configure the API URL's in the API Input Configuration Form section.
    4. Save configuration.
