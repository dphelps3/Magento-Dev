var fluid = {
    Ajax : function(){
        jQuery("#loading").hide();
        var content = jQuery("#ajax-content").hide();
        jQuery("#toggle-ajax").bind("click", function(e) {
            if ( jQuery(this).is(".hidden") ) {
                jQuery("#ajax-content").empty();

                jQuery("#loading").show();
                jQuery("#ajax-content").load("", function() {
                    jQuery("#loading").hide();
                    content.slideDown();
                });
            }
            else {
                content.slideUp();
            }
            if ($(this).hasClass('hidden')){
                $(this).removeClass('hidden').addClass('visible');
            }
            else {
                $(this).removeClass('visible').addClass('hidden');
            }
            e.preventDefault();
        });
    },
Toggle : function(){
	var default_hide = {"dd": true, "dfd": true, "ws": true, "pr":true, "ep":true, "bp":true};
	jQuery.each(
		["dd", "dfd", "ws", "pr", "ep", "bp"],
		function() {
			var el = jQuery("#" + (this == 'accordion' ? 'accordion-block' : this) );
			if (default_hide[this]) {
				el.hide();
                jQuery("[id='toggle-"+this+"']").addClass("hidden")
			}
            jQuery("[id='toggle-"+this+"']")
			.bind("click", function(e) {
				if (jQuery(this).hasClass('hidden')){
                    jQuery(this).removeClass('hidden').addClass('visible');
					el.slideDown();
				} else {
                    jQuery(this).removeClass('visible').addClass('hidden');
					el.slideUp();
				}
				e.preventDefault();
			});
		}
	);
},
    Accordion: function(){
        jQuery("#accordion").accordion({
            'header': "h3.atStart"
        }).bind("accordionchangestart", function(e, data) {
                data.newHeader.css({
                    "font-weight": "bold",
                    "background": "#fff"
                });

                data.oldHeader.css({
                    "font-weight": "normal",
                    "background": "#eee"
                });
            }).find("h3.atStart:first").css({
                "font-weight": "bold",
                "background": "#fff"
            });
    }
}
jQuery(function ($) {
    if($("#accordion").length){fluid.Accordion();}
    if($("[id$='ajax']").length){fluid.Ajax();}
	if(jQuery("[id^='toggle']").length){fluid.Toggle();}
});