document.getElementById('wiki-search').onkeyup = function() {
    if(this.value.length > 3) {
        var container = document.getElementById('wiki-search-result-box');
        container.getElementsByClassName('loader')[0].style.display = 'block';
        container.style.display = 'block';

        var searchIn = [];
        var filter = document.getElementsByClassName('search-filter');
        for(var ii = 0; ii < filter.length; ii++) {
            if(filter[ii].checked) {
                searchIn.push(filter[ii].value);
            }
        }

        $.ajax({
            url:'/wp-admin/admin-ajax.php',
            method:'post',
            data:{
              action:'wiki_search',
                search:this.value,
                filter: searchIn
            },
            success:function(response) {
                var searchResults = document.getElementById('wiki-search-result');
                container.getElementsByClassName('loader')[0].style.display = 'none';
                searchResults.innerHTML = response;
                searchResults.style.display = 'block';

                var wikiContext = wiki._context;
                var wikiView = wikiContext.getElementsByClassName('wiki-view')[0];
                var detailView = wiki._detail;
                var slideContext = wiki._sliedeContext;

                wiki.initWikiLinks(wikiView, wikiContext, detailView, wikiView, slideContext);
            }
        });
    } else {
        var searchResults = document.getElementById('wiki-search-result');
        searchResults.style.display = 'none';
        document.getElementById('wiki-search-result-box').style.display = 'none';
    }
};

var list = document.getElementsByClassName('search-filter');

for(var ii = 0; ii < list.length; ii++) {
    list[ii].onclick = function() {
        document.getElementById('wiki-search').onkeyup();
    };
}