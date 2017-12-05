/* door.js - Rolling Door Form for cshardware.com - last modified 09-22-2016 by dp */
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
    var total_quantity = 0;
	var sku = elem.parentNode.parentNode.firstChild.nextSibling.innerHTML;
    console.log('elem.value='+elem.value);
	sku_info[sku].qty = elem.value;
    if(jQuery('input.qtyinput').length > 0){
		jQuery('input.qtyinput').each(function(){
           var number = parseInt(jQuery(this).val()) ? parseInt(jQuery(this).val()) : 0;
           total_quantity += number;
        });
        if(total_quantity > 0){
            jQuery("#going_to_cart").addClass('going_visible');
            jQuery("#going_to_cart #cart_total_quantity_value").text(total_quantity);
        }
	}
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

function flush_pending_skus() {
    var str = '', sku;
    while (skus_to_load.length > 0) {
        sku = skus_to_load.pop();
        str += (str ? ("|" + sku) : (sku));
    }

	while (pending_skus.length > 0) {
        sku = pending_skus.pop();
        jQuery("#" + sku.replace(/(:|\.)/g,'\\$1')).html(jQuery('<div />').html(get_tr_from_sku(sku)).children(":first").html());
    }

	if (str) {
        jQuery(document).ajaxStart(function(){
                            jQuery("div.progress-image").show();
                         });
        jQuery.get("/ssajax", {rb_id: "0AFBE47B797546FFB51B455F9E12553A", sku: str},
        function(data) {

            jQuery("record", data).each(function ( index, value) {
                var record = value; /* jQuery(this); */
                sku = jQuery("sku", record).text();
                sku_info[sku] = {};
                sku_info[sku].p_key = jQuery("p_key", record).text();
                sku_info[sku].nm = jQuery("nm", record).text();
                sku_info[sku].ds = (jQuery("ds", record).text());
                sku_info[sku].retail_price = parseFloat(jQuery("retail_price", record).text());
                sku_info[sku].image = jQuery("thumb", record).text();
                sku_info[sku].qty = '';
                pending_skus.push(sku);
            });
            if (!is_updating) {
                flush_pending_skus();
            }
            jQuery(document).ajaxStop(function(){
                             jQuery('div.progress-image').hide();
                         });
        }, 'xml');
    }
}

