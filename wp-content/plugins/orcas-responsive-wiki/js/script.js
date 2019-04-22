var $ = jQuery;
var oldSize = 0;
var wiki = null;

jQuery(document).ready(function () {
    // var calendarContext = document.getElementsByClassName('responsive-wiki-context');
    // for (var ii = 0; ii < calendarContext.length; ii++) {
    //     new Wiki(calendarContext[ii]);
    // }

    resize();
});

function resize() {
    var calendarContext = document.getElementsByClassName('responsive-wiki-context');
    for (var ii = 0; ii < calendarContext.length; ii++) {
        if(calendarContext[ii]._oldSize == undefined || calendarContext[ii]._oldSize != calendarContext[ii].clientWidth) {
            calendarContext[ii]._oldSize = calendarContext[ii].clientWidth;
            wiki = new Wiki(calendarContext[ii]);
        }
    }
    window.setTimeout(resize, 1000);
}

function Wiki(context, type) {
    this._type = type;
    this._context = context;

    this.init(context);

    var extensions = context.getAttribute('data-extensions').split(',');
    for(var ii = 0; ii < extensions.length; ii++) {
        if(extensions[ii].trim().length > 0) {
            this._extensions[extensions[ii]] = eval("new " + extensions[ii].trim() + "(this)");
        }
    }

    if(document.getElementsByClassName('hide-wiki')[0]) {
        window.setTimeout(function() {
            document.getElementsByClassName('hide-wiki')[0].style.opacity = 1;
            document.getElementsByClassName('wiki-initial-loader')[0].style.display = 'none';
        }, 1000);
    }
}

Wiki.prototype._type = null;
Wiki.prototype._history = {'wiki':[]};
Wiki.prototype._filters = {};
Wiki.prototype._context = '';
Wiki.prototype._detail = null;
Wiki.prototype._sliedeContext = null;
Wiki.prototype._extensions = {};

Wiki.prototype.addFilter = function(callName, call) {
    if(!this._filters[callName]) this._filters[callName] = [];
    this._filters[callName].push(call);
};

Wiki.prototype.applyFilter = function(callName, args) {
    if(this._filters[callName]) {
        var args = {
            'context':this._context,
            'args':args
        };

        for(var ii = 0; ii < this._filters[callName].length; ii++) {
            var obj = this._filters[callName][ii];
            if(obj instanceof Array) {
                args.args = obj[0][obj[1]](args);
            } else {
                args.args = obj(args);
            }
        }
        return args.args;
    }

    return args;
};

Wiki.prototype.initPiece = function() {
    PIECE.initialise('wiki-content', {
        min:1,
        pieces:[':'],
        data:function(_handlingData) {
            if(_handlingData) {
                if(_handlingData.before && _handlingData.before.value == 'wiki') {
                    var data = JSON.parse(document.getElementsByClassName('responsive-wiki')[0].getAttribute('data-pice'));
                    var result = [];

                    for(var ii = 0; ii < data.length; ii++) {
                        result.push(data[ii].key);
                    }

                    return result;
                }
            }

            return [];
        }
    });
};

