/**
 * @package        HEAD. Article Module
 * @version        1.7.4
 * 
 * @author         Carsten Ruppert <webmaster@headmarketing.de>
 * @link           https://www.headmarketing.de
 * @copyright      Copyright © 2018 HEAD. MARKETING GmbH All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
'use strict';
(function($) {

	$.ModIntroVideo = function(options, module)
	{
		this.module = $(module);
		this.module.data('modintrovideo', this);
		this._init(options);
	}

	$.ModIntroVideo.Defaults = {
		evNamespace 	: '.modintro.video',
		allowTouch 		: false,
		itemSelector	: 'article'
	}

	$.ModIntroVideo.prototype = {
		
		_init : function(options) 
		{
			this.opt = $.extend(true, {}, $.ModIntroVideo.Defaults, options);
			
			this.setup();

			// Wenn ein AJAX Ereigniss eintritt wird Setup erneut ausgeführt.
			this.module.on('afterLoad', function(){
				this.setup();
			}.bind(this));
		},

		setup : function() 
		{
			var list = this.module.find(this.opt.itemSelector),
				item;

			for(var i = 0, len = list.length; i < len; i++)
			{
				item = list.eq(i);
				
				// -- Intro-Video Setup
				if(item.children('.item-introvideo').length)
				{
					item.off(this.opt.evNamespace); // Wenn setup() erneut aufgerufen wird schalten wir zuerst alle Event-Überwachung aus.
					
					item.on('mouseleave' + this.opt.evNamespace, function(ev) {
						this.hideIntroVideo(ev.currentTarget);
					}.bind(this));
					
					item.triggerHandler('mouseleave');
				}
			}

			if(list.find('.item-introvideo.with-full-video').length)
			{
				this.bindLightboxes();
			}

		},

		bindLightboxes : function()
		{
			if(!$.featherlight) {
	
				if($app && $app.extensions.list.featherlight && $app.extensions.list.featherlight.autoload)
				{
					if(console) console.log('mod_articles_head: Warte auf Featherlight.js von $app.');
					$($app).one('featherlightReady', function() {
						this.bindLightboxes();
					}.bind(this));
				}
				else {
					if(console) console.log('mod_articles_head: Featherlight.js ist nicht installiert.');
				}
				return;
			}

			var list = this.module.find('.item-introvideo.with-full-video');

			if(list.length) {

				var video;
				for(var i = 0, len = list.length; i < len; i++)
				{
					video = list.eq(i);
					video.off(this.opt.evNamespace);
					video.on('click' + this.opt.evNamespace, function(ev) {
						ev.preventDefault();
						var tpl =   '<div class="video-player">' +
									'   <video class="featherlight-video" autoplay controls volume="70" type="video/mpeg">' +
									'	   <source src="' + $(this).data('fullvideo') + '" type="video/mp4" />' +
									'   </video>' +
									'</div>';
			
						$.featherlight(tpl, {openSpeed : 750});
					});
				}
			}
		},

		hideIntroVideo : function(item) 
		{
			var item 	= $(item),
				wrapper = item.find('.item-introvideo');

			if(wrapper.length)
			{
				wrapper.removeClass('in');

				var video = wrapper.find('video');
				
				if(video.length) { // Es muss nicht zwingend ein Video da sein.
					video = video.get(0);
					if(! $(video).attr('autoplay')) {
						video.pause();
						if(video.currentTime) video.currentTime = 0;  /* Wenn Internet Explorer 11 noch keine Metadaten geladen hat bekommen wir sonst beim ersten abfeuern von hideVideo einen InvalidStateError */
					}
				
					if(!this.opt.allowTouch) {
						item.one('touchstart' + this.opt.evNamespace, function(ev) {
							/**
								Wenn wir nicht wollen, dass Touch-Devices das Vorschauvideo zu sehen bekommen – weil das ein technisches und auch „useability” Problem sein kann, weil das Video erst ausgeblendet wird, 
								wenn der <article> den Fokus verliert (onmouseleave gefeuert wird).
							*/
							$(this).off('mouseover' + this.opt.evNamespace); /* touchstart findet vor mouseover statt, also schalten wir hier mouseover einfach ab. */
						});
					}

					item.one('mouseover' + this.opt.evNamespace, function(ev) {
						this.showIntroVideo(ev.currentTarget);
					}.bind(this));
				}

			}
		},

		showIntroVideo : function(item) {
			var item 	= $(item),
				wrapper = item.find('.item-introvideo');

			if(wrapper.length) {
				var video = wrapper.find('video');
				if(video) {
					wrapper.addClass('in');
					video.get(0).play();
				}
			}
		}

	} // prototype

	$.fn.modintrovideo = function(options)
	{
		var self = $(this).data('modintrovideo');

		if(!self)
		{
			self = new $.ModIntroVideo(options, this);
		}
		return self;
	}

})(jQuery);
