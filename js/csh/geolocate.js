function geolocate() {
  var url = "http://freegeoip.net/json/";
  var geodata;
  var JSON = JSON || {};
  
  // implement JSON.stringify serialization
  JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
      // simple data type
      if (t == "string") obj = '"'+obj+'"';
        return String(obj);
    } else {
    // recurse array or object
      var n, v, json = [], arr = (obj && obj.constructor == Array);
      for (n in obj) {
        v = obj[n]; t = typeof(v);
        if (t == "string") v = '"'+v+'"';
        else if (t == "object" && v !== null) v = JSON.stringify(v);
        json.push((arr ? "" : '"' + n + '":') + String(v));
      }
      return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
  };
  
  // implement JSON.parse de-serialization
  JSON.parse = JSON.parse || function (str) {
    if (str === "") str = '""';
      eval("var p=" + str + ";");
      return p;
  };
  
  //Check if cookie already exist. If not, query IPInfoDB
  this.checkcookie = function(callback) {
    geolocationCookie = getCookie('geolocation');
    if (!geolocationCookie) {
      getGeolocation(callback);
    } else {
      geodata = JSON.parse(geolocationCookie);
      callback();
    }
  }
  
  //Return a geolocation field
  this.getField = function(field) {
    try {
      return geodata[field];
    } catch(err) {}
  }
  
  //Request to IPInfoDB
  function getGeolocation(callback) {
    try {
      jQuery.ajax({
	      url: url,
	      cache: false,
	      crossDomain: true,
	      dataType: (jQuery.support.cors ? "json" : "jsonp"),
	      success: function(data){
			if (data['country_code'] == 'US') {
				geodata = data;
				JSONString = JSON.stringify(geodata);
				setCookie('geolocation', JSONString, 365);
				callback();
			}
	      },
	      error: function (request, status, error) { console.log(status + ", " + error); }
	});
    } catch(err) {}
  }
  
  //Set the cookie
  function setCookie(c_name, value, expire) {
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+expire);
    document.cookie = c_name+ "=" +escape(value) + ((expire==null) ? "" : ";expires="+exdate.toGMTString());
  }
  
  //Get the cookie content
  function getCookie(c_name) {
    if (document.cookie.length > 0 ) {
      c_start=document.cookie.indexOf(c_name + "=");
      if (c_start != -1){
        c_start=c_start + c_name.length+1;
        c_end=document.cookie.indexOf(";",c_start);
        if (c_end == -1) {
          c_end=document.cookie.length;
        }
        return unescape(document.cookie.substring(c_start,c_end));
      }
    }
    return '';
  }
}

var visitorGeolocation = new geolocate();

//Check for cookie and run a callback function to execute after geolocation is read either from cookie or IPInfoDB API
//$(document).ready(function(){alert(visitorGeolocation.getField('countryCode'))}) doesnt work with google Chrome, this is why a callback is used instead
var callback = function() {
	if (jQuery.inArray(visitorGeolocation.getField('region_code'), ["ND","SD","MN","IA","NE","WI","IL","IN","MI","OH"]) !== -1) {
		jQuery("#shipping-ad").html('<div style="background-color: #fec; border-radius: 15px; padding: 10px; margin-bottom: 10px; background-image: url(\'/images/csh_truck.png\'); background-repeat: no-repeat; background-position: right"><img style="float: left;" src="/images/free_shipping.png"><h3>You may qualify for free shipping!</h3><p>We are shipping all standard orders over $50 free to Illinois, Indiana, Iowa, Michigan, Minnesota, Nebraska, North Dakota, Ohio, South Dakota, and Wisconsin.</p><p>Note: Only applies to standard delivery (ground) shipments; does <em>not</em> include expedited shipments, cabinet orders, or orders containing over-size items that ship as truckline freight.</p></div>');
		//alert("You are in " + visitorGeolocation.getField('region_name'));
	}
};
jQuery(document).ready(function() { visitorGeolocation.checkcookie(callback) });