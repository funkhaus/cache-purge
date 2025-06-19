<?php

/*
 * Run these functions on update of the settings page
 */
function cp_options_updated($old_value, $new_value, $option_name)
{
    // Try a GrpahCDN cache purge
    $response = cp_do_graphcdn_purge();
    $body = wp_remote_retrieve_body($response);
    $code = wp_remote_retrieve_response_code($response);

    // Handle success or error
    if (is_wp_error($response) || $code !== 200) {
        cp_set_notice("error", $body);
    } else {
        cp_set_notice("success", "GraphCDN cache purged!");
    }

    // Try a Cloudflare cache purge
    $response = cp_do_cloudflare_purge();
    $body = wp_remote_retrieve_body($response);
    $code = wp_remote_retrieve_response_code($response);

    // Handle success or error
    if (is_wp_error($response) || $code !== 200) {
        $body = json_decode($body);
        cp_set_notice(
            "error",
            $body->errors[0]->message ?? "Unknown Cloudflare error."
        );
    } else {
        cp_set_notice("success", "Cloudflare cache purged!");
    }
}
add_action("update_option_cp_settings", "cp_options_updated", 10, 3);

function cp_run_purge_cache() {
    cp_do_graphcdn_purge(false);
    cp_do_cloudflare_purge(false);
}

/*
 * Run this on edit of a post/page/menuItem/attachment.
 * This is slient, so it displays no errors.
 */
function cp_purge_cache($object_id)
{
    // Abort if doing an auto save of a revision
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }

    // Abort if NOT publishing something
    if (get_post_status($object_id) !== "publish") {
        return;
    }

    if ( wp_next_scheduled( 'cp_run_purge_cache' ) ) {
        error_log( print_r( 'Already scheduled', true ) );
        return;
    }

    wp_schedule_single_event(time() + 1, 'cp_run_purge_cache');
}
add_action( 'cp_run_purge_cache', 'cp_run_purge_cache' );
add_action("wp_update_nav_menu", "cp_purge_cache", 20, 1);
add_action("save_post", "cp_purge_cache", 20, 1);
add_action("attachment_updated", "cp_purge_cache", 20, 1);
add_action("delete_post", "cp_purge_cache", 20, 1);

/*
 * Purge everything on ACF site options save
 */
function cp_acf_saved() {
	$screen = get_current_screen();
	if (strpos($screen->id, "site-options") == true) {
	    // Try a GrpahCDN cache purge
	    $graphcdn_response = cp_do_graphcdn_purge(false);

	    // Try a Cloudflare cache purge
	    $cloudflare_response = cp_do_cloudflare_purge(false);
	}
}
add_action('acf/save_post', 'cp_acf_saved', 20);

/*
 * This function hits the GraphCDN API and attempts to purge the cache
 */
function cp_do_graphcdn_purge($blocking = true)
{
    $options = get_option("cp_settings");
    $url = $options["graphcdn_admin_url"] ?? "";
    $token = $options["graphcdn_token"] ?? "";

    // Skip if missing required settings
    if (!$url || !$token) {
        return;
    }

    // Make the GQL query to GraphCDN
    $gql = json_encode([
        "query" => "mutation purgeAll {_purgeAll(soft: true)}",
    ]);
    $args = [
        "method" => "POST",
        "timeout" => 45,
        "redirection" => 5,
        "httpversion" => "1.0",
        "blocking" => $blocking,
        "headers" => [
            "Content-Type" => "application/json",
            "graphcdn-token" => $token,
        ],
        "body" => $gql,
    ];
    return wp_remote_post($url, $args);
}

/*
 * This function hits the GraphCDN API and attempts to purge the cache
 */
function cp_do_cloudflare_purge($blocking = true)
{
    $options = get_option("cp_settings");
    $zone_id = $options["cloudflare_zone_id"] ?? "";
    $token = $options["cloudflare_token"] ?? "";

    // Skip if missing required settings
    if (!$zone_id || !$token) {
        return;
    }

    // Make the GQL query to GraphCDN
    $url = "https://api.cloudflare.com/client/v4/zones/{$zone_id}/purge_cache";
    $args = [
        "method" => "POST",
        "timeout" => 45,
        "redirection" => 5,
        "httpversion" => "1.0",
        "blocking" => $blocking,
        "headers" => [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer {$token}",
        ],
        "body" => json_encode([
            "purge_everything" => true,
        ]),
    ];
    return wp_remote_post($url, $args);
}
