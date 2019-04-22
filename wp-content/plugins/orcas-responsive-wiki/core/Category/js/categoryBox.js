var categories = document.getElementsByClassName('category-box');
var categoryHistory = [];

function updateBreadCrumb() {
    var breadCrumb = document.getElementById('category-bread-crumb').children[0];
    var firstItem = breadCrumb.children[0];
    breadCrumb.innerHTML = '';
    breadCrumb.appendChild(firstItem);

    for(var ii = 0; ii < categoryHistory.length; ii++) {
        breadCrumb.innerHTML += '<li data-key="' + categoryHistory[ii].key + '">' + categoryHistory[ii].name + '</li>';
    }

    if(breadCrumb.children.length > 1) {
        breadCrumb.style.display = 'block';
    } else {
        breadCrumb.style.display = 'none';
    }
}

function findKeyInBreadCrumb(key) {
    var revers = JSON.parse(JSON.stringify(categoryHistory)).reverse();
    for(var ii = 0; ii < revers.length; ii++) {
        if(revers[ii].key == key) {
            return ii;
        }
    }

    return -1;
}

for(var ii = 0; ii < categories.length; ii++) {
    categories[ii].onclick = function() {
        var id = this.getAttribute('data-category-view');
        document.getElementById(id).style.display = 'block';
        var header =  this.getElementsByClassName('category-header')[0];
        var name = header.textContent;
        categoryHistory.push({name:name, key:header.getAttribute('data-category-view')});
        updateBreadCrumb();
    };
}

document.getElementById('category-bread-crumb').onclick = function(e) {
    var target = e.target;

    if(target.hasAttribute('data-key')) {
        var itemsDelete = 1;
        var index = findKeyInBreadCrumb(target.getAttribute('data-key'));

        if(index == 0) return;

        if(index == -1) {
            index = itemsDelete = categoryHistory.length;
        }

        for(var ii = categoryHistory.length - (index); ii <  categoryHistory.length; ii++) {
            document.getElementById(categoryHistory[ii].key).style.display = 'none';
        }

        categoryHistory.splice(categoryHistory.length - (index), itemsDelete);
        updateBreadCrumb();
    }
};