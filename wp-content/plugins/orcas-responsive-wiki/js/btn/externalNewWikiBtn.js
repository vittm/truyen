if(!document.getElementById('responsive-wiki-new-form')) {
    $.ajax({
        method:'post',
        url:'/wp-admin/admin-ajax.php',
        data:{
            action:'wiki_load_form'
        },
        success:function(response) {
            var body = document.getElementsByTagName('body')[0];
            var div = document.createElement('div');

            div .innerHTML = response;
            var modal = div.children[0];
            body.appendChild(modal);


            var abort = document.getElementById('form-create-close-btn');
            abort._modal = modal;
            abort.onclick = function() {
                this._modal.style.display = 'none';
            };

            wiki = new Wiki(document.getElementById('form-create-modal').children[0], 'modal-create-form');

            var btnList = document.getElementsByClassName('external-new-wiki-action');

            for(var ii = 0; ii < btnList.length; ii++) {
                btnList[ii]._modal = modal;
                btnList[ii].onclick = function() {
                    this._modal.style.display = 'block';
                    return false;
                };
            }
        }
    });
} else {
    var btnList = document.getElementsByClassName('external-new-wiki-action');

    for(var ii = 0; ii < btnList.length; ii++) {
        btnList[ii].onclick = function() {
            document.getElementById('responsive-wiki-context').getElementsByClassName('btn-new-wiki')[0].click();
            return false;
        };
    }
}