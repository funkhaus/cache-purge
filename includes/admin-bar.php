<?php

/*
 * Add the purge button to the admin bar at top of WP dashboard
 */
function cp_toolbar_item($wp_admin_bar)
{
    $title = '
        <span class="msg"><span class="dashicons dashicons-admin-site"></span> Purge Cache</span>
    ';

    $has_configured_plugin = cp_has_configured_plugin();

    $args = [
        "id" => "cp-purge-button",
        "title" => $title,
        "href" =>
            admin_url() . "options-general.php?page=cache-purge",
        "meta" => [
            "class" => "cp-purge-button" . ($has_configured_plugin ? "" : " cp-status-no-configured-providers")
        ],
    ];

    $wp_admin_bar->add_node($args);
}
add_action("admin_bar_menu", "cp_toolbar_item", 999);