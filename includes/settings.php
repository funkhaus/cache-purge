<?php

/*
 * Register a new settings page under Settings sub-menu
 */
function cd_register_options_page()
{
    add_options_page(
        "Cache Purge",
        "Cache Purge",
        "manage_options",
        "cache-purge",
        "cp_options_page"
    );
}
add_action("admin_menu", "cd_register_options_page");

/*
 * Register the new settings fields on the new Admin page
 */
function cp_settings_init()
{
    register_setting("cp_plugin", "cp_settings");

    add_settings_section(
        "cp-plugin-graphcdn-section",
        "GraphCDN Settings",
        "cp_settings_section_render",
        "cp_plugin"
    );

    add_settings_field(
        "graphcdn_admin_url",
        "Admin URL",
        "cp_graphcdn_admin_url_render",
        "cp_plugin",
        "cp-plugin-graphcdn-section"
    );

    add_settings_field(
        "graphcdn_token",
        "Purging API Token",
        "cp_graphcdn_token_render",
        "cp_plugin",
        "cp-plugin-graphcdn-section"
    );

    add_settings_section(
        "cp-plugin-cloudflare-section",
        "Cloudflare Settings",
        "cp_settings_section_render",
        "cp_plugin"
    );

    add_settings_field(
        "cloudflare_zone_id",
        "Zone ID",
        "cp_cloudflare_zone_id_render",
        "cp_plugin",
        "cp-plugin-cloudflare-section"
    );

    add_settings_field(
        "cloudflare_token",
        "API Token",
        "cp_cloudflare_token_render",
        "cp_plugin",
        "cp-plugin-cloudflare-section"
    );
}
add_action("admin_init", "cp_settings_init");

/*
 * Settings title render function. Used for what comes after the new settings group title.
 */
function cp_settings_section_render()
{
    //echo 'Config this plugin below.';
}

/*
 * GraphCDN Admin URL render function
 */
function cp_graphcdn_admin_url_render()
{
    $options = get_option("cp_settings");
    $admin_url = $options["graphcdn_admin_url"] ?? "";
    ?>
            <input type='url' class="input input-graphcdn-admin-url regular-text code" name='cp_settings[graphcdn_admin_url]' value='<?php echo $admin_url; ?>' placeholder="https://admin.graphcdn.io/clientname">

            <p class="description">The GraphCDN admin API URL found in the <a href="https://graphcdn.io/" target="_blank">GraphCDN</a> dashboard, on the `Purging API Playground` settings page.</p>

        <?php
}

/*
 * GraphCDN API token render function
 */
function cp_graphcdn_token_render()
{
    $options = get_option("cp_settings");
    $token = $options["graphcdn_token"] ?? "";
    ?>
            <input type='password' class="input input-graphcdn-token regular-text code" name='cp_settings[graphcdn_token]' value='<?php echo $token; ?>' placeholder="ff4f38b00ab4bddd7be924fb9b109e39cb98483ced80252c899f8082d010b9fc">

            <p class="description">A GraphCDN Purging API Token. Found in the <a href="https://graphcdn.io/" target="_blank">GraphCDN</a> dashboard, on the `Settings > Purging API Tokens` settings page. It's recommended to name this token "WordPress".</p>

        <?php
}

/*
 * Cloudflare Zone ID render function
 */
function cp_cloudflare_zone_id_render()
{
    $options = get_option("cp_settings");
    $zone_id = $options["cloudflare_zone_id"] ?? "";
    ?>
            <input type='text' class="input input-cloudflare-zone-id regular-text code" name='cp_settings[cloudflare_zone_id]' value='<?php echo $zone_id; ?>' placeholder="023e105f4ecef8ad9ca31a8372d0c353">

            <p class="description">The "Zone ID" for your domain at <a href="https://cloudflare.com/" target="_blank">Cloudflare</a>. This can be found on the `Overview` page for the domain.</p>

        <?php
}

/*
 * Cloudflare API Token render function
 */
function cp_cloudflare_token_render()
{
    $options = get_option("cp_settings");
    $token = $options["cloudflare_token"] ?? "";
    ?>
            <input type='password' class="input input-cloudflare-token regular-text code" name='cp_settings[cloudflare_token]' value='<?php echo $token; ?>' placeholder="c2547eb745079dac9320b638f5e225cf483cc5cfdda41">

            <p class="description">An "API Token" for your domain at <a href="https://cloudflare.com/" target="_blank">Cloudflare</a> with the `cache_purge` permission. This can be found on the `My Profile > API Tokens` page for your user. It's recommended to create a custom token with `Zone => Cache Purge => Purge` settings. Call the token "WordPress cache purge plugin". You can leave all other settings as default.</p>

        <?php
}

/*
 * Render function for the new Admin page. This controls the output of everything on the page.
 */
function cp_options_page()
{
    ?>

        <div id="page-cp-options" class="wrap page-cp-options">
            <h1>Cache Purge</h1>

            <p>This plugin will purge the entire GraphCDN and Cloudflare cache's when any post types, menus or attachments change in WordPress.</p>

            <form action='options.php' method='post'>

                <?php
                // Required WP functions so form submits correctly.
                settings_fields("cp_plugin");
                do_settings_sections("cp_plugin");
                submit_button();?>

            </form>

        </div>

    <?php
}
