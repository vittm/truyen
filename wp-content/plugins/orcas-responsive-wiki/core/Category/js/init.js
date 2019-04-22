jQuery('.dd').nestable();
jQuery('.dd').on('change', function(e) {
    update();
});

function update() {
    var data = jQuery('.dd').nestable('serialize');
    document.getElementById('categories-form-list').value = JSON.stringify(data);

    var inputs = document.getElementById('nestable3').getElementsByClassName('editable-category-name');

    for(var ii = 0; ii < inputs.length; ii++) {
        inputs[ii].onchange = function() {
            this.parentNode.parentNode.setAttribute('data-new', this.value);
        };
    }
}

document.getElementById('nestable3').onclick = function(e) {
    var target = e.target;

    if(target.classList.contains('close-icon-list')) {
        target.parentNode.style.display = 'none';
        var icons =  target.previousElementSibling.getElementsByClassName('icon');
        for(var ii = 0; ii < icons.length; ii++) {
            if(icons[ii].checked) {
                var value = icons[ii].value;
                if(ii == 0) value = '';
                target.parentNode.parentNode.parentNode.setAttribute('data-icon', value);
                update();
                break;
            }
        }
    } else if(target.classList.contains('open-icons')) {
        target.nextElementSibling.style.display = 'block';
    } else if(target.classList.contains('remove-icon')) {
        target.parentNode.parentNode.parentNode.parentNode.removeChild(target.parentNode.parentNode.parentNode);
        update();
    }
};

document.getElementById('add-new-category-submit').onclick = function(e) {
    if(document.getElementById('add-new-category').value.trim().length > 0) {
        if(unique(document.getElementById('add-new-category').value.trim())) {
            var tpl = document.getElementById('category-placeholder').innerHTML;
            var li = document.createElement('div');
            var list = document.getElementById('categories');
            li.innerHTML = tpl
                .split('{{NAME}}').join(document.getElementById('add-new-category').value.trim())
                .split('{{PLACEHOLDER}}').join(Math.floor(Math.random() * 1000000))
                .split('{{ICON}}').join('')
                .split('{{COLLAPSE}}').join('')
                .split('{{SUBLIST}}').join('')
                .split('{{ID}}').join(document.getElementById('add-new-category').value.trim().replace(' ', '-').toLowerCase());

            list.appendChild(li.children[0]);
            update();
            document.getElementById('add-new-category').value = '';
            document.getElementById('category-exist-error').style.display = 'none';
        } else {
            document.getElementById('category-exist-error').style.display = 'block';
        }
    }

    e.stopPropagation();
    return false;
};

function unique(newItem) {
    var list = document.getElementsByClassName('dd-item');
    for(var index = 0; index < list.length; index++) {
        if(list[index].getAttribute('data-new') == newItem) {
            return false;
        }
    }

    return true;
}

update();