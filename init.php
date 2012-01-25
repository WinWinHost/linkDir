<?php

/*
	Plugin Name: LinkDir
	Plugin URI: http://www.winwinhost.com/
	Description: With this plugin you can make a website directory where visitors can add new websites.You also have the posibility to add new categories from your Dashboard and new websites into categories from your website directory page.
Every 24 hours the plugin runs a formula to calculate the page rank of every website from all the categories.
	Version: 2.1
	Author: WinWinHost, Inc.
	Author URI: http://www.winwinhost.com/
	License: GPL2
*/

// Don't allow loading the page directly from plugin directory
if(end(explode("/",$_SERVER['SCRIPT_FILENAME']))=="init.php") die();

define("LINKDIR_PROCESSING",dirname(__FILE__)."/processing.php");
define("LINKDIR_ROOT",dirname(__FILE__)."/");
list($url) = explode("/",plugin_basename(__FILE__));
define("LINKDIR_URL","/wp-content/plugins/".$url."/");
define("LINKDIR_MOREINFO","http://www.winwinhost.com/page.php/opensource/link-directory/");

require_once(LINKDIR_PROCESSING);

register_activation_hook(LINKDIR_ROOT."init.php","linkdir_install");
register_deactivation_hook(LINKDIR_ROOT."init.php","linkdir_disable");

add_action("admin_menu","linkdir_menu");
add_action("init","linkdir_statuscheck");
add_action("wp_head","linkdir_style");
add_filter("the_content","check_tags",50);

