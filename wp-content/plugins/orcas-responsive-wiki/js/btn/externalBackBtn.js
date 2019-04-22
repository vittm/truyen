var btnList = document.getElementsByClassName('external-back-action');

for(var ii = 0; ii < btnList.length; ii++) {
    btnList[ii].onclick = function() {
        document.getElementById('detail-page-back').click();
        return false;
    };
}