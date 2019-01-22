# vB4-CMS-Article-to-WP-Post-Converter
Import Scripts Converting VBCMS Articles to WordPress

Unfortunately there aren't any decent converters for migrating from vBulletin to Wordpress, particularly in versions past 4.x

Here's a Step by step:

Install bbPress & import using their vbulletin importer (note that they offer 2 different versions, ver 3.x & 4.x)

Download the Converter & converter_inc.php and place them in the root of your WP Installation.

Converter_inc.php:
Edit lines 4 thru 8 to reflect YOUR vBulletin Database Details
Edit line #9 to reflect the path to your vBulletin attachments storage location.  
Edit Lines #48 & 49 to reflect your own Domain Name to import your image attachments.

Once you've done that you can call the script https://Your-WP-Domain.com/converter.php  

If you've installed wordpress in a folder it would be www.examplename/foldernamehere/converter.php 

It is recommended to add the following 3 lines to your wp-config.php before importing:
Find: <?php
Add:
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

If you should encounter any errors or stalling you can access the debug log at www.example.com/wp-content/debug.log

Always double check your WP posts to ensure ALL data transferred.

Once you've transferred your CMS, you can use the following IN ORDER!

Import External Images 2 by VR51 https://github.com/VR51/import-external-images-2  //Activate & Run, then Deactivate
Import External Attachments by Ryan P.C. McQuen // Activate & Run, then Deactivate
Auto Upload Images by Ali Irani // Activate & Run, do NOT Deactivate
