/**
 * Created by icewindow on 31.05.18.
 */

(function($) {
    $('.orcas-nagger .nag span a').on('click', function(evt) {
        evt.preventDefault();
        var $this = $(this);
        var p = $this.parent().parent();
        var plugin = p.data('plugin');
        var type = p.parent().hasClass('rate-nagger') ? 'rate' : 'pro';
        $.ajax({
            url: orcas_nagger.ajax,
            method: 'post',
            data: {
                action: 'orcas-nagger',
                plugin: plugin,
                type: type,
                dismiss: $this.hasClass('dismiss'),
                stop: $this.hasClass('stop')
            }
        });
        if (p.parent().find('.nag:visible').length <= 1) {
            // Last one
            p.parent().find('button').click();
        } else {
            p.slideUp();
        }
    })
})(jQuery);