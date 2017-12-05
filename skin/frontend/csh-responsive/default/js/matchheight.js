jQuery(document).ready(function($) {

	function resizeDiv() {

	    var flexsize_product = -1;
	    var flexsize_pricebox = -1;
	    var flexsize_catbox = -1;
	    var flexsize_subcatthumb = -1;

	    $('.product-name').each(function() {
	      flexsize_product = flexsize_product > $(this).height() ? flexsize_product : $(this).height();
	    });

	    $('.price-box').each(function() {
	      flexsize_pricebox = flexsize_pricebox > $(this).height() ? flexsize_pricebox : $(this).height();
	    });

	    $('.cat-box').each(function() {
	      flexsize_catbox = flexsize_catbox > $(this).height() ? flexsize_catbox : $(this).height();
	    });

	    $('.subcat_thumb').each(function() {
	      flexsize_subcatthumb = flexsize_subcatthumb > $(this).height() ? flexsize_subcatthumb : $(this).height();
	    });

	  
      $('.product-name').height(flexsize_product);
      $('.price-box').height(flexsize_pricebox);
      $('.cat-box').height(flexsize_catbox);
      $('.subcat_thumb').height(flexsize_subcatthumb);
	}

	resizeDiv();
	$(window).resize(function() {
		$('.product-name').removeAttr('style');
		$('.price-box').removeAttr('style');
		$('.cat-box').removeAttr('style');
		$('.subcat_thumb').removeAttr('style');
		resizeDiv();
	});

});