function BezierPoint(t, X0, Y0, X1, Y1, X2, Y2, X3, Y3) {
//Then for any t in [0,1] you get a point on the curve given by the coordinates
	var x = Math.pow((1-t), 3) * X0 + 3*Math.pow((1-t),2) * t * X1 + 3*(1-t) * Math.pow(t,2) * X2 + Math.pow(t,3) * X3;
	var y = Math.pow((1-t), 3) * Y0 + 3*Math.pow((1-t),2) * t * Y1 + 3*(1-t) * Math.pow(t,2) * Y2 + Math.pow(t,3) * Y3;
	return [x,y];
}

function lineDistance( x1, y1, x2, y2 ) {
    var xs = x2 - x1;
    var ys = y2 - y1;    
    xs = xs * xs;
    ys = ys * ys;
    return Math.sqrt( xs + ys );
}

function BezierLength(X0, Y0, X1, Y1, X2, Y2, X3, Y3) {
	var len = 0;
	for (var i = 0; i < 100; i++) {
		var pt1 = BezierPoint(i/100,X0, Y0, X1, Y1, X2, Y2, X3, Y3);
		var pt2 = BezierPoint((i/100 + 0.01),X0, Y0, X1, Y1, X2, Y2, X3, Y3);
		len = len + lineDistance(pt1[0], pt1[1], pt2[0], pt2[1]);
	}
	return len;
}