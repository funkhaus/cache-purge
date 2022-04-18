<?php
/*
Plugin Name: Cache Purge
Description: Trigger a cache purge of Cloudflare and/or GraphCDN on content change
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
include_once plugin_dir_path(__FILE__) . "includes/admin-bar.php";
include_once plugin_dir_path(__FILE__) . "includes/api.php";

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

function cp_admin_scripts()
{
    wp_enqueue_script("jquery");
    wp_enqueue_script(
        "cp_main",
        plugins_url("js/main.js", __FILE__),
        null,
        $plugin_version
    );
    wp_enqueue_style(
        "cp_main",
        plugins_url("css/main.css", __FILE__),
        null,
        $plugin_version
    );
    wp_enqueue_style("dashicons");
    $js_vars = [
        "hasConfiguredCacheProvider" => cp_has_configured_plugin(),
        "nonce" => wp_create_nonce("wp_rest"),
        "apiUrl" => site_url("/wp-json/cp")
    ];
    wp_add_inline_script(
        "cp_main",
        "var cp_vars = " . wp_json_encode($js_vars),
        "before"
    );
}
add_action("admin_enqueue_scripts", "cp_admin_scripts");

function cp_add_api_routes()
{
    // Use this to trigger a cache purge
    register_rest_route("cp", "/purge", [
        [
            "methods" => "POST",
            "callback" => "cp_purge_post",
            "permission_callback" => "cp_check_user_can_purge",
        ],
    ]);
}
add_action("rest_api_init", "cp_add_api_routes");
