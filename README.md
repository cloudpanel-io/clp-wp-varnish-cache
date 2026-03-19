# CLP Varnish Cache Plugin for WordPress

With the **CLP Varnish Cache Plugin** for WordPress, you can easily manage your Varnish cache settings and perform purge operations directly from your WordPress admin dashboard or admin bar.

<p align="center">
  <a href="https://www.cloudpanel.io/docs/v2/frontend-area/varnish-cache/wordpress/plugin/" target="_blank">
    <img src="/release/plugin.png?v=0.0.1" alt="CLP Varnish Cache Plugin">
  </a>
</p>

## Features

* **Auto-Purging:** Automatically purges the cache when updating content.
* **Cache Lifetime:** Define how long items should live in the Varnish cache before being refreshed.
* **Custom Exclusions:** Configure custom URL paths and GET parameters to exclude from the cache.
* **Manual Purging:** Easily purge the entire cache, specific URLs, or cache tags via the WordPress admin settings or the admin bar.

## 💻 For Developers: Hooking into the Plugin

If you are writing custom plugins, themes, or using a tool like Code Snippets, you can easily hook into the `ClpVarnishCacheManager` to clear the cache programmatically.

Here are a few examples of how to utilize the manager class:

```php
// First, make sure the class is available
if (class_exists('ClpVarnishCacheManager')) {
    $varnish_manager = new ClpVarnishCacheManager();

    // purge the entire cache
    $varnish_manager->purge_entire_cache();

    // or purge a specific URL
    $varnish_manager->purge_url('https://www.yourdomain.com/specific-page/');

    // or purge a specific cache tag
    $varnish_manager->purge_tag('my_custom_tag');
}
```

## Support This Project

* Please star the project on GitHub
* Write about **CloudPanel** on platforms like **Twitter**, **Facebook** or **LinkedIn**
* Follow us on [Twitter](https://twitter.com/cloudpanel_io) and retweet our tweets
* Write a **Blog** post about **CloudPanel**
* Give us [Feedback](https://www.cloudpanel.io/feedback/) to improve **CloudPanel**
* Report [Bugs](https://github.com/cloudpanel-io/cloudpanel-ce/issues) on Github
* Join our [Discord Server](https://discord.cloudpanel.io/)
* [Get in touch with us](https://www.cloudpanel.io/contact/) if you have other ideas