Wiki.prototype.init = function (wikiContext) {
    //this.initPiece();
    if(this._type == undefined) {
        var width = wikiContext.clientWidth;
        var height = wikiContext.clientHeight;
        var slideContext = this._sliedeContext = wikiContext.getElementsByClassName('slide-context-main')[0];
        var wikiView = wikiContext.getElementsByClassName('wiki-view')[0];
        var detailView = this._detail = wikiContext.getElementsByClassName('wiki-detail-view')[0];
        var btnBack = wikiContext.getElementsByClassName('btn-back');
        var multiContentView = wikiContext.getElementsByClassName('multi-content-slide')[0];
        var form = wikiContext.getElementsByClassName('new-wiki-form')[0];
        var btnNew = wikiContext.getElementsByClassName('btn-new-wiki')[0];
        var slides = wikiContext.getElementsByClassName('slide');

        multiContentView._height = height + 'px';
        multiContentView.style.height = height + 'px';

        var edtLink = wikiContext.getElementsByClassName('wiki-page-edit')[0];
        if (edtLink) {
        edtLink._context = wikiContext;
        edtLink._class = this;
        edtLink.onclick = function () {
            document.getElementById('responsive-wiki-form').innerHTML = document.getElementById('responsive-wiki-form').getAttribute('data-edit');
            var target = this;
            var form = this._context.getElementsByClassName('new-wiki-form')[0];
            form.getElementsByTagName('form')[0].style.display = 'none';
            form.getElementsByTagName('form')[1].style.display = 'none';
            form.getElementsByClassName('loader')[0].style.display = 'block';
            document.getElementById('wiki-delete-id').value = target.getAttribute('data-id');
            this._context.getElementsByClassName('back-from-form')[0].classList.add('to-details');
            this._class.viewEdit(this._context);
            var that = this._class;
            jQuery.ajax({
                url: '/wp-admin/admin-ajax.php',
                method: 'post',
                data: {
                    action: 'wiki_page_edit',
                    id: target.getAttribute('data-id'),
                    security: document.getElementById('wiki-inner').getAttribute('data-nonce')
                },
                success: function (e) {
                    var response = JSON.parse(e);
                    var formLayout = form.getElementsByTagName('form')[0];
                    for (var name in response) {
                        var fieldName = 'wiki-' + name;
                        var field = formLayout.getElementsByClassName(fieldName)[0];

                        if (field) {
                            var type = field.getAttribute('type');
                            if (type == 'checkbox' || type == 'radio') {
                                field.checked = response[name] == 1;
                            } else {
                                field.value = response[name];
                                if (field._richText) {
                                    $(field).summernote("code", response[name]);
                                }
                            }
                        }
                    }

                    that.applyFilter('wiki_open_edit_form', response);

                    form.getElementsByTagName('form')[0].style.display = 'block';
                    form.getElementsByTagName('form')[1].style.display = 'block';
                    form.getElementsByClassName('loader')[0].style.display = 'none';
                }
            });
        }
    }

        var zIndex = 100;
        for (var xx = 0; xx < slides.length; xx++) {
            slides[xx].style.position = 'relative';
            slides[xx].style.zIndex = zIndex;
            slides[xx].style.width = (width - 8) + 'px';
            zIndex--;
        }

        for (var jj = 0; jj < btnBack.length; jj++) {
            btnBack[jj]._context = wikiContext;
            btnBack[jj]._class = this;
            btnBack[jj]._calendar = wikiView;
            btnBack[jj]._slider = slideContext;
            btnBack[jj].onclick = function () {
                if (this.classList.contains('to-details')) {
                    this.classList.remove('to-details');
                    this._class.viewDetails(this._context);
                    var form = this._context.getElementsByClassName('new-wiki-form')[0];
                    window.setTimeout(function (form) {
                        form.getElementsByTagName('form')[0].style.display = 'block';
                        form.getElementsByClassName('loader')[0].style.display = 'none';
                    }, 1000, form);

                    var externalFormBackButtons = document.getElementsByClassName('external-form-back-action');

                    for (var ii = 0; ii < externalFormBackButtons.length; ii++) {
                        externalFormBackButtons[ii].style.display = 'none';
                    }

                    var externalBackButtons = document.getElementsByClassName('external-back-action');

                    for (var ii = 0; ii < externalBackButtons.length; ii++) {
                        externalBackButtons[ii].style.display = 'inline-block';
                    }
                } else {
                    //this._class.viewDetails(this._context);
                    this.parentNode.parentNode.parentNode.style.height = this.parentNode.parentNode.parentNode._height;
                    this.parentNode.parentNode.style.zIndex = 1;
                    // this._calendar.style.opacity = 1;
                    // this.parentNode.parentNode.style.opacity = 0;
                    this._slider.style.marginLeft = 0;

                    var externalBackButtons = document.getElementsByClassName('external-back-action');

                    for (var ii = 0; ii < externalBackButtons.length; ii++) {
                        externalBackButtons[ii].style.display = 'none';
                    }

                    var externalFormBackButtons = document.getElementsByClassName('external-form-back-action');

                    for (var ii = 0; ii < externalFormBackButtons.length; ii++) {
                        externalFormBackButtons[ii].style.display = 'none';
                    }
                }
            };
        }

        //form.style.width = multiContentView.style.width = calendarView.style.width = calendarDetailView.style.width = (width - 8) + 'px';
        slideContext.style.width = ((width + 60) * 2) + 'px';

        var slideContextViews = wikiContext.getElementsByClassName('slide-context');

        for (var jj = 0; jj < slideContextViews.length; jj++) {
            slideContextViews[jj]._moveDistance = width - 8;
            slideContextViews[jj].style.width = ((width + 60) * 2) + 'px';
        }

        this.initWikiLinks(wikiView, wikiContext, detailView, wikiView, slideContext);
        this.onlySlideToDetail(slideContext, wikiView, detailView);


        if (btnNew) {
            btnNew._context = wikiContext;
            btnNew._class = this;
            btnNew._form = form;
            btnNew._parent = wikiView;
            btnNew._slider = slideContext;
            btnNew.onclick = function () {
                document.getElementById('responsive-wiki-form').innerHTML = document.getElementById('responsive-wiki-form').getAttribute('data-new');
                //reset form
                this._form.getElementsByTagName('form')[1].style.display = 'none';
                var inputs = this._form.getElementsByTagName('input');
                for (var ii = 0; ii < inputs.length; ii++) {
                    var type = inputs[ii].getAttribute('type');
                    if (type == 'checkbox' || type == 'radio') {
                        inputs[ii].checked = false;
                    } else {
                        if (inputs[ii].getAttribute('name') != '_wpnonce' && inputs[ii].getAttribute('name') != '_wp_http_referer') {
                            inputs[ii].value = '';
                            if (inputs[ii]._richText) {
                                $(inputs[ii]).summernote("code", '');
                            }
                        }
                    }
                }

                var textareas = this._form.getElementsByTagName('textarea');
                for (var ii = 0; ii < textareas.length; ii++) {
                    textareas[ii].value = '';
                    if (textareas[ii]._richText) {
                        $(textareas[ii]).summernote("code", '');
                    }
                }

                var selections = this._form.getElementsByTagName('select');
                for (var ii = 0; ii < selections.length; ii++) {
                    selections[ii].selectedIndex = 0;
                }


                this._class.viewEdit(this._context);
                this._context.getElementsByClassName('slide-context-secondary')[0].classList.add('no-transition');
                this._form.parentNode.style.height = this._form.clientHeight + 'px';
                this._form.style.zIndex = 5;
                this._slider.style.marginLeft = '-' + this._parent.clientWidth + 'px';

                var externalFormBackButtons = document.getElementsByClassName('external-form-back-action');

                for (var ii = 0; ii < externalFormBackButtons.length; ii++) {
                    externalFormBackButtons[ii].style.display = 'inline-block';
                }
            };
        }

        this.initRichText();

        //init newButtonSubmit
        document.getElementsByClassName('btn-new-wiki-submit')[0].onclick = function () {
            var textareas = document.getElementsByClassName('rich-text');

            for (var ii = 0; ii < textareas.length; ii++) {
                var ta = textareas[ii];
                var doc = document.createElement('div');
                doc.innerHTML = ta.value;
                var lists = [doc.getElementsByTagName('ul'), doc.getElementsByTagName('ol')];
                for (var uu = 0; uu < lists.length; uu++) {
                    var itemLists = lists[uu];
                    for (var kk = 0; kk < itemLists.length; kk++) {
                        var item = itemLists[kk];

                        if (item.previousElementSibling.tagName == 'LI') {
                            item.previousElementSibling.appendChild(item);
                        }
                    }
                }

                $(ta).summernote('code', doc.innerHTML);
            }


            return true;
        };
    } else if(this._type === 'modal-create-form') {
        this.initRichText();
    }
};


