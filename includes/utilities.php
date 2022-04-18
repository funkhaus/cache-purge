<?php
/*
 * This function renders the admin notices.
 * I use a transient here so we can use the native settings/options API posting the form to options.php
 */
function cp_render_admin_notices()
{
    $notices = get_transient("cp_admin_notices") ?? [];
    delete_transient("cp_admin_notices");

    // Abort if no errors.
    if (!is_array($notices)) {
        return;
    }

    // Show any errors we have
    foreach ($notices as $notice) {
        $type = $notice["type"] ?? "";
        $message = $notice["message"] ?? "";

        if ($type && $message): ?>	
		    <div class="notice notice-<?php echo $type; ?> is-dismissible">
		        <p><?php echo $message; ?></p>
		    </div>	
	    <?php endif;
    }
}
add_action("admin_notices", "cp_render_admin_notices");

/*
 * Helper function to set a transient for the admin notice
 */
function cp_set_notice($type, $message)
{
    $notices = get_transient("cp_admin_notices") ?? [];

    $new = [
        "type" => $type,
        "message" => $message,
    ];
    $notices[] = $new;

    set_transient("cp_admin_notices", $notices, 30);
}

function cp_has_configured_plugin() {
    $options = get_option("cp_settings");

    $cf_zone_id = $options["cloudflare_zone_id"] ?? "";
    $cf_token = $options["cloudflare_token"] ?? "";

    $graph_cdn_url = $options["graphcdn_admin_url"] ?? "";
    $graph_cdn_token = $options["graphcdn_token"] ?? "";

    $has_configured_cache_provider = false;

    if ( ( $cf_zone_id && $cf_token ) || ( $graph_cdn_url && $graph_cdn_token ) ) {
        $has_configured_cache_provider = true;
    }

    return $has_configured_cache_provider;
}
