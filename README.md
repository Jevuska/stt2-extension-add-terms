---
Contributors: Jevuska
Donate link: http://www.jevuska.com/donate/
Requires at least: 4.4
Tested up to: 4.4
License: GPLv2
---

## STT2 Extension Add Terms WordPress Plugin
Manage your terms better, add terms into single post manually, get terms via referrer, and save them as post meta. Search the terms that relevant of post content as well as WordPress search default algorithm.

#### Requirement
 * jQuery latest version
 * WordPress version 4.4
 * PHP Server version 7.0
 
#### Package
 * The excerpt preview of terms result is supported by Plugin Search Excerpt by Scott Yang
 * Autocomplete terms suggestion is supported by Google Suggest for jQuery plugin by Haochi Chen
 
## Frequently Asked Questions

#### Can I add bulk terms ?
Yes, you can. After you add one or more terms via input field, just add your terms list that separated by comma into textarea.

## Installation
1. Upload the entire `STT2 Extension Add Terms` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your settings and you are ready to go.

## Screenshot
1. STT2EXTAT General Settings
![screenshot 1](lib/assets/img/screenshot-1.jpg)

2. STT2EXTAT Manual Insert Tool and Term Stats
![screenshot 2](lib/assets/img/screenshot-2.jpg)

3. STT2EXTAT Widget
![screenshot 3](lib/assets/img/screenshot-3.jpg)

## Changelog
* 1.1.0 = December 15, 2015
 * Create admin plugin
 * Available to get terms via referrer
 * Insert terms as post meta
 * Shortcode and widget available
 * Sanitizing input and output of incoming terms
 * Add Search Excerpt plugin for search page snippet
 * Fix hook setup on activation an deactivation
 * Delete files stt2extat-x.x.x.php (x.x.x = version), and create stt2extat-1.1.0.php include in stable minor version 1.1
 * Add comments in each functions
 * Fix translation in Bahasa Indonesia
 * WordPress version 4.4
 * PHP Server version 7.0
 
* 1.0.4 = October 26 2015
 * Change short syntax for arrays at `stt2extat_insert_callback` to work under PHP 5.4
 
* 1.0.3 = October 25, 2015
 * Sanitize, escape, and validate POST, REQUEST calls
 * Remove old jquery UI, use jQuery UI WP Core
 * Update jquery-stt2extat.js
 * Remove unused files and fixes other functions for a security related bug

* 1.0.2 = October 16, 2015
 * Fix readability code
 * Fix undefined variable
 * Internationalize plugin
 * minify version jquery-stt2extat.min.js
  
* 1.0.1 = October 13, 2015
 * Remove session PHP
 * Add update plugin check
 * Add Screenshoot

* 1.0.0 = June 28, 2015
 * First official release!

## Upgrade Notice
###### v1.1.0
This new minor and patch version to fixes a security related bug. Upgrade immediately.

###### v1.0.3
This version fixes a security related bug.  Upgrade immediately.

###### v1.0.2 =
This version fixes a security related bug.  Upgrade immediately.

## Note
Search Excerpt plugin under package of this plugin, a setting is available to enable or disable it.