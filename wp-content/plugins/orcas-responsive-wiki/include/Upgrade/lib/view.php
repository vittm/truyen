<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post">
        <table class="form-table">
            <tbody><tr valign="top">
                <th scope="row"><?php echo __('Upgrade token', 'orcas-upgrade'); ?></th>
                <td class="orcas-item-update-token"><input id="orcas_upgrade_token" style="width: 100%;" name="orcas_upgrade_token" value="<?php echo get_option('orcas_upgrade_token'); ?>" type="text"></td>
                <td><?php submit_button(); ?></td>
            </tr>
            </tbody></table>
        <small>
    </form>

    <?php
    $items = \de\orcas\extension\ShopItems::getItems();

    foreach ($items as $slug => $item) {
        include 'item.phtml';
    }

    ?>
</div>