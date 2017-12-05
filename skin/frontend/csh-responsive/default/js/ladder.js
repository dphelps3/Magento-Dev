/* ladder.js version 3.0 -- updated by davidP on 09/30/16 */

var prev_value = "",
	finish_code = "",
	wheel_type = "",
	curve9016_qty = 0,
	curve90_qty = 0,
	curve135_qty = 0,
	rail4ft_qty = 0,
	rail6ft_qty = 0,
	rail8ft_qty = 0,
	v_bracket_qty = 0,
	h_bracket_qty = 0,
	splice_qty = 0,
	tap_qty = 1,
	tread_qty = 0,
	stop_qty = 0,
	ballstop_qty = 0,
	roller_type = "",
	species_code = "",
	ladder_height = "",
	ladder_width = "",
	splicekits_code = "",
	handrail_code = "",
	top_rung_species = "",
	top_rung_qty = 0,
	rung_supports_qty = 0,
	old_img = "",
	img_prefix = "http://cdn.cshardware.com/media/wysiwyg/QGDHTML",
  // change back to img_prefix = "images/QGDHTML",
	img_postfix = "_a.jpg",
  // change back to img_postfix = "_a.jpg",
	page_update = false,
	cookie_session = "",
	total_price = 0.0,
	sku_info = {},
	skus_to_load = []

function disable_enter_key(e) {
	var key;
	if(window.event)
		key = window.event.keyCode; //IE
	else
		key = e.which; //firefox

	return (key != 13);
}

function create_cookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	//alert(name+"="+value+expires+"; path=/");
	document.cookie = name+"="+value+expires+"; path=/";
}

function read_cookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function erase_cookie(name) {
	create_cookie(name,"",-1);
}

function split_parameters(param_list) {
	param_list = decodeURIComponent(param_list).split("&")
	var params = {}
	var param = ""
	for (var i=0; i<param_list.length; i++) {
		param = param_list[i].split("=")
		params[param[0]] = encodeURIComponent(param[1])
	}
	return params
}

function hover(obj) {
//	page_update = false
//	var img = obj.parentNode.getElementsByTagName("img")[0]
//	old_img = img.src
//	if (obj.htmlFor) {
//		obj = document.getElementById(obj.htmlFor)
//	}
//	switch(obj.name) {
//	case "finish":
//		img.src = img_prefix + obj.value + img_postfix
//		break;
//	case "wheel":
//		img.src = img_prefix + "wheel" + obj.value +finish_code + img_postfix
//		break;
//	case "roller":
//		img.src = (obj.value) ? img_prefix + "roller" + obj.value +finish_code + img_postfix : "images/blank.gif"
//		break;
//	case "species":
//		img.src = (obj.value) ? img_prefix + obj.value + img_postfix : "images/blank.gif"
//		break;
//	case "top_rungs":
//		img.src = (obj.value) ? img_prefix + "610" + obj.value + img_postfix : "images/blank.gif"
//		break;
//	}
}

function hoverout(obj) {
//	if (!page_update) {
//		var img = obj.parentNode.getElementsByTagName("img")[0]
//		img.src = old_img
//	}
}

// Makes the AJAX call for queued skus and writes received data to the appropriate <tr>
function flush_pending_skus() {
	var str = '', sku
	while (skus_to_load.length > 0) { // build the AJAX search string
		sku = skus_to_load.pop()
		//str += (str ? ("|or|searchexact~p.sku~" + sku) : ("searchexact~p.sku~" + sku))
		str += (str ? ("|" + sku) : (sku))
	}
	if (str) { // if a search string was built, make the AJAX call below
		jQuery("div.progress-image").show();
		jQuery.get("/ssajax", {rb_id: "0AFBE47B797546FFB51B455F9E12553A", sku: str}, function(data) {
			jQuery("record", data).each(function () {
				var record = jQuery(this)
				sku = record.find("sku").text()
				sku_info[sku] = {}
				sku_info[sku].p_key = record.find("p_key").text()
				sku_info[sku].nm = record.find("nm").text()
				sku_info[sku].ds = jQuery('<div />').html(record.find("ds").text()).text()
				sku_info[sku].retail_price = parseFloat(record.find("retail_price").text())
				sku_info[sku].image = record.find("thumb").text().replace("_t.jpg","")
				sku_info[sku].qty = ''
			})
			jQuery(document).ajaxStop(function(){
                             jQuery('div.progress-image').hide();
                         });
			update_cart()
		})
	}
}