function get_tr_from_sku(sku) {
	if (!sku_info[sku]) {
		skus_to_load.push(sku);
		return '<tr id="' + sku + '"><td class="sku_image">&nbsp;</td><td class="sku_td">' + sku + '</td><td>Loading product info...</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	} else {
		var str = '<tr id="' + sku + '"><td class="sku_image"><img src="' + sku_info[sku].image + '" /></td>';
		str += '<td class="sku_td">' + sku + '</td>';
        if(sku_info[sku].ds){
            str += '<td class="contDesc"><div class="sku_name">' + sku_info[sku].nm + '</div><div class="sku_desc">' + sku_info[sku].ds + '</div><div class="toggle_desc"><a onclick="toggle_desc(this); return false;" href="#">[+] Show more</a></div></td>';
        }else{
            str += '<td class="contDesc"><div class="sku_name">' + sku_info[sku].nm + '</div></td>';
        }
        if(sku_info[sku].retail_price){
            str += '<td class="sku_price">$' + sku_info[sku].retail_price.toFixed(2) + '</td>';
        }else{
            str += '<td class="sku_price">Not available</td>';
        }
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
console.log("Name:" + elem.name);
console.log("Value:" + elem.value);

// array of all straps with red finish options
var redStraps = ["gb", "ic", "pl", "wr"];

	switch(elem.name) {
	case "strap":
		if (elem.value == "11" || elem.value == "12") {
							//document.getElementById("finish_02").disabled = true;
							document.getElementById("finish1").style.display = "inline";
							document.getElementById("finish2").style.display = "inline";
							document.getElementById("finish3").style.display = "inline";
							document.getElementById("finish4").style.display = "inline";
							document.getElementById("finish5").style.display = "none";
							document.getElementById("finish6").style.display = "none";
		} else if (elem.value == "an") {
							document.getElementById("finish1").style.display = "inline";
							document.getElementById("finish2").style.display = "none";
							document.getElementById("finish3").style.display = "inline";
							document.getElementById("finish4").style.display = "none";
							document.getElementById("finish5").style.display = "none";
							document.getElementById("finish6").style.display = "none";
	 } else if (elem.value == "gb" || elem.value == "ic" || elem.value == "pl" || elem.value == "wr") {
							document.getElementById("finish1").style.display = "inline";
							document.getElementById("finish2").style.display = "inline";
							document.getElementById("finish3").style.display = "inline";
							document.getElementById("finish4").style.display = "inline";
							document.getElementById("finish5").style.display = "inline";
							document.getElementById("finish6").style.display = "none";
	} else if (elem.value == "tg") {
							document.getElementById("finish1").style.display = "inline";
							document.getElementById("finish2").style.display = "inline";
							document.getElementById("finish3").style.display = "inline";
							document.getElementById("finish4").style.display = "none";
							document.getElementById("finish5").style.display = "none";
							document.getElementById("finish6").style.display = "none";
	} else if (elem.value == "lh") {
							document.getElementById("finish1").style.display = "inline";
							document.getElementById("finish2").style.display = "none";
							document.getElementById("finish3").style.display = "inline";
							document.getElementById("finish4").style.display = "none";
							document.getElementById("finish5").style.display = "none";
							document.getElementById("finish6").style.display = "inline";
	} else {
						document.getElementById("finish_02").disabled = false;
						document.getElementById("finish1").style.display = "inline";
						document.getElementById("finish2").style.display = "inline";
						document.getElementById("finish3").style.display = "inline";
						document.getElementById("finish4").style.display = "inline";
						document.getElementById("finish5").style.display = "none";
						document.getElementById("finish6").style.display = "none";
	}
		break;

	case "finish":
		if (elem.value == "02") {
			document.getElementById("strap_1104").disabled = true;
			document.getElementById("strap_1204").disabled = true;
		} else {
			document.getElementById("strap_1104").disabled = false;
			document.getElementById("strap_1204").disabled = false;
		}
			if (document.getElementsByName("strap").checked = true) {
				jQuery('#doorThk').show();
			}
	}

	update_parts();
}

function update_parts() {
	is_updating = true;
	var html_string = "";

	if (!cookie_session) {
		html_string = '<p class="text_bold info_message">Error: Cookies must be enabled.</p>';
	} else if (!vars["strap"]) {
		html_string = '<p class="text_bold info_message">Please select a strap (Step 1).</p>';
	} else if (!vars["finish"]) {
		html_string = '<p class="text_bold info_message">Please select a finish color (Step 2).</p>';
	}

	if (html_string == "") {
        //other step 3 options

        html_string += '<form action="http://' + window.location.hostname + '/addcart?';
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

		// Step 3, option 1
		// html_string += '<fieldset><legend>STEP 3: Select your door and get FREE SHIPPING&#42;</legend><div id="step_3"';
		html_string += '<div id="doorOpt3" class="doorOpt"><p class="text_bold"><span class="doorOptHeader">Option 2: Pre-Assembled, Pre-Finished Door&nbsp;&nbsp;</span><span style="font-weight:bold; font-size:10px; color:red; vertical-align:top;">FREE SHIPPING*</span></p>';
		html_string += '<p>All four doors come pre-assembled, pre-finished, and constructed with Ponderosa Pine wood. Measures 84&quot; tall, 36&quot; wide, and 1-1/2&quot; thick. Buy this door and receive <strong>FREE SHIPPING</strong> on your entire order. <span style="font-weight: normal; font-style: italic; font-size: 11px;">&#42;Applies only to those within the 48 contiguous United States.</span></p>'
		html_string += '<table class="parts_list tablesaw tablesaw-stack" data-tablesaw-mode="stack" >';
		html_string += get_tr_from_sku("QG.DTZ.36X84.GR");
		html_string += get_tr_from_sku("QG.D3W.36X84.WH");
		html_string += get_tr_from_sku("QG.DTW.36X84.MO");
		html_string += get_tr_from_sku("QG.DTX.36X84.FA");

		// option 2
		html_string += '</table ></div><div id="doorOpt3" class="doorOpt"><p class="text_bold"><span class="doorOptHeader">Option 3: Ready-to-Assemble, Unfinished, Unassembled Door</span></p>';
		html_string += '<p>If you want a more rustic look or have your own unique style in mind, we offer the RTA Barn Door that you can assemble and finish with a stain of your choice. Measures 81&quot; tall, 40&quot; wide, and 1-1/2&quot; thick. Regular shipping costs apply.</p>';
		html_string += '<table class="parts_list tablesaw tablesaw-stack" data-tablesaw-mode="stack">';
		html_string += get_tr_from_sku("QG.RTA.00.40X81.PN");
		html_string += '</table></div>';
		html_string += '</div>';

        //other steps
		html_string += '<fieldset class="panel other_parts"><legend><a class="accordion-toggle" data-toggle="collapse" href="#step_4"><h3 style="">STEP 4: Choose your roller strap quantity</h3></a></legend><div class="fiedset_content collapse" id="step_4">';
		html_string += '<table class="parts_list tablesaw tablesaw-stack" data-tablesaw-mode="stack">';
		html_string += '<tr><th colspan="4">Roller Strap</th></tr><tr>';

		// convert strap two-letter string to uppercase
			var stpstr = vars["strap"];
			var updated_stp = stpstr.toUpperCase();

		if (vars["strap"] == "SP"){
			html_string += get_tr_from_sku("QG.1104.NH." + vars["finish"]);
		} else if (vars["strap"] == "FL"){
			html_string += get_tr_from_sku("QG.1204.NH." + vars["finish"]);
		} else if (jQuery("#finish_11").is(':checked')){
			// switch statement
			switch (vars["strap"]) {
				case "gb":
				case "ic":
				case "pl":
				case "wr":
					html_string += get_tr_from_sku("QG.1304." + updated_stp + ".11");
				break;
				default:
					html_string += get_tr_from_sku("QG.1304." + updated_stp + "." + vars["finish"]);
				break;
			}
			// end of switch statement
		} else {
			html_string += get_tr_from_sku("QG." + ((updated_stp == "11" || updated_stp == "12") ? updated_stp + "04." : "1304." + updated_stp + ".") + vars["finish"]);
		}

		// establish variable to hold finish values
		var compFinish;
		if (vars["finish"] == "01" || vars["finish"] == "11" ) {
			compFinish = "08";
		} else {
			compFinish = vars["finish"];
		}

		html_string += '<td>&nbsp;</td><td colspan="3" class="sku_note">Note: Sold individually; 2 recommended for each door.</td></tr>';
		html_string += '</table></div></fieldset>';
		html_string += '<fieldset class="panel other_parts"><legend><a class="accordion-toggle" data-toggle="collapse" href="#step_5"><h3 style="">STEP 5: Select your rail length and quantity</h3></a></legend><div class="fiedset_content collapse" id="step_5"><table class="parts_list tablesaw tablesaw-stack" data-tablesaw-mode="stack">';
		html_string += '<tr><th colspan="4">Rails</th></tr>';
		html_string += get_tr_from_sku("QG.4004." + compFinish);
		html_string += get_tr_from_sku("QG.4006." + compFinish);
		html_string += get_tr_from_sku("QG.4008." + compFinish);
		html_string += '</table></div></fieldset>';
		html_string += '<fieldset class="panel other_parts"><legend><a class="accordion-toggle" data-parent="#door-fields" data-toggle="collapse" href="#step_6"><h3 style="">STEP 6: Select your wall-mounting brackets</h3></a></legend><div class="fiedset_content collapse" id="step_6"><table class="parts_list tablesaw tablesaw-stack" data-tablesaw-mode="stack">';
		html_string += '<tr><th colspan="4">Wall mounting brackets</th></tr>';

		if (document.getElementById("door_01").checked == true) {
			html_string += get_tr_from_sku("QG.201." + compFinish);
		} else {
			html_string += get_tr_from_sku("QG.203." + compFinish);
		};

		html_string += '<tr><td>&nbsp;</td><td colspan="3" class="sku_note">Note: We recommend a minimum of 1 bracket for every 16in of rail. Additional brackets may be needed depending on the weight of the door.</td></tr>';
		html_string += '</table></div></fieldset>';
		html_string += '<fieldset class="panel other_parts"><legend><a class="accordion-toggle" data-parent="#door-fields" data-toggle="collapse" href="#step_7"><h3 style="">STEP 7: Select your floor guides and stops</h3></a></legend><div class="fiedset_content collapse" id="step_7"><table class="parts_list tablesaw tablesaw-stack" data-tablesaw-mode="stack">';
		html_string += '<tr><th colspan="4">Door stops and center guides</th></tr>';
		if (vars["strap"] == "11" || vars["strap"] == "12") {
			html_string += get_tr_from_sku("QG." + vars["strap"] + "02." + compFinish);
			html_string += get_tr_from_sku("QG." + vars["strap"] + "03." + compFinish);
			html_string += get_tr_from_sku("QG." + vars["strap"] + "01." + compFinish);
		} else {
			if (vars["strap"] === "SP") {
				html_string += get_tr_from_sku("QG.1103.NH." + compFinish);
				html_string += get_tr_from_sku("QG.1102.NH." + compFinish);
				html_string += get_tr_from_sku("QG.1101.NH." + compFinish);
			} else if (vars["strap"] === "FL") {
				html_string += get_tr_from_sku("QG.1203.NH." + compFinish);
				html_string += get_tr_from_sku("QG.1202.NH." + compFinish);
				html_string += get_tr_from_sku("QG.1201.NH." + compFinish);
			} else {
				html_string += get_tr_from_sku("QG.1306." + ((vars["door"] == "03") ? "01.": "02.") + compFinish);
				html_string += get_tr_from_sku("QG.1305.DS." + compFinish);
				html_string += get_tr_from_sku("QG.1301.DG." + compFinish);
			}
		}
		html_string += get_tr_from_sku("QG.1000.08");
		html_string += '</table></div></fieldset>';
		html_string += '<fieldset class="panel other_parts"><legend><a class="accordion-toggle" data-parent="#door-fields" data-toggle="collapse" href="#step_8"><h3 style="">STEP 8: Select your rail end stops</h3></a></legend><div class="fiedset_content collapse" id="step_8"><table class="parts_list tablesaw tablesaw-stack" data-tablesaw-mode="stack">';
		html_string += '<tr><th colspan="4">Rail End Stops</th></tr>';
		html_string += get_tr_from_sku("QG.40." + compFinish);
		html_string += get_tr_from_sku("QG.401." + compFinish);

		html_string += '</table></div></fieldset>';
		html_string += '<fieldset class="panel other_parts"><legend><a class="accordion-toggle" data-parent="#door-fields" data-toggle="collapse" href="#step_9"><h3 style="">STEP 9: Select your optional accessories</h3></a></legend><div class="fiedset_content collapse" id="step_9"><table class="parts_list tablesaw tablesaw-stack" data-tablesaw-mode="stack">';
		html_string += '<tr><th colspan="4">Rail accessories</th></tr>';
		html_string += get_tr_from_sku("QG.402." + compFinish);
		html_string += get_tr_from_sku("QG.41");
		html_string += get_tr_from_sku("QG.TAP1420");
		html_string += '<tr><th colspan="4">Handles</th></tr>';

		html_string += get_tr_from_sku("QG.1199.01." + compFinish);
		html_string += get_tr_from_sku("QG.1199.02." + compFinish);
		html_string += get_tr_from_sku("QG.1199.03." + compFinish);
		html_string += get_tr_from_sku("QG.1199.04." + compFinish);

		/* New Handles */
		html_string += get_tr_from_sku("QG.1199.05." + compFinish);
		html_string += get_tr_from_sku("QG.1399.03." + compFinish);
		html_string += get_tr_from_sku("QG.1299.01." + compFinish);
		html_string += get_tr_from_sku("QG.1399.04." + compFinish);
		html_string += get_tr_from_sku("QG.1399.02." + compFinish);
		html_string += get_tr_from_sku("QG.1399.01." + compFinish);

		// new recessed pull section
		html_string += '<tr><th colspan="4">Recessed Pulls</th></tr>';

		// switch statement for recessed pulls
		switch (compFinish) {
			case "02":
				html_string += get_tr_from_sku("DA.FP4134U15");
				html_string += get_tr_from_sku("DA.FP7178U26D");
				html_string += get_tr_from_sku("DA.FP7178U26");
				html_string += get_tr_from_sku("DA.FP7178U15A");
				html_string += get_tr_from_sku("DA.FP7178U15");
				html_string += get_tr_from_sku("DA.FP7178U14");
				break;
			case "07":
				html_string += get_tr_from_sku("DA.FP4134U10B");
				html_string += get_tr_from_sku("DA.FP7178U15A");
				html_string += get_tr_from_sku("DA.FP7178U10B");
				break;
			case "08":
			case "09":
				html_string += get_tr_from_sku("DA.FP4134U19");
				html_string += get_tr_from_sku("DA.FP7178U19");
				break;
		}

		// list the brass recessed pulls
		html_string += get_tr_from_sku("DA.FP7178U5");
		html_string += get_tr_from_sku("DA.FP7178U3");
		html_string += get_tr_from_sku("DA.FP7178CR003");

		// Next section
		html_string += '<tr><th colspan="4">Latches</th></tr>';

		html_string += get_tr_from_sku("QG.1307.01." + compFinish);

		html_string += '</table></div></fieldset><button id="add_to_cart_button" class="btn btn-sm btn-primary pull-right" type="submit"><span class="glyphicon glyphicon-shopping-cart"></span>Add to Cart</button></form>';

	}

    if(jQuery('#parts').length > 0){
        jQuery('#parts').html(html_string);

        jQuery('a.accordion-toggle').each(function(){
			jQuery(this).on('click',function(){
                if((jQuery(jQuery(this).attr('href')).length > 0) && (jQuery(jQuery(this).attr('href')).hasClass('in'))){
                    jQuery(jQuery(this).attr('href')).hide();
                    return;
                }
				jQuery('div.fiedset_content').removeClass('in').hide();

                if(jQuery(jQuery(this).attr('href')).length >0){
                    jQuery(jQuery(this).attr('href')).show();
                }

                if(jQuery('#step_3:visible').length > 0){
                    jQuery('#doorOpt2, #doorOpt3').show();
                }else{
                    jQuery('#doorOpt2, #doorOpt3').hide();
                }
            })
        })

        if(jQuery('#step_3:visible').length > 0){
            jQuery('#doorOpt2, #doorOpt3').show();
        }else{
            jQuery('#doorOpt2, #doorOpt3').hide();
        }
    }

	is_updating = false;
	flush_pending_skus();
}

jQuery(document).ready(function($)
{
	cookie_session = split_parameters(read_cookie("cookie%5Fsession"));
    if(jQuery('input[name="strap"]:checked').length > 0){
        update_page(jQuery('input[name="strap"]:checked').get()[0]);
    }
    if(jQuery('input[name="finish"]:checked').length > 0){
        update_page(jQuery('input[name="finish"]:checked').get()[0]);
    }
    if(jQuery('input[name="door"]:checked').length > 0){
        update_page(jQuery('input[name="door"]:checked').get()[0]);
    }
    update_parts();
});


	// pushing red finish for the following straps
	// if (stpstr == 'wr'){
	// 	alert("RED IS PICKED");
	// 	document.getElementById('finish5').style.visibility="visible";
	// } else if (y == 'pl') {
	// 	document.getElementById('finish5').style.visibility="visible";
	// } else if (y == 'ic') {
	// 	document.getElementById('finish5').style.visibility="visible";
	// } else if (y == 'gb') {
	// 	document.getElementById('finish5').style.visibility="visible";
	// } else {
	// 	alert("RED NOT PICKED");
	// 	document.getElementById('finish5').style.visibility="hidden";
	// }


function reset_page() {
    jQuery('#door-fields').find("input[type=text]").val("");
	jQuery('#door-fields').find('input[type=radio]').attr('checked', false);
    document.cookie = "cookie%5Fsession=; path=/";
	window.location.reload()
}
