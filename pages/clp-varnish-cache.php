<?php

global $clp_varnish_cache_admin;
$is_network = is_multisite() && is_network_admin();
$successNotice = null;
$errorNotice = null;
$host = (true === isset($_SERVER['HTTP_HOST']) && false === empty(sanitize_text_field($_SERVER['HTTP_HOST'])) ? sanitize_text_field($_SERVER['HTTP_HOST']) : '');

function getPostValue($key) {
    $postValue = (true === isset($_POST[$key]) && false === empty(sanitize_text_field($_POST[$key])) ? sanitize_text_field($_POST[$key]) : '');
    return $postValue;
}

$clp_cache_manager = $clp_varnish_cache_admin->get_clp_cache_manager();

if (true === isset($_POST['action']) && 'save-settings' == sanitize_text_field($_POST['action'])) {
    $old_cache_tag_prefix = $clp_cache_manager->get_cache_tag_prefix();
    $enabled = (1 == getPostValue('enabled')  ? true : false);
    $server = getPostValue('server');
    $cache_lifetime = getPostValue('cache-lifetime');
    $cache_tag_prefix = getPostValue('cache-tag-prefix');
    $excluded_params = array_map('trim', array_filter(explode(',', getPostValue('excluded-params'))));
    $excludes = (true === isset($_POST['excludes']) ? sanitize_textarea_field($_POST['excludes']) : '');
    $excludes = array_map('trim', array_filter(explode(PHP_EOL, $excludes)));
    if (false === empty($server) && false === empty($cache_lifetime) && false === empty($cache_tag_prefix)) {
        $cache_settings = [
            'enabled'        => $enabled,
            'server'         => $server,
            'cacheTagPrefix' => $cache_tag_prefix,
            'cacheLifetime'  => $cache_lifetime,
            'excludes'       => $excludes,
            'excludedParams' => $excluded_params,
        ];
        $clp_cache_manager->write_cache_settings($cache_settings);
        $clp_cache_manager->reset_cache_settings();
        if (false === $enabled) {
            if (false === empty($old_cache_tag_prefix)) {
                $clp_cache_manager->purge_tag($old_cache_tag_prefix);
            }
            if (false === empty($host)) {
                $clp_cache_manager->purge_host($host);
            }
        }
        $successNotice = 'Settings have been saved.';
    }
}

if (true === isset($_POST['action']) && 'purge-cache' == sanitize_text_field($_POST['action'])) {
    $purge_values = array_map('trim', array_filter(explode(',', getPostValue('purge-value'))));
    if (false === empty($purge_values)) {
        foreach ($purge_values as $purge_value) {
            $purge_value = trim($purge_value);
            if (false === empty($purge_value)) {
                if (true === str_starts_with($purge_value, 'http')) {
                    $clp_cache_manager->purge_url($purge_value);
                } else {
                    $clp_cache_manager->purge_tag($purge_value);
                }
            }
        }
        $successNotice = 'Varnish Cache has been purged.';
    }
}

if (true === isset($_GET['action']) && 'purge-entire-cache' == sanitize_text_field($_GET['action'])) {
    if (false === empty($host)) {
        $clp_cache_manager->purge_host($host);
    }
    $cache_tag_prefix = $clp_cache_manager->get_cache_tag_prefix();
    if (false === empty($cache_tag_prefix)) {
        $clp_cache_manager->purge_tag($cache_tag_prefix);
    }
    $successNotice = 'Varnish Cache has been purged.';
}

$clp_cache_settings = $clp_cache_manager->get_cache_settings();
$is_enabled = $clp_cache_manager->is_enabled();
$server = $clp_cache_manager->get_server();
$cache_lifetime = $clp_cache_manager->get_cache_lifetime();
$cache_tag_prefix = $clp_cache_manager->get_cache_tag_prefix();
$excluded_params = $clp_cache_manager->get_excluded_params();
$excludes = $clp_cache_manager->get_excludes();

?>
<h1 id="clp-varnish-cache"><?php esc_html_e( 'CLP Varnish Cache', 'clp-varnish-cache' ); ?></h1>