function get_tr_from_sku(sku, qty) {
    if(!qty){
        qty = 0;
    }
	if (!sku_info[sku]) { // if the sku hasn't been stored, we need to get it
		// queue the sku for the AJAX call
		skus_to_load.push(sku)
		// return an empty <tr> that will be populated once the AJAX call is made
		if (qty > 1) {
			return '<tr><td class="qty_td">' + qty + ' x </td><td class="sku_td">' + sku + '</td><!--<td>Loading product info...</td>--><td>&nbsp;</td></tr>'
		} else {
			return '<tr><td></td><td class="sku_td">' + sku + '</td><!--<td>Loading product info...</td>--><td>&nbsp;</td></tr>'
		}
	} else {
		qty = parseInt(qty)
		var o_price = sku_info[sku].retail_price
        if(!o_price){
            o_price = 0;
        }
		var price = o_price.toFixed(2)
		var t_price = (o_price*qty).toFixed(2)
		var key =  sku_info[sku].p_key
		total_price += o_price * qty
		if (qty > 1) {
			return '<tr><td class="qty_td">' + qty + ' x </td><td class="sku_td"><!--<p>-->' + sku + '<!--</p><p class="unit_price">--><br />@ ' + (o_price ? '$'+price : 'Not avail.') + '<!--</p>--></td><!--<td class="desc_td">' + sku_info[sku].nm + '</td>--><td class="price_td">' + (o_price ? '$'+t_price : 'Not avail.') + '<input type="hidden" name="keys" value="' + key + '" /><input type="hidden" name="qty[' + key + ']" value="' + qty + '" /></td></tr>'
		} else {
			return '<tr><td></td><td class="sku_td">' + sku + '</td><!--<td class="desc_td">' + sku_info[sku].nm + '</td>--><td class="price_td">' + (o_price ? '$'+price : 'Not avail.') + '<input type="hidden" name="keys" value="' + key + '" /><input type="hidden" name="qty[' + key + ']" value="' + qty + '" /></td></tr>'
		}
	}
}

function set_radio_value(set_name, val) {
	radio_set = document.getElementsByName(set_name)
	for (var i=0; i<radio_set.length; i++) {
		if (radio_set[i].value == val) {
			radio_set[i].checked = true
		} else {
			radio_set[i].checked = false
		}
	}
}

function reset_page() {
	erase_cookie("shoppinglist")
	window.location.reload()
}

function load_list(params) {
	set_radio_value("finish", params["finish_code"])
	set_radio_value("wheel", params["wheel_type"])
	set_radio_value("roller", params["roller_type"])
	document.getElementById("8ft").value = params["rail8ft_qty"]
	document.getElementById("6ft").value = params["rail6ft_qty"]
	document.getElementById("4ft").value = params["rail4ft_qty"]
	document.getElementById("90degree16").value = params["curve9016_qty"]
	document.getElementById("90degree").value = params["curve90_qty"]
	document.getElementById("135degree").value = params["curve135_qty"]
	document.getElementById("vertical").value = params["v_bracket_qty"]
	document.getElementById("horizontal").value = params["h_bracket_qty"]
	document.getElementById("stop").value = params["stop_qty"]
	document.getElementById("ballstop").value = params["ballstop_qty"]
	document.getElementById("splice").value = params["splice_qty"]
	document.getElementById("tap").value = params["tap_qty"]
	set_radio_value("species", params["species_code"])
	set_radio_value("height", params["ladder_height"])
	set_radio_value("width", params["ladder_width"])
	set_radio_value("handrail", params["handrail_code"])
	set_radio_value("splicekits", params["splicekits_code"])
	document.getElementById("tread").value = params["tread_qty"]
	set_radio_value("top_rungs", params["top_rung_species"])
	document.getElementById("top_rung_qty").value = params["top_rung_qty"]
	document.getElementById("rung_supports").value = params["rung_supports_qty"]
}