Wiki.prototype.initRichText = function() {
    var list = document.getElementsByClassName('rich-text');
    for(var ii = 0; ii < list.length; ii++) {
        var item = list[ii];

        var autocomplet = JSON.parse(document.getElementsByClassName('responsive-wiki-data-container')[0].getAttribute('data-piece'));
        var dataList = {};

        for(var key in autocomplet) {
            dataList[key + autocomplet[key].separator] = {
                src:autocomplet[key].src,
                insertTpl:'<a data-wiki-page="{{name}}" data-page-name="{{item}}" data-id="{{id}}" class="wiki-link" href="?wiki-page={{name}}">{{item}}</a>'
            };
        }
        
        dataList = this.applyFilter('wiki_rich_text_init', dataList);

        for(var keyName in dataList) {
            dataList[keyName].data = [];
            for(var index in dataList[keyName].src) {
                dataList[keyName].data.push(dataList[keyName].src[index].key);
            }
        }

        var matcher = new RegExp('(' + Object.keys(dataList).join('|') + '.*)');
        item._richText = true;
        $(item).summernote({
            height: 300,
            hint: {
                summernote:$(item),
                keys:Object.keys(dataList),
                words: dataList,
                match: matcher,
                key:'',
                search: function (keyword, callback) {
                    for(var ii = 0; ii < this.keys.length; ii++) {
                        if(keyword.indexOf(this.keys[ii]) == 0) {
                            this.key = this.keys[ii];
                            keyword = keyword.substr(this.key.length);
                            break;
                        }
                    }

                    callback($.grep(this.words[this.key].data, function (item) {
                        if(item.length == 0) return true;
                        else return item.indexOf(keyword) === 0;
                    }));
                },
                content: function (item) {
                    if(this.words[this.key].insertTpl) {
                            var text = this.words[this.key].insertTpl.split('{{item}}').join(item);
                            var key = item.split(' ').join('-').toLowerCase();
                            do {
                                var placeHolder = text.match('{{([a-zA-Z0-9]+)}}');
                                if(placeHolder) {
                                    if(this.words[this.key].src[key][placeHolder[1]]) {
                                        text = text.split('{{' + placeHolder[1] + '}}').join(this.words[this.key].src[key][placeHolder[1]]);
                                    } else {
                                        text = text.split('{{' + placeHolder[1] + '}}').join('');
                                    }
                                }
                            } while(placeHolder);

                            if(this.words[this.key].src[key].isHtml) {
                                window.setTimeout(function(summernote, markup) {
                                    summernote.summernote('pasteHTML', markup);
                                }, 1, this.summernote, text);

                                return '';
                            }

                        return text;
                    } else {
                        return item;
                    }
                }
            }
        });
     }

};

