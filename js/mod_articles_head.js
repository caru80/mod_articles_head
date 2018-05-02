(function($){
    /*
        mod_articles_head
        AJAX Interface
    */
    function modArticlesHeadLoadmore(trigger)
    {
        var conf    = trigger.data('modintroajax'),
            loading = $('<div class="mod-intro-loading"><div class="sk-cube-grid"><div class="sk-cube sk-cube1"></div><div class="sk-cube sk-cube2"></div><div class="sk-cube sk-cube3"></div><div class="sk-cube sk-cube4"></div><div class="sk-cube sk-cube5"></div><div class="sk-cube sk-cube6"></div><div class="sk-cube sk-cube7"></div><div class="sk-cube sk-cube8"></div><div class="sk-cube sk-cube9"></div></div></div>'),
            temp    = trigger.parent(),
            request = {
                'option' : 'com_ajax',
                'module' : 'articles_head',
                'method' : 'getList',
                'modid'  : conf.id,
                'start'  : conf.s,
                'format' : 'raw'
                };

        trigger.remove();
        temp.html(loading);

        $.ajax({
            url    : conf.url,
            type   : 'POST',
            data   : request,
            success: function (response) {
                temp.remove();
                $(conf.target).append(response);
                
                modArticlesHeadAJAXTriggers();

                $('#mod-intro-' + conf.id).triggerHandler('afterLoad', conf); // Wenn in den neu geladenen Sachen Scripte gestartet werden müssen, oder so. 
                /*
                    Bswp: 
                    $('.mod-intro').on('afterLoad', function(ev, conf){
                        $module = $(this);

                        $neuerWrapper   = ('#intro-' + conf.id + '-items-' + conf.s);
                        $neueItems      = $neuerWrapper.find('article');

                        // z.B. Spaltenhöhe mit $app.equalColumns:
                        $app.equalColumns.reset();
                        $app.equalColumns.init();
                    });
                */
            },
            error: function(response) {
                console.log(response);
            }
        });
    }
    
    function modArticlesHeadAJAXTriggers()
    {
        $('[data-modintroajax]').each(function(){
            var trigger = $(this);

            trigger.off('.modintro');

            trigger.on('click', function() {
               modArticlesHeadLoadmore($(this));
            });
        });
    }

    $(function(){
        modArticlesHeadAJAXTriggers();  
    });

})(jQuery);