function update_cookie() {
	var cookie_string = ""
	cookie_string += "finish_code=" + finish_code + "&"
	cookie_string += "wheel_type=" + wheel_type + "&"
	cookie_string += "roller_type=" + roller_type + "&"
	cookie_string += "rail4ft_qty=" + rail4ft_qty + "&"
	cookie_string += "rail6ft_qty=" + rail6ft_qty + "&"
	cookie_string += "rail8ft_qty=" + rail8ft_qty + "&"
	cookie_string += "curve9016_qty=" + curve9016_qty + "&"
	cookie_string += "curve90_qty=" + curve90_qty + "&"
	cookie_string += "curve135_qty=" + curve135_qty + "&"
	cookie_string += "v_bracket_qty=" + v_bracket_qty + "&"
	cookie_string += "h_bracket_qty=" + h_bracket_qty + "&"
	cookie_string += "splice_qty=" + splice_qty + "&"
	cookie_string += "tap_qty=" + tap_qty + "&"
	cookie_string += "stop_qty=" + stop_qty + "&"
	cookie_string += "ballstop_qty=" + ballstop_qty + "&"
	cookie_string += "species_code=" + species_code + "&"
	cookie_string += "ladder_height=" + ladder_height + "&"
	cookie_string += "ladder_width=" + ladder_width + "&"
	cookie_string += "splicekits_code=" + splicekits_code + "&"
	cookie_string += "handrail_code=" + handrail_code + "&"
	cookie_string += "tread_qty=" + tread_qty + "&"
	cookie_string += "top_rung_species=" + top_rung_species + "&"
	cookie_string += "top_rung_qty=" + top_rung_qty + "&"
	cookie_string += "rung_supports_qty=" + rung_supports_qty
	create_cookie("shoppinglist", cookie_string, 3)
}