Wiki.prototype.viewEdit = function (context) {
    var slideContext = context.getElementsByClassName('slide-context-secondary')[0];
    slideContext.style.marginLeft = '-' + slideContext._moveDistance + 'px';
    var externalFormBackButtons = document.getElementsByClassName('external-form-back-action');

    for(var ii = 0; ii < externalFormBackButtons.length; ii++) {
        externalFormBackButtons[ii].style.display = 'inline-block';
    }

    var externalBackButtons = document.getElementsByClassName('external-back-action');

    for(var ii = 0; ii < externalBackButtons.length; ii++) {
        externalBackButtons[ii].style.display = 'none';
    }
};

Wiki.prototype.viewDetails = function (context) {
    var slideContext = context.getElementsByClassName('slide-context-secondary')[0];
    slideContext.style.marginLeft = 0;
};

Wiki.prototype.buildBreadCrump = function(type) {
    if(this._history[type].length > 1) {
        var breadCrump = [];
        for(var ii = 0; ii < this._history[type].length; ii++) {
            var link = this._history[type][ii];
            if(ii == this._history[type].length - 1) {
                var name = link;
                if(!(typeof link == 'string')) name = link.getAttribute('data-page-name');
                breadCrump.push('<span class="wiki-breadcrumb-current">'+ name + '</span>');
            } else {
                var callBack = '';
                if(link.hasAttribute('data-callback')) {
                    callBack = 'data-callback="' + link.getAttribute('data-callback') + '"';
                }
                breadCrump.push('<a ' + callBack + ' data-index="' + ii + '" data-wiki-page="' + link.getAttribute('data-wiki-page') + '" data-page-name="' + link.getAttribute('data-page-name') + '" data-id="' + link.getAttribute('data-id') + '" class="wiki-link" href="?wiki-page=' + link.getAttribute('data-wiki-page') + '">' + link.getAttribute('data-page-name') + '</a>');
            }
        }

        var breadCrumb = document.getElementsByClassName('wiki-breadcrumb')[0];
        breadCrumb.innerHTML = breadCrump.join('<span class="wiki-breadcrumb-separator">&gt;</span>');


        var lists = breadCrumb.getElementsByTagName('A');
        for(var ii = 0; ii < lists.length; ii++) {
            var item = lists[ii];
            item._context = this._context;
            item._class = this;
            item._detail = this._detail;
            item._parent = parent;
            item._slider = this._sliedeContext;

            if(item.hasAttribute('data-callback')) {
                item.onclick = function() {
                    var callBack = item.getAttribute('data-callback').split('.');
                    this._class._extensions[callBack[0]][callBack[1]]();
                    return false;
                }
            } else {
                item.onclick = this.openWikiDetailPage;
            }
        }

    }
};

