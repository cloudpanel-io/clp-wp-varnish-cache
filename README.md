# CLP Varnish Cache (Fork)

This is a fork of [cloudpanel-io/clp-wp-varnish-cache](https://github.com/cloudpanel-io/clp-wp-varnish-cache) — the **CLP Varnish Cache Plugin** for **WordPress**, which lets you manage cache settings and perform purge operations.

<p align="center">
  <a href="https://www.cloudpanel.io/docs/v2/frontend-area/varnish-cache/wordpress/plugin/" target="_blank">
    <img src="/release/plugin.png?v=0.0.1">
  </a>
</p>

## Changes in this fork

### 1.1.0

- **Auto-purge on system updates** — Optionally clear the entire Varnish cache when WordPress core, a theme, or a plugin is updated. Controlled by a new checkbox in the plugin settings.

### 1.0.4

- **Fix fatal error when purging cache from admin bar** — `check_entire_cache_purge()` was called during plugin load, before `wp_get_current_user()` was available. The method is now hooked into `admin_init` instead. Fixes [upstream issue #15](https://github.com/cloudpanel-io/clp-wp-varnish-cache/issues/15).
