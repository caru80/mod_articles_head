<?php
/**
	mod_articles_head
*/
defined('_JEXEC') or die;

$layoutConf = (object)array(
				"class_sfx"		=> "intro-videos",
				"item_layout" 	=> "videos_item"
				);

require JModuleHelper::getLayoutPath('mod_articles_head', 'default'); 
?>
<script>
'use strict';
(function($) {
	$(function() {
		var list = $('article', '#mod-intro-<?php echo $module->id;?>');

		if(!list.length) return;

		var showVideo = function() {
			var wrapper = $(this).find('.teaser-video.with-full');

			if(wrapper.length) 
			{
				wrapper.addClass('in');
				wrapper.find('video').get(0).play();
			}
		}
		var hideVideo = function() {
			var wrapper = $(this).find('.teaser-video.with-full');

			if(wrapper.length) 
			{
				wrapper.removeClass('in');

				var video = wrapper.find('video').get(0);
				if(! $(video).attr('autoplay')) {
					video.pause();
					video.currentTime = 0;
				}
				
				$(this).one('mouseover.modintro', function(ev) 
				{
					showVideo.call(this);
				});
			}
		}

		list.each(function() 
		{
			var article = $(this);

			article.on('mouseleave.modintro', function(ev) {
				hideVideo.call(this);
			});
			article.triggerHandler('mouseleave');
		});
	});
})(jQuery);
</script>