function update_cart() {
	var cart_list = document.getElementById("cart_list"),
		brackets = 0,
		splice_kits = 0,
		stops = 0
	total_price = 0.0;
	var total_quantity=0;

	cart_text = '<form onkeypress="return false" action="http://' + window.location.hostname + '/addcart?'
	cart_text += 'type=v200add&o_url=https%3A%2F%2F' + window.location.hostname.replace(".","%2E") + '&createsessioncookie=1&noredirect=1&'
	cart_text += "a_name=" + cookie_session["a_name"] + "&"
	cart_text += "c_Lastname=" + cookie_session["c_Lastname"] + "&"
	cart_text += "c_firstName=" + cookie_session["c_firstName"] + "&"
	cart_text += "c_userName=" + cookie_session["c_userName"] + "&"
	cart_text += "c_id=" + cookie_session["c_id"] + "&"
	cart_text += "a_id=" + cookie_session["a_id"] + "&"
	cart_text += "s_url=" + cookie_session["s_url"] + "&"
	cart_text += "s_key=" + cookie_session["s_key"] + "&"
	cart_text += "l_ws_key=" + cookie_session["l_ws_id"] + "&"
	cart_text += "sc_id=" + cookie_session["s_key"]
	cart_text += '" method="post">'
	cart_text += '<table id="order_ladder_table" class="table table-condensed"><caption class="order_caption">Current order</caption><tr><th class="qty_th" scope="col">QTY</th><th class="sku_th" scope="col">SKU</th><!--<th class="desc_th" scope="col">Description</th>--><th class="price_th" scope="col">PRICE</th></tr>'
	if (finish_code && wheel_type && roller_type && ladder_width) {
		if (ladder_width == "16"){
			cart_text += get_tr_from_sku("QG." + roller_type + wheel_type + "0." + finish_code, 1);
			total_quantity+=1;

		} else if (ladder_width == "20"){
			cart_text += get_tr_from_sku("QG." + roller_type + wheel_type + "0-20." + finish_code, 1);
			total_quantity+=1;
		}

	}

	if (species_code != "NO" && ladder_height) {
		if (ladder_width == "20"){
			cart_text += get_tr_from_sku("QG.60" + ladder_height + "-20" + "." + species_code, 1);
			total_quantity+=1;
		} else {
			cart_text += get_tr_from_sku("QG.60" + ladder_height + "." + species_code, 1);
			total_quantity+=1;
		}
	}
	if (handrail_code && handrail_code != "none") {
		cart_text += get_tr_from_sku("QG.6309." + handrail_code, 1);
		total_quantity+=1;
	}
	if (species_code && ladder_height && ladder_height != "08") {
		var rungs = ((ladder_height == "09") ? 1 : 2)
	} else {
		var rungs = 0
	}
	if (rungs + rung_supports_qty) {
		if (ladder_width == "20"){
			cart_text += get_tr_from_sku("QG.620-20." + ((finish_code == "02") ? "02" : "08"), rungs + rung_supports_qty);
			total_quantity+=(rungs + rung_supports_qty);
		} else {
			cart_text += get_tr_from_sku("QG.620." + ((finish_code == "02") ? "02" : "08"), rungs + rung_supports_qty);
			total_quantity+=(rungs + rung_supports_qty);
		}
	}
	if (finish_code) {

		if (curve9016_qty) {
			cart_text += get_tr_from_sku("QG.40CRV16." + finish_code, curve9016_qty);
			total_quantity+=curve9016_qty;
		}
		if (curve90_qty) {
			cart_text += get_tr_from_sku("QG.40CRV." + finish_code, curve90_qty);
			total_quantity+=curve90_qty;
		}
		if (curve135_qty) {
			cart_text += get_tr_from_sku("QG.40CRV135." + finish_code, curve135_qty);
			total_quantity+=curve135_qty;
		}
		if (rail8ft_qty) {
			cart_text += get_tr_from_sku("QG.4008." + finish_code, rail8ft_qty);
			total_quantity+=rail8ft_qty;
		}
		if (rail6ft_qty) {
			cart_text += get_tr_from_sku("QG.4006." + finish_code, rail6ft_qty);
			total_quantity+=rail6ft_qty;
		}
		if (rail4ft_qty) {
			cart_text += get_tr_from_sku("QG.4004." + finish_code, rail4ft_qty);
			total_quantity+=rail4ft_qty;
		}
		if (v_bracket_qty) {
			brackets += v_bracket_qty
			cart_text += get_tr_from_sku(((roller_type == "2" || roller_type == "" || roller_type == "7") ? "QG.201." + finish_code : "QG.301." + finish_code), v_bracket_qty);
			total_quantity+=v_bracket_qty;
		}
		//if ((roller_type == "2" || roller_type == "") && h_bracket_qty) {
		//	brackets += h_bracket_qty
		//	cart_text += get_tr_from_sku("QG.202." + finish_code, h_bracket_qty)
		//}
		if (h_bracket_qty) {
			brackets += h_bracket_qty
			cart_text += get_tr_from_sku(((roller_type == "2" || roller_type == "" || roller_type == "7") ? "QG.202." + finish_code : "QG.302." + finish_code), h_bracket_qty);
			total_quantity+=h_bracket_qty;
		}
		if (stop_qty) {
			stops += stop_qty
			cart_text += get_tr_from_sku("QG.40." + finish_code, stop_qty);
			total_quantity+=stop_qty;
		}
		if (ballstop_qty) {
			stops += ballstop_qty
			cart_text += get_tr_from_sku("QG.401." + finish_code, ballstop_qty);
			total_quantity+=ballstop_qty;
		}
	}
	// if (jQuery("input:radio[id='splicekits_none']").not(":checked")) {
	// 	if (splice_qty) {
	// 		splice_kits += splice_qty
	// 		cart_text += get_tr_from_sku("QG.41", splice_qty);
	// 		total_quantity+=splice_qty;
	// 		if (tap_qty) {
	// 			cart_text += get_tr_from_sku("QG.TAP1420", tap_qty);
	// 			total_quantity+=tap_qty;
	// 		}
	// 	}
	// }
	// console.log(splicekits_code);
	// if (splicekits_code && splicekits_code != "none") {
	// 	if (splice_qty) {
	// 		cart_text += get_tr_from_sku("QG.41", splice_qty);
	// 		total_quantity+=splice_qty;
	// 	if (tap_qty) {
	// 			cart_text += get_tr_from_sku("QG.TAP1420", tap_qty);
	// 			total_quantity+=tap_qty;
	// 		}
	// 	}
	// 	cart_text += get_tr_from_sku("QG.6309." + handrail_code, 1);
	// 	total_quantity+=1;
	// }

	if (splice_qty) {
			cart_text += get_tr_from_sku("QG.41", splice_qty);
			total_quantity+=splice_qty;
		}

	if (tap_qty) {
			cart_text += get_tr_from_sku("QG.TAP1420", tap_qty);
			total_quantity+=tap_qty;
		}

	if (tread_qty) {
		cart_text += get_tr_from_sku("QG.61", tread_qty);
		total_quantity+=tread_qty;
	}
	if (top_rung_species && top_rung_qty) {
		cart_text += get_tr_from_sku("QG.610." + top_rung_species, top_rung_qty);
		total_quantity+=top_rung_qty;
	}
	cart_text += '<tr><td></td><!--<td></td>--><td class="total_td text_bold">TOTAL:</td><td class="total_price_td text_bold">$' + total_price.toFixed(2) + '</td></tr>'

	cart_text += '<tr><td colspan="3" ><button type="reset" onclick="reset_page()" class="btn btn-sm btn-clear pull-left" ><span class="glyphicon glyphicon-refresh"></span>Clear All</button>'
	if (total_price > 1) {
		cart_text += '<button type="submit" class="btn btn-sm btn-primary pull-right" ><span class="glyphicon glyphicon-shopping-cart"></span>Add to Cart</button>'
	}
	cart_text += '</td></tr></table></form>';


	var suggest_bracket_qty = 2*(curve9016_qty + curve90_qty + curve135_qty) + Math.round((rail8ft_qty*8*12 + rail6ft_qty*6*12 + rail4ft_qty*4*12)/32)
	var suggest_splice_qty = (curve9016_qty + curve90_qty + curve135_qty + rail8ft_qty + rail6ft_qty + rail4ft_qty) - 1
	if (brackets < suggest_bracket_qty) {
		cart_text += '<p class="cart_warning">Note: You have selected less than the recommended number of ' + suggest_bracket_qty + ' rail brackets.</p>'
	}
	if (stops < 1 && curve90_qty + curve135_qty + rail8ft_qty + rail6ft_qty + rail4ft_qty) {
		cart_text += '<p class="cart_warning">Note: You have rails, but no end stops.</p>'
	}
	if (splice_kits < suggest_splice_qty) {
		cart_text += '<p class="cart_warning">Note: You have selected less than the recommended number of ' + suggest_splice_qty + ' splice kits.</p>'
	}

	//~ cart_text += '<p class="note">*price is not final; actual prices will be shown once the products have been added to the cart</p>'
	cart_text += '<div id="cart_div_footer">&nbsp;</div>';
	if (total_price < 1) {
		cart_list.style.display = "none"
	} else {
		cart_list.style.display = "block"
	}
	cart_list.innerHTML = cart_text;
	if(jQuery('#cart_list:visible').length > 0){
		change_cart_div_class();
		jQuery("#going_to_cart").addClass('going_visible');
		jQuery("#going_to_cart #cart_total_quantity_value").text(total_quantity);
	}

	flush_pending_skus()
}

