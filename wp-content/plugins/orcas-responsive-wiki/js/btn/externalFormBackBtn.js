var btnList = document.getElementsByClassName('external-form-back-action');

for(var ii = 0; ii < btnList.length; ii++) {
    btnList[ii].onclick = function() {
        document.getElementById('wiki-form-back-btn').click();
        return false;
    };
}