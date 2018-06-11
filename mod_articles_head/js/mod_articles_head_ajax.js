/**
	mod_articles_head AJAX Interface

	v 0.2
	Carsten Ruppert
	2018-06-08
*/
'use strict';
(function($) {

	$.ModIntroAJAX = function(options, module)
	{
		this.module = $(module);
		this.module.data('modintroajax', this);
		this._init(options);
	}

	$.ModIntroAJAX.Defaults = {
		evNamespace : '.modintro.ajax',
		html : {
			loading : '<div class="mod-intro-loading"><div class="sk-cube-grid"><div class="sk-cube sk-cube1"></div><div class="sk-cube sk-cube2"></div><div class="sk-cube sk-cube3"></div><div class="sk-cube sk-cube4"></div><div class="sk-cube sk-cube5"></div><div class="sk-cube sk-cube6"></div><div class="sk-cube sk-cube7"></div><div class="sk-cube sk-cube8"></div><div class="sk-cube sk-cube9"></div></div></div>'
		},
		request : {
			'option' : 'com_ajax',
			'module' : 'articles_head',
			'method' : 'getList',
			'format' : 'raw'
		}
	}

	$.ModIntroAJAX.prototype = {
		
		_init : function(options) 
		{
			this.opt = $.extend(true, {}, $.ModIntroAJAX.Defaults, options);
			this.setup();

			// Wenn ein AJAX Ereigniss eintritt wird Setup erneut ausgeführt.
			this.module.on('afterLoad', function() {
				this.setup();
			}.bind(this));
        },
        
        setup : function()
        {
            var trigger = this.module.find('[data-modintroajax]');

            for(var i = 0, len = trigger.length; i < len; i++) {
				trigger.one('click' + this.opt.evNamespace, function(ev) {
					this.sendRequest(ev.currentTarget);
				}.bind(this));
            }
		},
        
        postEffects : function(response, config)
        {
            var html  = $(response),
                items = html.find('.item');

			items.css({visibility : 'hidden', opacity : '0'});
			
            $(config.target).append(html);

            var animateIn = function() {
				var anim = this.data('modintroanim');
                this.one('webkitAnimatioEnd msAnimationEnd animationend', function(){
					var anim = $(this).data('modintroanim');
					$(this).removeClass(anim.class + ' ' + anim.name);
                });
                this.addClass(anim.class + ' ' + anim.name).css({visibility : '', opacity : '1'});
            }

			var item;
            for(var i = 0, len = items.length; i < len; i++)
            {
				item = items.eq(i);
				item.data('modintroanim', {class : config.aniclass, name : config.aniname});
				window.setTimeout(animateIn.bind(items.eq(i)), i * 100);
            }
        },

		sendRequest : function(trigger) 
		{
			var trigger = $(trigger),
				temp 	= trigger.parent(), // Hier wird die Ladeanzeige eingeblendet
				config 	= trigger.data('modintroajax');	// {"url":"<?php echo JUri::root();?>index.php","id":<?php echo $module->id;?>,"s":<?php echo $start;?>,"target":"#mod-intro-items-list-<?php echo $module->id;?>"}

			this.opt.request.modid = config.id; // Joomla Modul Id
			this.opt.request.start = config.s;  // Start
	
			trigger.remove();
			temp.html($(this.opt.html.loading));
            
            //$('html, body').stop().animate({ scrollTop : temp.offset().top }, {duration : 800} );

			$.ajax({
				url    : config.url,
				type   : 'POST',
				data   : this.opt.request,
				success: function (response) 
				{
					temp.remove();

                    if(config.animate) {
                        this.postEffects(response, config);
                    }
                    else {
                        $(config.target).append(response);
                    }

					this.module.triggerHandler('afterLoad');
				}.bind(this),
				error: function(response) {
					console.log(response);
				}
			});
		}
	} // prototype

	$.fn.modintroajax = function(options)
	{
		var self = $(this).data('modintroajax');

        if(!self) 
        {
			self = new $.ModIntroAJAX(options, this);
		}
		return self;
	}

})(jQuery);
