<?php

global $clp_varnish_cache_admin;

$clp_cache_manager = $clp_varnish_cache_admin->get_clp_cache_manager();
$clp_cache_settings = $clp_cache_manager->get_cache_settings();

$is_network = is_multisite() && is_network_admin();
$notice = null;
if (true === isset($_GET['action']) && 'purge-entire-cache' == $_GET['action']) {
    $notice = $clp_varnish_cache_admin->get_success_notice('Varnish Cache has been purged.');
}

?>
<h1 id="clp-varnish-cache"><?php esc_html_e( 'CLP Varnish Cache', 'clp-varnish-cache' ); ?></h1>

<div class="clp-varnish-cache-container">
  <?php if (false === empty($clp_cache_settings)): ?>
    <?php if (false === is_null($notice)): ?>
      <?php echo $notice; ?>
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
                    sdfsdf
                  </td>
                </tr>
                <tr>
                  <td class="field-name">
                    <?php esc_html_e( 'Varnish Server', 'clp-varnish-cache' ); ?>:
                  </td>
                  <td>
                    <input type="text" name="server" required="required">
                  </td>
                </tr>
              </tbody>
            </table>
            <input type="hidden" name="action" value="settings" />
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
                    <input type="text" name="purge-value" required="required" class="purge-value" placeholder="https://wp.moby.io/example-site">
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
    </div>
  <?php else: ?>
     <?php echo $clp_varnish_cache_admin->get_error_notice('Settings File Not Found!'); ?>
  <?php endif ?>
</div>