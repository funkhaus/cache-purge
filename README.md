# Cache Purge

This plugin will purge the entire cache at GraphCDN and/or Cloudflare on create/edit of posts, pages, menu items and attachments.

## Install

1.  Upload the files to `/wp-content/plugins/`
2.  Enter any settings on the plugins settings page. You don't need to set both Cloudflare and GraphCDN, just the one provider you need or both.

## Cookies

Getting cookies to work with GraphCDN is a little tricky. You'll want to make sure your PHP constant `COOKIE_DOMAIN` is set to a `.example.com`. A lot of WordPress hosts (like Flywheel) prevent you from setting `COOKIE_DOMAIN` on your own though. [See here for our solution](https://github.com/funkhaus/fuxt-backend/issues/23). 