function update_page() {
	page_update = true

	var suggest_bracket_qty = 2*(curve9016_qty + curve90_qty + curve135_qty) + Math.round((rail8ft_qty*8*12 + rail6ft_qty*6*12 + rail4ft_qty*4*12)/32)
	var suggest_splice_qty = (curve9016_qty + curve90_qty + curve135_qty + rail8ft_qty + rail6ft_qty + rail4ft_qty) - 1;
	var elem = document.getElementById("suggest_bracket_qty")
	if (suggest_bracket_qty > 0) {
		elem.style.display = "block"
		elem.innerHTML = "<strong>Based on your rail selections, we recommend <em>at least</em> " + suggest_bracket_qty + " rail brackets</strong>"
	} else {
		elem.style.display = "none"
	}

	elem = document.getElementById("suggest_splice_qty")
	if (suggest_splice_qty > 0) {
		elem.style.display = "block"
		elem.innerHTML = "Based on your rail selections, we recommend " + suggest_splice_qty + " splice kits (one per rail join)"
	} else {
		elem.style.display = "none"
	}

	if (curve9016_qty || curve90_qty || curve135_qty) {
		var roller = document.getElementsByName("roller")
		roller[3].disabled = true
		// roller[4].disabled = true
		if (roller[3].checked) {
			roller[2].checked = true
			roller_type = roller[2].value
		}
		if (curve9016_qty) {
			roller[1].disabled = true
			if (roller[1].checked) {
				roller[2].checked = true
				roller_type = roller[2].value
			}
		}
	} else {
		var roller = document.getElementsByName("roller")
		roller[1].disabled = false
		roller[3].disabled = false
		//roller[4].disabled = false
	}

	if (roller_type == "2" || roller_type == "") {
		document.getElementById("90degree16").disabled = true
		document.getElementById("90degree").disabled = false
		document.getElementById("135degree").disabled = false
	} else if (roller_type == "7") {
		document.getElementById("90degree16").disabled = false
		document.getElementById("90degree").disabled = false
		document.getElementById("135degree").disabled = false
	} else {
		document.getElementById("90degree16").disabled = true
		document.getElementById("90degree").disabled = true
		document.getElementById("135degree").disabled = true
	}

	if (roller_type == "") {
		document.getElementById("rung_supports").disabled = false
  } else {
		document.getElementById("rung_supports").disabled = true
	}

	//all work well
	if (finish_code) {
		//document.getElementById("finish_img").src = img_prefix + finish_code + img_postfix
		document.getElementById("straightrail_img").src = img_prefix + "4004" + finish_code + img_postfix
		document.getElementById("curvedrail_img").src = img_prefix + "CRV" + finish_code + img_postfix
		document.getElementById("h_bracket_img").src = img_prefix + "202" + finish_code + img_postfix
		document.getElementById("stop_img").src = img_prefix + "stop" + finish_code + img_postfix
		document.getElementById("ballstop_img").src = img_prefix + "ball" + finish_code + img_postfix
		document.getElementById("v_bracket_img").src = img_prefix + "201" + finish_code + img_postfix
		document.getElementById("rung_supports_img").src = img_prefix + "620" + ((finish_code == "02") ? "02" : "08") + img_postfix
		jQuery("#selectwheel").find("img").each(function(index) {
									jQuery(this ).attr('src',img_prefix + "wheel" + jQuery(this ).closest("label").find("input").attr("value") + finish_code + img_postfix);
								});
		jQuery("#selectroller").find("img").each(function(index) {
								if(!jQuery(this ).hasClass("image_blank")){
									jQuery(this ).attr('src',img_prefix + "roller" + jQuery(this ).closest("label").find("input").attr("value") + finish_code + img_postfix);
								}
							});
	}
	// if (wheel_type) {
	// 	document.getElementById("wheel_img").src = "QGDHTMLwheel003_a.jpg"
	// }
	if (roller_type) {
		//document.getElementById("roller_img").src = img_prefix + "roller" + roller_type + finish_code + img_postfix
		document.getElementById("v_bracket_img").src = img_prefix + ((roller_type == "2" || roller_type == "7") ? "201" : "301") + finish_code + img_postfix
		document.getElementById("v_bracket_specs").getElementsByTagName("a")[0].href =  'images/qg' + ((roller_type == "2" || roller_type == "7") ? "2" : "3") + '01spec.jpg'
		document.getElementById("h_bracket_img").src = img_prefix + ((roller_type == "2" || roller_type == "7") ? "202" : "302") + finish_code + img_postfix
		document.getElementById("h_bracket_specs").getElementsByTagName("a")[0].href =  'images/qg' + ((roller_type == "2" || roller_type == "7") ? "2" : "3") + '02spec.jpg'

	}
	//if (species_code) {
	//	document.getElementById("species_img").src = img_prefix + species_code + img_postfix
	//}
	if (top_rung_species) {
		document.getElementById("top_rungs_img").src = img_prefix + "610" + top_rung_species + img_postfix
	}

	// check if no ladder is selected
	if (species_code == "NO") {
		// disable elements in Step 6 and change the button to say skip
		jQuery("#height_8ft_label").click(false);
		jQuery("#height_8ft_label").css('cursor', 'not-allowed');
		jQuery("#height_8ft_label").css('opacity', '0.45');
		jQuery("#height_9ft_label").click(false);
		jQuery("#height_9ft_label").css('cursor', 'not-allowed');
		jQuery("#height_9ft_label").css('opacity', '0.45');
		jQuery("#height_10ft_label").click(false);
		jQuery("#height_10ft_label").css('cursor', 'not-allowed');
		jQuery("#height_10ft_label").css('opacity', '0.45');
		jQuery('#contToStp7').html('Skip to Step 7');
		jQuery('#contToStp7').removeAttr("disabled");
		jQuery('#contToStp7').removeClass("disabled");
		jQuery('#contToStp7').attr("data-toggle", "collapse");
		jQuery('#contToStp7').attr("data-parent", "#ladder-fields");
		jQuery('#contToStp7').attr("data-target", "#selectrail");
		jQuery('#railSections').attr("data-toggle", "collapse");
		jQuery('#railSections').attr("data-parent", "#ladder-fields");
		jQuery('#railSections').attr("data-target", "#selectrail");
		jQuery('#railSections').css('cursor', 'pointer');
		jQuery('#railSections').css('opacity', '1.0');
	}

// if ladder species is selected
if (species_code != "NO") {
	jQuery("#height_8ft_label").unbind("click");
	jQuery("#height_8ft_label").css('cursor', 'pointer');
	jQuery("#height_8ft_label").css('opacity', '1.0');
	jQuery("#height_9ft_label").unbind("click");
	jQuery("#height_9ft_label").css('cursor', 'pointer');
	jQuery("#height_9ft_label").css('opacity', '1.0');
	jQuery("#height_10ft_label").unbind("click");
	jQuery("#height_10ft_label").css('cursor', 'pointer');
	jQuery("#height_10ft_label").css('opacity', '1.0');
	jQuery('#contToStp7').html('Continue to Step 7');

	if (species_code == "RO" || species_code == "MA" || species_code == "CH") {
	document.getElementById("top_rung_qty").value = "0";
	document.getElementById("top_rungs_none").checked = true;
	document.getElementById("top_rungs_none").click();
		var toprung_name = document.getElementsByName("top_rungs");
			for (var tr=1;tr<toprung_name.length;tr++) {
			//	toprung_name[tr].checked = false;
				toprung_name[tr].disabled = true;
				}

		} else {
		var toprung_name = document.getElementsByName("top_rungs");
		for (var tr=0;tr<toprung_name.length;tr++)		{
			toprung_name[tr].disabled = false;
			}
		}
}
	update_cart()
}