Wiki.prototype.openWikiDetailPage = function() {
    if(this._clearHistory) {
        this._class._history.wiki = [];
    } else if(this.hasAttribute('data-index')) {
        var pos = this.getAttribute('data-index');
        this._class._history.wiki.splice(pos, this._class._history.wiki.length - pos);
    }
    document.getElementsByClassName('wiki-breadcrumb')[0].style.display = 'none';
    document.getElementsByClassName('wiki-breadcrumb')[0].innerHTML = '';
    var context = this._context;
    var that = this._class;
    var detail = this._detail;
    var parent = this._parent;
    var slide = this._slider;
    this._class.viewDetails(this._context);
    this._context.getElementsByClassName('slide-context-secondary')[0].classList.remove('no-transition');
    var content = this._detail.getElementsByClassName('wiki-page')[0];
    var loader = this._detail.getElementsByClassName('loader')[0];
    content.innerHTML = '';
    loader.style.display = 'block';
    var header = this._detail.getElementsByClassName('detail-header')[0];

    header.children[0].textContent = this.getAttribute('data-page-name');
    header.children[1].textContent = '';

    this._detail.style.zIndex = 5;
    // this._detail.style.opacity = 1;
    // this._parent.style.opacity = 0;
    this._slider.style.marginLeft = '-' + this._parent.clientWidth + 'px';
    var editLink = this._detail.getElementsByClassName('wiki-page-edit')[0];
    if(editLink) {
        editLink.setAttribute('data-id', this.getAttribute('data-id'));
    }

    this._class.applyFilter('wiki_open_details', {'id': this.getAttribute('data-id')});

    history.pushState(null, 'Wiki: ' + this.getAttribute('data-page-name'), '?wiki-page=' + this.getAttribute('data-wiki-page'));

    this._class._history.wiki.push(this);

    this._class.buildBreadCrump('wiki');

    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        method: 'post',
        data: {
            action: 'wiki_page',
            id: this.getAttribute('data-id'),
            security: document.getElementById('wiki-inner').getAttribute('data-nonce')
        },
        success: function(e) {
            var response = JSON.parse(e);
            loader.style.display = 'none';
            content.innerHTML = response.content;

            var links = context.getElementsByClassName('wiki-link');
            for(var ii = 0; ii < links.length; ii++) {
                var item = links[ii];
                item._context = context;
                item._class = that;
                item._detail = detail;
                item._parent = parent;
                item._slider = slide;
                item.onclick = that.openWikiDetailPage;
            }

            var externalBackButtons = document.getElementsByClassName('external-back-action');

            for(var ii = 0; ii < externalBackButtons.length; ii++) {
                externalBackButtons[ii].style.display = 'inline-block';
            }

            document.getElementsByClassName('wiki-breadcrumb')[0].style.display = 'block';

            return false;
        }
    });

    return false;
};

Wiki.prototype.loadWikiPageDetails = function(id) {
    var content = this._detail.getElementsByClassName('wiki-page')[0];
    var loader = this._detail.getElementsByClassName('loader')[0];
    content.innerHTML = '';
    loader.style.display = 'block';

    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        method: 'post',
        data: {
            action: 'wiki_page',
            id: id,
            security: document.getElementById('wiki-inner').getAttribute('data-nonce')
        },
        success: function(e) {
            var response = JSON.parse(e);
            loader.style.display = 'none';
            content.innerHTML = response.content;

            document.getElementsByClassName('wiki-breadcrumb')[0].style.display = 'block';

            return false;
        }
    });
};

Wiki.prototype.onlySlideToDetail = function(slideContext, calendarView, calendarDetailView) {
    if(document.getElementsByClassName('wiki-page-pre-fill').length > 0) {
        calendarDetailView.style.zIndex = 5;
        slideContext.style.marginLeft = '-' + calendarView.clientWidth + 'px';
        var alink = document.createElement('A');
        alink.setAttribute('data-page-name', this._context.getAttribute('data-page-name'));
        alink.setAttribute('data-wiki-page', this._context.getAttribute('data-wiki-page'));
        alink.setAttribute('data-id', this._context.getAttribute('data-id'));

        this._history.wiki.push(alink);
    }
};

Wiki.prototype.initWikiLinks = function(wikiView, wikiContext, calendarDetailView, calendarView, slideContext) {
    var wikiList = wikiView.getElementsByClassName('wiki-entries');
    for(var ii = 0; ii < wikiList.length; ii++) {
        wikiList[ii]._context = wikiContext;
        wikiList[ii]._class = this;
        wikiList[ii]._detail = calendarDetailView;
        wikiList[ii]._parent = calendarView;
        wikiList[ii]._slider = slideContext;
        wikiList[ii]._clearHistory = true;
        wikiList[ii].onclick = this.openWikiDetailPage;
    }
};