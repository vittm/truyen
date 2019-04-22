<?php
    //
    if($success) {?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Done! Licence key accepted.', 'orcas-upgrade'); ?></p>
    </div>
<?php } else if($error) { ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'Invalid licence key or password', 'orcas-upgrade'); ?></p>
    </div>
<?php } ?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div style="position: relative">
        <form method="post">
            <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row"><?php echo __('Upgrade token', 'orcas-upgrade'); ?></th>
                <td class="orcas-item-update-token">
                    <?php echo strlen(get_option('orcas_upgrade_token')) > 0 ? '<div class="valid"></div>' : ''; ?>
                    <input required id="orcas_upgrade_token" style="width: 100%;" name="orcas_upgrade_token" value="<?php echo get_option('orcas_upgrade_token'); ?>" type="text">
                </td>
            </tr>
            <tr <?php echo strlen(get_option('orcas_upgrade_token')) > 0 ? 'style="display:none;"' : ''; ?>>
                <th scope="row"><?php echo __('orcas.de password', 'orcas-upgrade'); ?></th>
                <td class="orcas-item-update-token">
                    <input required id="orcas_upgrade_token_password" style="width: 100%;" name="orcas_upgrade_token_password" value="" type="password" placeholder="<?php echo __('Your orcas.de account password.', 'orcas-upgrade'); ?>">
                </td>
            </tr>
            <tr>
                <td <?php echo strlen(get_option('orcas_upgrade_token')) > 0 ? 'style="display:none;"' : ''; ?>><?php submit_button(); ?></td>
            </tr>
            </tbody>
            </table>
        </form>
        <form method="post" style="position: absolute;     top: 7px; right: 9px; <?php echo strlen(get_option('orcas_upgrade_token')) > 0 ? 'display:inline-block;' : 'display:none;'; ?>">
            <input type="submit" class="button button-primary" name="delete-token" value="<?php echo __('Delete token', 'orcas-upgrade'); ?>"/>
        </form>
    </div>

    <?php
    $items = \de\orcas\extension\ShopItems::getItems();

    if(is_array($items)) {
        foreach($items as $slug => $item) {
            include 'item.phtml';
        }
    }

    ?>
</div>