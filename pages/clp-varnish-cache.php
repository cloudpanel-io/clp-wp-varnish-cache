<?php

global $clp_varnish_cache_admin;
$is_network = is_multisite() && is_network_admin();
$successNotice = null;
$errorNotice = null;
$host = !empty($_SERVER['HTTP_HOST']) ? sanitize_text_field($_SERVER['HTTP_HOST']) : '';

function getPostValue($key) {
    return !empty($_POST[$key]) ? sanitize_text_field($_POST[$key]) : '';
}

$clp_cache_manager = $clp_varnish_cache_admin->get_clp_cache_manager();
$form_action_url = $is_network ? network_admin_url('settings.php?page=clp-varnish-cache') : admin_url('options-general.php?page=clp-varnish-cache');

if (isset($_POST['action']) && $_POST['action'] === 'save-settings') {
    if (!isset($_POST['clp_varnish_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['clp_varnish_nonce']), 'clp_varnish_save_settings')) wp_die('Security check failed.');

    $old_cache_tag_prefix = $clp_cache_manager->get_cache_tag_prefix();
    $enabled = getPostValue('enabled') == 1;
    $server = getPostValue('server');
    $cache_lifetime = getPostValue('cache-lifetime');
    $cache_tag_prefix = getPostValue('cache-tag-prefix');
    $excluded_params = array_map('trim', array_filter(explode(',', getPostValue('excluded-params'))));
    $excludes = isset($_POST['excludes']) ? sanitize_textarea_field($_POST['excludes']) : '';
    $excludes = array_map('trim', array_filter(explode(PHP_EOL, $excludes)));

    if (!empty($server) && !empty($cache_lifetime) && !empty($cache_tag_prefix)) {
        $clp_cache_manager->write_cache_settings([
            'enabled'        => $enabled,
            'server'         => $server,
            'cacheTagPrefix' => $cache_tag_prefix,
            'cacheLifetime'  => $cache_lifetime,
            'excludes'       => $excludes,
            'excludedParams' => $excluded_params,
        ]);
        $clp_cache_manager->reset_cache_settings();

        if (!$enabled) {
            if (!empty($old_cache_tag_prefix)) $clp_cache_manager->purge_tag($old_cache_tag_prefix);
            if (!empty($host)) $clp_cache_manager->purge_host($host);
        }
        $successNotice = 'Settings have been saved.';
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'purge-cache') {
    if (!isset($_POST['clp_varnish_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['clp_varnish_nonce']), 'clp_varnish_purge_cache')) wp_die('Security check failed.');

    $purge_values = array_map('trim', array_filter(explode(',', getPostValue('purge-value'))));
    foreach ($purge_values as $purge_value) {
        if (empty($purge_value)) continue;

        if (strpos($purge_value, 'http') === 0) $clp_cache_manager->purge_url($purge_value);
        else $clp_cache_manager->purge_tag($purge_value);
    }
    if (!empty($purge_values)) $successNotice = 'Varnish Cache has been purged.';
}

if (isset($_GET['action']) && $_GET['action'] === 'purge-entire-cache') {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field($_GET['_wpnonce']), 'purge-entire-cache')) wp_die('Security check failed.');

    $clp_cache_manager->purge_entire_cache();
    $successNotice = 'Varnish Cache has been purged.';
}

$clp_cache_settings = $clp_cache_manager->get_cache_settings();
$is_enabled = $clp_cache_manager->is_enabled();
$server = $clp_cache_manager->get_server();
$cache_lifetime = $clp_cache_manager->get_cache_lifetime();
$cache_tag_prefix = $clp_cache_manager->get_cache_tag_prefix();
$excluded_params = $clp_cache_manager->get_excluded_params();
$excludes = $clp_cache_manager->get_excludes();
$excludes_text = is_array($excludes) ? implode(PHP_EOL, $excludes) : $excludes;

?>
<div class="clp-varnish-cache-container">
  <?php if (!empty($clp_cache_settings)): ?>

    <?php if ($successNotice !== null): ?>
      <div id="notice" class="notice notice-success fade is-dismissible">
        <p><strong><?php echo esc_html($successNotice); ?></strong></p>
      </div>
    <?php endif; ?>
    <?php if ($errorNotice !== null): ?>
      <div id="notice" class="notice notice-error fade is-dismissible">
        <p><strong><?php echo esc_html($errorNotice); ?></strong></p>
      </div>
    <?php endif; ?>

    <!-- Settings Card -->
    <form action="<?php echo esc_url($form_action_url); ?>" method="post">
      <div class="clp-varnish-cache-block">
        <div class="clp-varnish-cache-block-header">
          <h3>
            <label class="clp-toggle" title="<?php esc_attr_e('Enable Varnish Cache', 'clp-varnish-cache'); ?>">
              <input type="checkbox" name="enabled" value="1" <?php checked($is_enabled); ?>>
              <span class="clp-toggle-slider"></span>
            </label>
            <?php esc_html_e('Varnish Cache', 'clp-varnish-cache'); ?>
          </h3>
        </div>
        <div class="clp-varnish-cache-block-content">
          <div class="clp-form-grid">
            <div class="clp-form-group">
              <label for="clp-server">
                <?php esc_html_e('Varnish Server', 'clp-varnish-cache'); ?>
                <span class="clp-required">*</span>
              </label>
              <input type="text" id="clp-server" name="server" required value="<?php echo esc_attr($server); ?>" placeholder="127.0.0.1:6081">
            </div>
            <div class="clp-form-group">
              <label for="clp-cache-lifetime">
                <?php esc_html_e('Cache Lifetime', 'clp-varnish-cache'); ?>
                <span class="clp-required">*</span>
              </label>
              <input type="text" id="clp-cache-lifetime" name="cache-lifetime" required value="<?php echo esc_attr($cache_lifetime); ?>">
              <p class="description"><?php esc_html_e('Cache Lifetime in seconds before being refreshed.', 'clp-varnish-cache'); ?></p>
            </div>
            <div class="clp-form-group">
              <label for="clp-cache-tag-prefix">
                <?php esc_html_e('Cache Tag Prefix', 'clp-varnish-cache'); ?>
                <span class="clp-required">*</span>
              </label>
              <input type="text" id="clp-cache-tag-prefix" name="cache-tag-prefix" required value="<?php echo esc_attr($cache_tag_prefix); ?>">
            </div>
            <div class="clp-form-group">
              <label for="clp-excluded-params"><?php esc_html_e('Excluded Params', 'clp-varnish-cache'); ?></label>
              <input type="text" id="clp-excluded-params" name="excluded-params" value="<?php echo esc_attr(is_array($excluded_params) ? implode(',', $excluded_params) : $excluded_params); ?>">
              <p class="description"><?php esc_html_e('List of GET parameters, separated by a comma, to disable caching.', 'clp-varnish-cache'); ?></p>
            </div>
            <div class="clp-form-group clp-full-col">
              <label for="clp-excludes"><?php esc_html_e('Excludes', 'clp-varnish-cache'); ?></label>
              <textarea id="clp-excludes" name="excludes" rows="6"><?php echo esc_textarea($excludes_text); ?></textarea>
              <p class="description"><?php esc_html_e('Urls and files that Varnish Cache shouldn\'t cache.', 'clp-varnish-cache'); ?></p>
            </div>
          </div>
        </div>
        <div class="clp-varnish-cache-block-footer">
          <?php wp_nonce_field('clp_varnish_save_settings', 'clp_varnish_nonce'); ?>
          <input type="hidden" name="action" value="save-settings">
          <input type="submit" class="button clp-btn-primary" value="<?php esc_attr_e('Save', 'clp-varnish-cache'); ?>">
        </div>
      </div>
    </form>

    <!-- Purge Cache Card -->
    <form action="<?php echo esc_url($form_action_url); ?>" method="post">
      <div class="clp-varnish-cache-block">
        <div class="clp-varnish-cache-block-header">
          <h3><?php esc_html_e('Purge Cache', 'clp-varnish-cache'); ?></h3>
          <a class="button clp-btn-secondary" href="<?php echo esc_url(wp_nonce_url($form_action_url . '&action=purge-entire-cache', 'purge-entire-cache')); ?>">
            <?php esc_html_e('Purge Entire Cache', 'clp-varnish-cache'); ?>
          </a>
        </div>
        <div class="clp-varnish-cache-block-content">
          <div class="clp-form-group">
            <input type="text" name="purge-value" required placeholder="https://www.domain.com/site.html">
            <p class="description"><?php esc_html_e('You can purge single urls or tags separated by comma.', 'clp-varnish-cache'); ?></p>
          </div>
        </div>
        <div class="clp-varnish-cache-block-footer">
          <?php wp_nonce_field('clp_varnish_purge_cache', 'clp_varnish_nonce'); ?>
          <input type="hidden" name="action" value="purge-cache">
          <input type="submit" class="button clp-btn-primary" value="<?php esc_attr_e('Purge Cache', 'clp-varnish-cache'); ?>">
        </div>
      </div>
    </form>

    <!-- Support Card -->
    <div class="clp-varnish-cache-block">
      <div class="clp-varnish-cache-block-header">
        <h3><?php esc_html_e('Support', 'clp-varnish-cache'); ?></h3>
      </div>
      <div class="clp-varnish-cache-block-content">
        <table class="clp-support-table">
          <tbody>
            <tr>
              <td><?php esc_html_e('Documentation', 'clp-varnish-cache'); ?>:</td>
              <td><a target="_blank" href="https://www.cloudpanel.io/docs/v2/frontend-area/varnish-cache/wordpress/plugin/">https://www.cloudpanel.io/docs/v2/frontend-area/varnish-cache/wordpress/plugin/</a></td>
            </tr>
            <tr>
              <td><?php esc_html_e('Discord', 'clp-varnish-cache'); ?>:</td>
              <td><a target="_blank" href="https://discord.cloudpanel.io/">https://discord.cloudpanel.io/</a></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  <?php else: ?>
    <div id="notice" class="notice notice-error fade is-dismissible">
      <p><strong><?php esc_html_e('Settings File Not Found!', 'clp-varnish-cache'); ?></strong></p>
    </div>
  <?php endif; ?>
</div>