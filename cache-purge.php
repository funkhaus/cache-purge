<?php
/*
Plugin Name: Cache Purge
Description: Trigger a cahce purge of Cloudflare and/or GraphCDN on content change
Version: 1.0.0
Author: Funkhaus
Plugin URI:  https://github.com/funkhaus/cache-purge
Author URI:  http://funkhaus.us
*/

/*
 * Import required files
 */
include_once plugin_dir_path(__FILE__) . "includes/utilities.php";
include_once plugin_dir_path(__FILE__) . "includes/purger.php";
include_once plugin_dir_path(__FILE__) . "includes/settings.php";

/*
 * Plugin activated, setup default options
 */
function cp_plugin_activated()
{
    $defaults = [
        "graphcdn_admin_url" => "",
        "graphcdn_token" => "",
        "cloudflare_zone_id" => "",
        "cloudflare_token" => "",
    ];
    add_option("cp_settings", $defaults);
}
register_activation_hook(__FILE__, "cp_plugin_activated");
