;if(typeof(jQueryIWD) == "undefined"){if(typeof(jQuery) != "undefined") {jQueryIWD = jQuery;}} $ji = jQueryIWD;
$ji(document).ready(function () {
    $ji('.qv-design-colpick').each(function () {
        var background = "#" + $ji(this).val();
        var text_color = ("012345678".indexOf(background[1]) !== -1) ? "#FFFFFF" : "#000000";
        $ji(this).css('background-color', background).css('color', text_color);
    });

    var currentColor = "ffffff";
    $ji('.qv-design-colpick').on('click',function () {
        currentColor = $ji(this).css('background-color');
        currentColor = rgb2hex(currentColor);
    }).colpick({
            onBeforeShow: function () {
                $ji(this).colpickSetColor(currentColor);
            },
            colorScheme: 'light',
            layout: 'rgbhex',
            onSubmit: function (hsb, hex, rgb, el) {
                $ji(el).colpickHide();
                $ji(el).val(hex.toUpperCase());
                var text_color = ("012345678".indexOf(hex[0]) !== -1) ? "#FFFFFF" : "#000000";
                $ji(el).css('background-color', '#' + hex).css('color', text_color);
            }
        });

    function rgb2hex(rgb) {
        rgb = rgb.match(/^rgb\w*\((\d+),\s*(\d+),\s*(\d+)/);
        return "#" +
            ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
            ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
            ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2);
    }
});
