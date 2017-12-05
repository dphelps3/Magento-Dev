/* fr-door.js - Flat Rail Rolling Door Form for cshardware.com - last modified 7-18-2016 */
var sku_info = {},
	skus_to_load = [],
	pending_skus = [],
	is_updating = false,
	cookie_session = "",
	vars = {};

function read_cookie(name) {
	var nameEQ = name + "="
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function split_parameters(param_list) {
	param_list = decodeURIComponent(param_list).split("&");
	var params = {};
	var param = "";
	for (var i=0; i<param_list.length; i++) {
		param = param_list[i].split("=");
		params[param[0]] = encodeURIComponent(param[1]);
	}
	return params;
}

function save_sku_qty(elem) {
	var sku = elem.parentNode.parentNode.firstChild.nextSibling.innerHTML;
	sku_info[sku].qty = elem.value;
}

function NumbersOnly(myfield, e) {
	var key;
	var keychar;
	if (window.event) {
		key = window.event.keyCode;
	} else if (e) {
		key = e.which;
	} else {
		return true;
	}
	keychar = String.fromCharCode(key);
	if ((key==null) || (key==0) || (key==8) || (key==9) || (key==27) ) {
		return true;
	} else if ((key==13) || (key==32)) {
		return false;
	} else if (keychar=='.' && myfield.value.indexOf('.')>-1) {
		return false;
	} else if ((("0123456789").indexOf(keychar) > -1)) {
		return true;
	} else {
		return false;
	}
}
function flush_pending_skus() {	var str = '', sku;	while (skus_to_load.length > 0) {		sku = skus_to_load.pop();		str += (str ? ("|" + sku) : (sku));	}
	while (pending_skus.length > 0) {		sku = pending_skus.pop();		jQuery("#" + sku.replace(/(:|\.)/g,'\\$1')).html(jQuery('<div />').html(get_tr_from_sku(sku)).children(":first").html());	}
	if (str) {		jQuery.get("/ssajax", {rb_id: "0AFBE47B797546FFB51B455F9E12553A", sku: str}, function(data) {			jQuery("record", data).each(function ( index, value) {				var record = value; /* jQuery(this); */				sku = jQuery("sku", record).text();				sku_info[sku] = {};				sku_info[sku].p_key = jQuery("p_key", record).text();				sku_info[sku].nm = jQuery("nm", record).text();				sku_info[sku].ds = (jQuery("ds", record).text());				sku_info[sku].retail_price = parseFloat(jQuery("retail_price", record).text());				sku_info[sku].image = jQuery("thumb", record).text();				sku_info[sku].qty = '';				pending_skus.push(sku);			});			if (!is_updating) {				flush_pending_skus();			}		}, 'xml');	}}

function get_tr_from_sku(sku) {
	if (!sku_info[sku]) {
		skus_to_load.push(sku);
		return '<tr id="' + sku + '"><td class="sku_image">&nbsp;</td><td class="sku_td">' + sku + '</td><td>Loading product info...</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	} else {
		var str = '<tr id="' + sku + '"><td class="sku_image"><img src="' + sku_info[sku].image + '" /></td>';
		str += '<td class="sku_td">' + sku + '</td>';
		str += '<td class="contDesc"><div class="sku_name">' + sku_info[sku].nm + '</div><div class="sku_desc">' + sku_info[sku].ds + '</div><div class="toggle_desc"><a onclick="toggle_desc(this); return false;" href="#">[+] Show more</a></div></td>';
		str += '<td class="sku_price">$' + sku_info[sku].retail_price.toFixed(2) + '</td>';
		str += '<td>&nbsp;Qty: <input type="hidden" name="keys" value="' + sku_info[sku].p_key + '" /><input type="text" size="6" name="qty[' + sku_info[sku].p_key + ']" onfocus="this.select();" onkeypress="return NumbersOnly(this, event);" onchange="save_sku_qty(this)" value="' + sku_info[sku].qty + '" class="qtyinput" tabindex="" /></td></tr>';
		return str;
	}
}

function toggle_desc(elem) {
	if (elem.parentNode.previousSibling.style.display == "" || elem.parentNode.previousSibling.style.display == "none") {
		elem.innerHTML = "[-] Show less";
	} else {
		elem.innerHTML = "[+] Show more";
	}
	jQuery(elem.parentNode.previousSibling).slideToggle();
}

function update_page(elem) {

	vars[elem.name] = elem.value;

	update_parts();
}

function update_parts() {
	is_updating = true;
	var html_string = "";
	if (!cookie_session) {
		html_string = "Error: Cookies must be enabled.";
	}

	if (html_string == "") { 		html_string += '<form action="http://' + window.location.hostname + '/addcart?';
		html_string += 'type=v200add&o_url=https%3A%2F%2F' + window.location.hostname.replace(".","%2E") + '&createsessioncookie=1&noredirect=1&';
		html_string += "a_name=" + cookie_session["a_name"] + "&";
		html_string += "c_Lastname=" + cookie_session["c_Lastname"] + "&";
		html_string += "c_firstName=" + cookie_session["c_firstName"] + "&";
		html_string += "c_userName=" + cookie_session["c_userName"] + "&";
		html_string += "c_id=" + cookie_session["c_id"] + "&";
		html_string += "a_id=" + cookie_session["a_id"] + "&";
		html_string += "s_url=" + cookie_session["s_url"] + "&";
		html_string += "s_key=" + cookie_session["s_key"] + "&";
		html_string += "l_ws_key=" + cookie_session["l_ws_id"] + "&";
		html_string += "sc_id=" + cookie_session["s_key"];
		html_string += '" method="post">';

		// Step 2
		html_string += '<fieldset><legend>STEP 2: Choose your roller strap quantity</legend><div id="step_2">';
		html_string += '<table class="parts_list">';
		html_string += '<tr><th colspan="4">Roller Strap</th></tr><tr>';
      // convert strap two-letter string to uppercase
			var stpstr = vars["strap"];
			var updated_stp = stpstr.toUpperCase();

      // replace hyphen with period
      var optimized_stp = updated_stp.replace(/-/g, '.');

  		html_string += get_tr_from_sku("QG.FR1304." + optimized_stp);

		html_string += '<td>&nbsp;</td><td colspan="3" class="sku_note">Note: Sold individually; 2 recommended for each door.</td></tr>';
		html_string += '</table></div></fieldset>';

		// Step 3, option 1
		html_string += '<fieldset><legend>STEP 3: Select your door and get FREE SHIPPING&#42;</legend><div id="step_3"';
		html_string += '<div class="doorOpt"><span class="doorOptHeader">Option 1: Pre-Existing Door&nbsp;&nbsp;</span>';
		html_string += '<p>If you already have a door, please skip to Step 4. Our Flat Rail hardware is compatible with doors up to 1-1/2 inches thick.</p>'
		html_string += '<div class="doorOpt"><span class="doorOptHeader">Option 2: Pre-Assembled, Pre-Finished Door&nbsp;&nbsp;</span><span style="font-weight:bold; font-size:10px; color:red; vertical-align:top;">FREE SHIPPING*</span>';
		html_string += '<p>All four doors come pre-assembled, pre-finished, and constructed with Ponderosa Pine wood. Measures 84&quot; tall, 36&quot; wide, and 1-1/2&quot; thick. Buy this door and receive <strong>FREE SHIPPING</strong> on your entire order. <span style="font-weight: normal; font-style: italic; font-size: 11px;">&#42;Applies only to those within the 48 contiguous United States.</span></p>';
		html_string += '<table class="parts_list" style="margin-left:50px;">';
		html_string += get_tr_from_sku("QG.DTZ.36X84.GR");
		html_string += get_tr_from_sku("QG.D3W.36X84.WH");
		html_string += get_tr_from_sku("QG.DTW.36X84.MO");
		html_string += get_tr_from_sku("QG.DTX.36X84.FA");

		// option 2
		html_string += '</table></div><div class="doorOpt"><span class="doorOptHeader">Option 3: Ready-to-Assemble, Unfinished, Unassembled Door</span>';
		html_string += '<p>If you want a more rustic look or have your own unique style in mind, we offer the RTA Barn Door that you can assemble and finish with a stain of your choice. Measures 81&quot; tall, 40&quot; wide, and 1-1/2&quot; thick. Regular shipping costs apply.</p>';
		html_string += '<table class="parts_list" style="margin-left:50px;">';
		html_string += get_tr_from_sku("QG.RTA.00.40X81.PN");
		html_string += '</table></div>';

		html_string += '</div></fieldset>';

		html_string += '<fieldset><legend>STEP 4: Select your rail length and quantity</legend><div id="step_4"><table>';
		html_string += '<tr><th colspan="4">Rails</th></tr>';

    var hwFinish;

    switch(vars["strap"]) {
      case "hk3-08":
      case "hk5-08":
      case "st3-08":
      case "d3-08":
      case "tm3-08":
      case "hs3-08":
        var hwFinish = "08";
        break;
      case "hk3-09":
      case "hk5-09":
      case "st3-09":
      case "d3-09":
      case "tm3-09":
      case "hs3-09":
        var hwFinish = "09";
        break;

    }
    html_string += get_tr_from_sku("QG.FR4003." + hwFinish);
		html_string += get_tr_from_sku("QG.FR4004." + hwFinish);
		html_string += get_tr_from_sku("QG.FR4006." + hwFinish);
		html_string += get_tr_from_sku("QG.FR4008." + hwFinish);
		html_string += '</table></div></fieldset>';
		html_string += '<fieldset><legend>STEP 5: Select your wall-mounting brackets</legend><div id="step_5"><table>';
		html_string += '<tr><th colspan="4">Wall mounting brackets</th></tr>';
    html_string += get_tr_from_sku("QG.FR201.08");
		html_string += '<tr><td>&nbsp;</td><td colspan="3" class="sku_note">Note: We recommend a minimum of 1 bracket for every 16in of rail. Additional brackets may be needed, depending on the weight of the door.</td></tr>';
		html_string += '</table></div></fieldset>';
		html_string += '<fieldset><legend>STEP 6: Select your door stops and center guide</legend><div id="step_6"><table>';
		html_string += '<tr><th colspan="4">Door stops and center guide</th></tr>';
		html_string += get_tr_from_sku("QG.FR40." + hwFinish);
		html_string += get_tr_from_sku("NT.1403.08");
		html_string += '</table></div></fieldset>';
		html_string += '<fieldset><legend>STEP 7: Select your optional accessories</legend><div id="step_8"><table>';
		html_string += '<tr><th colspan="4">Rail accessories</th></tr>';
		html_string += get_tr_from_sku("QG.FR41.08");

		// latch section
		html_string += '<tr><th colspan="4"><br />Latches</th></tr>';
		html_string += get_tr_from_sku("QG.1307.01." + hwFinish);

		// handles and pulls
		html_string += '<tr><th colspan="4"><br />Handles</th></tr>';
    html_string += get_tr_from_sku("QG.1199.01." + hwFinish);
		html_string += get_tr_from_sku("QG.1199.02." + hwFinish);
		html_string += get_tr_from_sku("QG.1199.03." + hwFinish);
		html_string += get_tr_from_sku("QG.1199.04." + hwFinish);
		html_string += get_tr_from_sku("QG.1199.05." + hwFinish);
		html_string += get_tr_from_sku("QG.1399.03." + hwFinish);
		html_string += get_tr_from_sku("QG.1299.01." + hwFinish);
		html_string += get_tr_from_sku("QG.1399.04." + hwFinish);
		html_string += get_tr_from_sku("QG.1399.02." + hwFinish);
		html_string += get_tr_from_sku("QG.1399.01." + hwFinish);
    html_string += '<tr><th colspan="4"><br />Recessed Pulls</th></tr>';
    html_string += get_tr_from_sku("DA.FP4134U19");
    html_string += get_tr_from_sku("DA.FP7178U19");

		// Drill bit
		html_string += '<tr><th colspan="4"><br />Tools</th></tr>';
    html_string += get_tr_from_sku("QG.DRILLBIT5169");

		html_string += '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" value="Add to Cart" class="btn_addtocart chrome" /></td></tr></table></fieldset></form>';
	}
	document.getElementById("parts").innerHTML = html_string;
	is_updating = false;
	flush_pending_skus();
}

jQuery(document).ready(function($)
{
	cookie_session = split_parameters(read_cookie("cookie%5Fsession"));
});

function describeStrap(x,y) {
	imgValue = y;
		jQuery("#dimensions").html("<img src='https://cdn.cshardware.com/media/wysiwyg/" + y + "-det.jpg' style='width:340px;' />");
}

function displayStrap(x,y) {
	imgValue = y;
		jQuery("#dimensions").html("<img src='https://cdn.cshardware.com/media/wysiwyg/" + y + "-det.jpg' style='width:340px;' />");
}

function testFunction(a,b) {

	// this will disable the describeStrap function
	window.describeStrap = describeStrap;
	// window.describeStrap = null;
}
