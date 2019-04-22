<div class="wiki-detail-view <?php echo $preFill['id'] > 0 ? 'wiki-page-pre-fill' : ''; ?>">
<div>
    <button id="detail-page-back" title="<?php echo __('Back', 'orcas-responsive-wiki'); ?>" class="btn btn-back"><?php echo __('Back', 'orcas-responsive-wiki'); ?></button>
    <h3 class="detail-header"><span><?php echo $this->deesc($preFill['title']) ?></span><span class="small"></span>
        <span class="wiki-icon-list">
            <?php if($allowCreate) { ?><i title="<?php echo __('Edit', 'orcas-responsive-wiki'); ?>" class="fa fa-edit wiki-page-edit" data-id="<?php echo esc_attr($preFill['id']); ?>"></i><?php } ?>
            <?php echo join("", $iconList); ?>
        </span>
    </h3>
    <div class="wiki-breadcrumb small"></div>
</div>

    <figure class="loader">
        <div></div><div></div>
        <div></div><div></div>
        <div></div><div></div>
        <div></div><div></div>
    </figure>

<div class="wiki-page"><?php echo $preFill['content']; ?></div>
</div>