<div class="clp-varnish-cache-container">
  <?php if (false === empty($clp_cache_settings)): ?>
    <?php if (false === is_null($successNotice)): ?>
      <div id="notice" class="notice notice-success fade is-dismissible">
        <p><strong><?php echo esc_html__($successNotice, 'clp-varnish-cache'); ?></strong></p>
      </div>
    <?php endif; ?>
    <?php if (false === is_null($errorNotice)): ?>
      <div id="notice" class="notice notice-error fade is-dismissible">
        <p><strong><?php echo esc_html__($errorNotice, 'clp-varnish-cache'); ?></strong></p>
      </div>
    <?php endif; ?>
    <div class="clp-varnish-cache-block-container">
      <form action="<?php echo (true === $is_network ? network_admin_url('settings.php?page=clp-varnish-cache') : admin_url('options-general.php?page=clp-varnish-cache')) ?>" method="post">
        <div class="clp-varnish-cache-block">
          <div class="clp-varnish-cache-block-header">
            <h3><?php esc_html_e( 'Settings', 'clp-varnish-cache' ); ?></h3>
          </div>
          <div class="clp-varnish-cache-block-content clp-varnish-cache-block-settings">
            <table class="form-table">
              <tbody>
                <tr>
                  <td class="field-name">
                    <?php esc_html_e( 'Enable Varnish Cache', 'clp-varnish-cache' ); ?>:
                  </td>
                  <td>
                    <input type="checkbox" name="enabled" <?php echo (true === $is_enabled ? 'checked' : ''); ?> value="1" />
                  </td>
                </tr>
                <tr>
                  <td class="field-name">
                    <?php esc_html_e( 'Varnish Server', 'clp-varnish-cache' ); ?>:
                  </td>
                  <td>
                    <input type="text" name="server" required="required" value="<?php echo esc_html($server); ?>" />
                  </td>
                </tr>
                <tr>
                  <td class="field-name">
                    <?php esc_html_e( 'Cache Lifetime', 'clp-varnish-cache' ); ?>:
                  </td>
                  <td>
                    <input type="text" name="cache-lifetime" required="required" value="<?php echo esc_html($cache_lifetime); ?>" />
                    <p class="description"><?php esc_html_e( 'Cache Lifetime in seconds before being refreshed.', 'clp-varnish-cache' ); ?></p>
                  </td>
                </tr>
                <tr>
                  <td class="field-name">
                    <?php esc_html_e( 'Cache Tag Prefix', 'clp-varnish-cache' ); ?>:
                  </td>
                  <td>
                    <input type="text" name="cache-tag-prefix" required="required" value="<?php echo esc_html($cache_tag_prefix); ?>" />
                  </td>
                </tr>
                <tr>
                  <td class="field-name">
                    <?php esc_html_e( 'Excluded Params', 'clp-varnish-cache' ); ?>:
                  </td>
                  <td>
                    <input type="text" name="excluded-params" value="<?php echo esc_html($excluded_params); ?>" />
                    <p class="description"><?php esc_html_e( 'List of GET parameters, separated by a comma, to disable caching.', 'clp-varnish-cache' ); ?></p>
                  </td>
                </tr>
                <tr>
                  <td class="field-name">
                    <?php esc_html_e( 'Excludes', 'clp-varnish-cache' ); ?>:
                  </td>
                  <td>
                    <textarea name="excludes" rows="6"><?php echo esc_textarea($excludes); ?></textarea>
                    <p class="description"><?php esc_html_e( 'Urls and files that Varnish Cache shouldn\'t cache.', 'clp-varnish-cache' ); ?></p>
                  </td>
                </tr>
              </tbody>
            </table>
            <input type="hidden" name="action" value="save-settings" />
            <input type="submit" class="button action" value="<?php esc_html_e( 'Save', 'clp-varnish-cache' ); ?>" />
          </div>
        </div>
      </form>
      <form action="<?php echo (true === $is_network ? network_admin_url('settings.php?page=clp-varnish-cache') : admin_url('options-general.php?page=clp-varnish-cache')) ?>" method="post">
        <div class="clp-varnish-cache-block">
          <div class="clp-varnish-cache-block-header">
            <h3><?php esc_html_e( 'Purge Cache', 'clp-varnish-cache' ); ?></h3>
            <a class="button button-primary" href="<?php echo (true === $is_network ? network_admin_url('settings.php?page=clp-varnish-cache&action=purge-entire-cache') : admin_url('options-general.php?page=clp-varnish-cache&action=purge-entire-cache')) ?>">Purge Entire Cache</a>
          </div>
          <div class="clp-varnish-cache-block-content clp-varnish-cache-block-purge-cache">
            <table class="form-table">
              <tbody>
                <tr>
                  <td>
                    <input type="text" name="purge-value" required="required" class="purge-value" placeholder="https://www.domain.com/site.html">
                    <p class="description"><?php esc_html_e( 'You can purge single urls or tags separated by comma.', 'clp-varnish-cache' ); ?></p>
                  </td>
                </tr>
              </tbody>
            </table>
            <input type="hidden" name="action" value="purge-cache" />
            <input type="submit" class="button action" value="<?php esc_html_e( 'Purge Cache', 'clp-varnish-cache' ); ?>" />
          </div>
        </div>
      </form>
      <div class="clp-varnish-cache-block">
        <div class="clp-varnish-cache-block-header">
          <h3><?php esc_html_e( 'Support', 'clp-varnish-cache' ); ?></h3>
        </div>
         <div class="clp-varnish-cache-block-content">
           <table class="form-table">
             <tbody>
               <tr>
                 <td class="field-name">
                   <?php esc_html_e( 'Documentation', 'clp-varnish-cache' ); ?>:
                 </td>
                 <td>
                   <a target="_blank" href="https://www.cloudpanel.io/docs/v2/frontend-area/varnish-cache/wordpress/plugin/">https://www.cloudpanel.io/docs/v2/frontend-area/varnish-cache/wordpress/plugin/</a>
                 </td>
               </tr>
               <tr>
                 <td class="field-name">
                   <?php esc_html_e( 'Discord', 'clp-varnish-cache' ); ?>:
                 </td>
                 <td>
                   <a target="_blank" href="https://discord.cloudpanel.io/">https://discord.cloudpanel.io/</a>
                 </td>
               </tr>
             </tbody>
           </table>
         </div>
      </div>
    </div>
  <?php else: ?>
    <div id="notice" class="notice notice-error fade is-dismissible">
      <p><strong><?php echo esc_html__('Settings File Not Found!', 'clp-varnish-cache'); ?></strong></p>
    </div>
  <?php endif ?>
</div>