/* This advButton function controls the enablity/disability of remaining
accordion divs. Users can no longer jump or "skip" steps. For example, when
user is on Step 1, the remaining 10 steps are disabled and unclickable. The
radio button element (finish color in this case) MUST be selected in order
for the "Continue to Step 2" to become enabled and for the Step 2 accordion
to be clickable. */

function advButton(val1, val2, val3, val4) {
	var tagId = val1; // tagId is the value of the red advance button
	var nextId = val2; // nextId is the value of the next HTML div id
	var elem = val3; // elem is the radio group name
	var nextDiv = val4; // nextDiv is the value of the next accordion id
	if (jQuery("input:radio[name='"+elem+"']").is(":checked")) {
		jQuery(tagId).removeAttr("disabled");
		jQuery(tagId).removeClass("disabled");
		jQuery(tagId).attr("data-toggle", "collapse");
		jQuery(tagId).attr("data-parent", "#ladder-fields");
		jQuery(tagId).attr("data-target", nextId);
		jQuery(nextDiv).attr("data-toggle", "collapse");
		jQuery(nextDiv).attr("data-parent", "#ladder-fields");
		jQuery(nextDiv).attr("data-target", nextId);
		jQuery(nextDiv).css('cursor', 'pointer');
		jQuery(nextDiv).css('opacity', '1.0');
	}
}

