function WikiCategory(wiki) {
        this._wiki = wiki;
        this.init();
    }

    WikiCategory.prototype._wiki = null;

    WikiCategory.prototype.init = function () {
        document.getElementById('category-selection-container').onclick = function (e) {
            if (e.target.classList.contains('new-sub-category')) {
                document.getElementById('dialog-new-category').style.display = 'block';
                document.getElementById('category-selection-container').style.display = 'none';


                var li = e.target.parentNode;

                document.getElementById('dialog-new-category')._edit = false;
                document.getElementById('dialog-new-category')._parent = li.getElementsByTagName('ul')[0];
            } else if (e.target.classList.contains('category-radio')) {
                document.getElementsByClassName('input-selection')[0].textContent = e.target.value;
            }
        };

        document.getElementById('new-category-name-abort').onclick = function () {
            document.getElementById('new-category-name').value = '';
            document.getElementById('dialog-new-category').style.display = 'none';
            document.getElementById('category-selection-container').removeAttribute('style');
        };

        document.getElementById('new-category-name-submit').onclick = function () {
            document.getElementById('dialog-new-category').style.display = 'none';
            document.getElementById('category-selection-container').removeAttribute('style');
            var tpl = document.getElementById('template-list').children[0];
            var clone = tpl.cloneNode(true);
            var parent = document.getElementById('dialog-new-category')._parent;
            var route = parent ? JSON.parse(parent.getAttribute('data-cat-route')) : [];
            var name = document.getElementById('new-category-name').value;
            route.push(name);
            var id = parseInt(Math.random() * 10000);

            clone.innerHTML = clone.innerHTML
                .split('{{ID}}').join(id)
                .split('{{NAME}}').join(name)
                .split('{{ROUTE}}').join(JSON.stringify(route))
                .split('{{SUB}}').join('')
                .split('{{VALUE}}').join(name);

            if (parent) {
                parent.appendChild(clone);
            } else {
                document.getElementById('category-selection-container').appendChild(clone);
            }

            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                method: 'post',
                data: {
                    action: 'wiki_edit_category',
                    route: route
                },
                success: function () {
                    document.getElementById('new-category-name').value = '';
                }
            });
        };
        this._wiki.addFilter('wiki_after_load_edit_form', [this, 'setSelectedCategory']);
        this._wiki.addFilter('wiki_open_edit_form', [this, 'openEditForm']);
    };

    WikiCategory.prototype.openEditForm = function(args) {
        var list = document.getElementsByClassName('category-radio');

        for(var ii = 0; ii < list.length; ii++) {
            if(list[ii].value == args.args.category) {
                list[ii].checked = true;
                document.getElementsByClassName('input-selection')[0].textContent = args.args.category;
                break;
            }
        }
    };

    WikiCategory.prototype.setSelectedCategory = function (args) {

        var list = document.getElementsByName('wiki-category');

        for (var ii = 0; ii < list.length; ii++) {
            if (list[ii].getAttribute('value') == args.args.category) {
                list[ii].checked = true;
                break;
            }
        }

        document.getElementsByClassName('input-selection')[0].textContent = args.args.category;
    };
