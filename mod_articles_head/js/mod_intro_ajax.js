/**
 * @package        HEAD. Article Module
 * @version        1.8.5
 * 
 * @author         Carsten Ruppert <webmaster@headmarketing.de>
 * @link           https://www.headmarketing.de
 * @copyright      Copyright © 2018 HEAD. MARKETING GmbH All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
		},
		ajaxConfig 		: null,
		scroll : {
			enabled  : true,
			duration : 1000,
			offsetQuery : ''
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
				trigger.eq(i)
				.off(this.opt.evNamespace)
				.one('click' + this.opt.evNamespace, function(ev) 
				{
					this.sendRequest(ev.currentTarget);
				}.bind(this));
			}

			this.module.find('.set-filters')
				.off(this.opt.evNamespace)
				.on('click' + this.opt.evNamespace, function() 
				{
					this.applyFitlerGroups();
				}.bind(this));

			this.module.find('.reset-filters')
				.off(this.opt.evNamespace)
				.on('click' + this.opt.evNamespace, function() 
				{
					this.resetFilterGroups();
				}.bind(this));
		},
		
		postEffects : function(response)
		{
			var html  = $(response),
				items = html.find('.item');

			items.css({visibility : 'hidden', opacity : '0'});
			
			if(this.opt.ajaxConfig.replace) {
				$(this.opt.ajaxConfig.target).html(html);
			}
			else {
				$(this.opt.ajaxConfig.target).append(html);
			}

			if(this.opt.scroll.enabled)
			{
				this.scrollToNewItems();
			}

			var animateIn = function() {
				var anim = this.data('modintroanim');
				this.one('webkitAnimatioEnd msAnimationEnd animationend', function(){
					var a = $(this).data('modintroanim');
					$(this).removeClass(a.class + ' ' + a.name);
				});
				if(anim) {
					this.addClass(anim.class + ' ' + anim.name).css({visibility : '', opacity : '1'});
				}
			}

			var item;
			for(var i = 0, len = items.length; i < len; i++)
			{
				item = items.eq(i);
				item.data('modintroanim', {class : this.opt.ajaxConfig.aniclass, name : this.opt.ajaxConfig.aniname});
				window.setTimeout(animateIn.bind(items.eq(i)), i * 100);
			}
		},


		setFilterGroupValues : function(group) 
		{
			let data 	= $(group).data('filtergroup'),
				values  = new Array();
			
			$('[name="' + data.field + '"]', group).each(function() 
			{
				switch(this.nodeName.toLowerCase())
				{
					case 'input' :
						if(this.checked && this.value != '') values[values.length] = this.value;
					break;
	
					case 'select' :
						for(let i = 0, len = this.options.length; i < len; i++) {
							if(this.options[i].selected && this.options[i].value != '') values[values.length] = this.options[i].value;
						}
					break;
				}
			});
			this.opt.ajaxConfig[data.name] = values;
		},


		resetFilterGroups : function()
		{
			this.module.triggerHandler('resetFilters');

			this.opt.ajaxConfig.catid 	= [];
			this.opt.ajaxConfig.tag 	= [];
			
			this.module.find('[data-filtergroup]').each(function() 
			{
				let data = $(this).data('filtergroup');

				$(this).find('[name="' + data.field + '"]').each(function()
				{
					switch(this.nodeName.toLowerCase())
					{
						case 'input' :
							if(this.checked) this.checked = false;
						break;
		
						case 'select' :
							for(let i = 0, len = this.options.length; i < len; i++) {
								if(this.options[i].selected) this.options[i].selected = false;
							}
						break;
					}
				});
			});

			this.sendRequest();
		},


		applyFitlerGroups : function()
		{
			this.opt.ajaxConfig.s 		= 0;
			this.opt.ajaxConfig.replace = true; // Beiträge im Modul ersetzen
			this.opt.ajaxConfig.filter  = true; // Ein Filter-Link wurde angeklickt, und dieser soll nicht aus dem DOM entfernt werden.
			
			let self = this;

			this.module.find('[data-filtergroup]').each(function() 
			{
				self.setFilterGroupValues(this);
			});

			this.sendRequest();
		},

		scrollToNewItems : function() 
		{
			let wrappers = $(this.opt.ajaxConfig.target).find('.async'),
				latest 	 = wrappers.eq(wrappers.length -1),
				y 		 = latest.offset().top;

			if(this.opt.scroll.offsetQuery != '') 
			{
				let el = $(this.opt.scroll.offsetQuery);
				if(el.length) {
					y -= el.outerHeight();
				}
			}

			$('html, body').stop().animate({ scrollTop : y }, {
				duration 	: this.opt.scroll.duration,
				easing 		: 'swing'
			});
		},

		sendRequest : function(trigger) 
		{
			if(trigger) 
			{
				trigger = $(trigger);

				this.opt.ajaxConfig = $.extend({}, this.opt.ajaxConfig, trigger.data('modintroajax'));
			}
			

			this.opt.request.modid = this.opt.ajaxConfig.id; // Joomla Modul Id
			this.opt.request.start = this.opt.ajaxConfig.s;  // Start
			
			// Kategorie-Filter
			if(this.opt.ajaxConfig.catid) 
			{
				this.opt.request.catid = this.opt.ajaxConfig.catid;
			}

			// Schlagorte Filter
			if(this.opt.ajaxConfig.tag)
			{
				this.opt.request.tag = this.opt.ajaxConfig.tag;
			}
 
			let temp;  // Hier wird die Ladeanzeige eingeblendet
			if(this.opt.ajaxConfig.replace) 
			{
				temp = $(this.opt.ajaxConfig.target);
			}
			else if(trigger)
			{
				temp = trigger.parent();
			}

			// Wurde ein Filter-Link angeklickt?
			if(!this.opt.ajaxConfig.filter && trigger) trigger.remove();

			// Ladeanzeige einhängen
			let indicator = $(this.opt.html.loading);
			temp.html(indicator);

			$.ajax({
				url		: this.opt.ajaxConfig.url,
				type 	: 'POST',
				data 	: this.opt.request,
				success: function (response) 
				{
					indicator.remove();

					if(this.opt.ajaxConfig.animate) {
						this.postEffects(response);
					}
					else {
						if(this.opt.ajaxConfig.replace) {
							$(this.opt.ajaxConfig.target).html(response);
						}
						else {
							$(this.opt.ajaxConfig.target).append(response);
						}

						if(this.opt.scroll.enabled)
						{
							this.scrollToNewItems();
						}
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