/* the advButton function above is for radio button inputs. For text field
inputs, a change method is used to detect user input into these fields */

function advButtonT(val1, val2, val3, val4) {
	var tagId = val1; // tagId is the value of the red advance button
	var nextId = val2; // nextId is the value of the next HTML div id
	var elem = val3; // elem is the text field name
	var nextDiv = val4; // nextDiv is the value of the next accordion id

	jQuery("input:text[name='"+elem+"']").focusin(function() {
		jQuery(tagId).removeAttr("disabled");
		jQuery(tagId).removeClass("disabled");
		jQuery(tagId).attr("data-toggle", "collapse");
		jQuery(tagId).attr("data-parent", "#ladder-fields");
		jQuery(tagId).attr("data-target", nextId);
		jQuery(nextDiv).attr("data-toggle", "collapse");
		jQuery(nextDiv).attr("data-parent", "#ladder-fields");
		jQuery(nextDiv).attr("data-target", nextId);
		jQuery(nextDiv).css('cursor', 'pointer');
		jQuery(nextDiv).css('opacity', '1.0');

		if (elem == "splice") {
			jQuery("#noSpliceKits").removeAttr("glyphicon glyphicon-ok-circle");
			jQuery("#noSpliceKits").removeClass("glyphicon glyphicon-ok-circle");
			jQuery("input:radio[id='splicekits_none']").prop('checked', false)
		}

		if (elem == "tap") {
			jQuery("#noSpliceKits").removeAttr("glyphicon glyphicon-ok-circle");
			jQuery("#noSpliceKits").removeClass("glyphicon glyphicon-ok-circle");
			jQuery("input:radio[id='splicekits_none']").prop('checked', false)
		}
	});
}

