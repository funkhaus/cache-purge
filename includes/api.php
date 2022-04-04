<?php

/**
 * Checks if the user is logged into WordPress and can purge caches. Used as permission_callback of WP JSON.
 *
 * @return Boolean|WP_Error true if valid token, otherwise error
 */
function cp_check_user_can_purge($request)
{
  return true;
}

/**
 * Purge configured caches
 */
function cp_purge_post()
{
    // Try a GraphCDN cache purge
    $response = cp_do_graphcdn_purge();

    if ($response != false) {
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
    
        // Handle success or error
        if (is_wp_error($response) || $code !== 200) {
            cp_set_notice("error", $body);
            return new WP_Error(
                "network_error",
                "Something went wrong trying to purge GraphCDN cache.",
                [
                    "status" => 500,
                ]
            );
        }
    
        cp_set_notice("success", "GraphCDN cache purged!");
    }


    // Try a Cloudflare cache purge
    $response = cp_do_cloudflare_purge();
    if ($response != false) {
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        // Handle success or error
        if (is_wp_error($response) || $code !== 200) {
            $body = json_decode($body);
            cp_set_notice(
                "error",
                $body->errors[0]->message ?? "Unknown Cloudflare error."
            );
            return new WP_Error(
                "network_error",
                "Something went wrong trying to purge Cloudflare cache.",
                [
                    "status" => 500,
                ]
            );
        }

        cp_set_notice("success", "Cloudflare cache purged!");
    }

    return new WP_REST_Response(null, 200);
}