function update_vars(obj) {

	if (jQuery("input:radio[id='splicekits_none']").is(":checked")) {
		jQuery("#noSpliceKits").attr("class", "glyphicon glyphicon-ok-circle");
		jQuery("#noSpliceKits").css('color', '#5D5D5D');
		jQuery("#splice").val(0);
		jQuery("#tap").val(0);
	}

	switch(obj.name) {
	case "finish":
		advButton("#contToStp2", "#selectwheel", "finish", "#wheelStyle");
		if (obj.checked) { finish_code = obj.value; }
	break;
	case "wheel":
		advButton("#contToStp3", "#selectroller", "wheel", "#rollerStyle");
		if (obj.checked) { wheel_type = obj.value; }
	break;
	case "roller":
		advButton("#contToStp4", "#selectwidth", "roller", "#ladderWidth");
		if (obj.checked) { roller_type = obj.value; }
	break;
	case "width":
		advButton("#contToStp5", "#selectwood", "width", "#ladderWood");
		if (obj.checked) { ladder_width = obj.value; }
	break;
	case "species":
		advButton("#contToStp6", "#selectheight", "species", "#ladderHeight");
		if (obj.checked) { species_code = obj.value; }
	break;
	case "height":
		advButton("#contToStp7", "#selectrail", "height", "#railSections");
		if (obj.checked) { ladder_height = obj.value; }
	break;
	case "CRV16":
		advButtonT("#contToStp8", "#selectbrackets", "CRV16", "#railBrackets");
		curve9016_qty = parseInt(obj.value)
	break;
	case "CRV":
		advButtonT("#contToStp8", "#selectbrackets", "CRV", "#railBrackets");
		curve90_qty = parseInt(obj.value)
		break;
	case "CRV135":
		advButtonT("#contToStp8", "#selectbrackets", "CRV135", "#railBrackets");
		curve135_qty = parseInt(obj.value)
		break;
	case "8ftrail":
		advButtonT("#contToStp8", "#selectbrackets", "8ftrail", "#railBrackets");
		rail8ft_qty = parseInt(obj.value)
		break;
	case "6ftrail":
		advButtonT("#contToStp8", "#selectbrackets", "6ftrail", "#railBrackets");
		rail6ft_qty = parseInt(obj.value)
		break;
	case "4ftrail":
		advButtonT("#contToStp8", "#selectbrackets", "4ftrail", "#railBrackets");
		rail4ft_qty = parseInt(obj.value)
		break;
	case "vertical":
		advButtonT("#contToStp9", "#selectendstops", "vertical", "#endStops");
		v_bracket_qty = parseInt(obj.value)
		break;
	case "horizontal":
		advButtonT("#contToStp9", "#selectendstops", "horizontal", "#endStops");
		h_bracket_qty = parseInt(obj.value)
		break;
	case "stop":
		advButtonT("#contToStp10", "#selectsplicekits", "stop", "#spliceKits");
		stop_qty = parseInt(obj.value)
		break;
	case "ballstop":
		advButtonT("#contToStp10", "#selectsplicekits", "ballstop", "#spliceKits");
		ballstop_qty = parseInt(obj.value)
		break;
	case "splice":
		//advButtonT("#contToStp11", "#selectaccessories", "splice", "#optAccess");
		splice_qty = parseInt(obj.value)
		break;
	case "tap":
		jQuery("#noSpliceKits").css('color', '#FFFFFF');
		//advButtonT("#contToStp11", "#selectaccessories", "tap", "#optAccess");
		tap_qty = parseInt(obj.value)
		break;
	case "handrail":
		if (obj.checked) { handrail_code = obj.value }
		break;
	case "tread":
		tread_qty = parseInt(obj.value)
		break;
	case "top_rungs":
		if (obj.checked) { top_rung_species = obj.value }
		break;
	case "top_rung_qty":
		top_rung_qty = parseInt(obj.value)
		break;
	case "rung_supports":
		rung_supports_qty = parseInt(obj.value)
	}
}

function csh_update(obj) {
	update_vars(obj)
	update_page()
	update_cookie()
}

function intext(obj) {
	prev_value = obj.value
	if (obj.value == "0") {
		obj.value = ""
	}
	obj.select()
}

function outtext(obj) {
	obj.value = (isNaN(parseInt(obj.value))) ? "0" : parseInt(obj.value)
	if (obj.value != prev_value) {
		csh_update(obj)
	}
}

jQuery(document).ready(function(jQuery)
{
	//~ jQuery("#cart_list").animate({top: jQuery("#selectfinish").offset().top+"px"},{duration:500,queue:false})
	//~ jQuery(window).scroll(function () {
		//~ top_limit = jQuery("#selectfinish").offset().top
		//~ bottom_limit = jQuery("#finalstep").offset().top + jQuery("#finalstep").height()
		//~ cart_height = jQuery("#cart_list").height()
		//~ scroll_value = jQuery(document).scrollTop()
		//~ scroll_offset = scroll_value
		//~ if (scroll_offset < top_limit) {
			//~ scroll_offset = top_limit
		//~ }
		//~ if (scroll_value + cart_height > bottom_limit) {
			//~ scroll_offset = bottom_limit - cart_height
		//~ }
		//~ jQuery("#cart_list").animate({top: scroll_offset+"px"},{duration:500,queue:false})
	//~ })
	var shopping_list = read_cookie("shoppinglist")
	//alert('read_cookie shopping_list='+shopping_list);
	if (shopping_list) {
		load_list(split_parameters(shopping_list))
	}else{
		jQuery('#ladder-fields').find("input[type=text]").val("0");
		jQuery('#ladder-fields').find('input[type=radio]').attr('checked', false);
	}
	inputs = document.getElementsByTagName("input")
	for (i=0; i<inputs.length; i++) {
		update_vars(inputs[i])
	}
	cookie_session = split_parameters(read_cookie("cookie%5Fsession"))
	update_page()
})
