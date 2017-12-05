/********************************************************************************
 * CUSTOM SERVICE HARDWARE CODE LICENSE
 * ------------------------------------
 * Copyright 2013 Custom Service Hardware, Inc.
 * All Rights Reserved
 *
 * NOTICE: This software is the intellectual property
 * of Custom Service Hardware, Inc. This code may not
 * be used, copied, modified, or distributed without
 * express written permission of Custom Service
 * Hardware.
 */
// ###############################################
// three-apply.js - Interacts with THREE.js engine
// ###############################################
// NOTES:
// * Y-axis is up in THREE.js as well as WebGL
//
//

// ############## GLOBALS #########################
var WIDTH, HEIGHT;
var container, renderer, camera, scene;
var controls, Tfcontrol, editor, thisObject;
var numberOfStairs;
var IBC2012 = true;
var axis = new THREE.Vector3(0,1,0);
var base_material;
var maxAnisotropy;
var sectionCount;
var balusterOptions = [];
var newelOptions = [];
var sprite;
var projector;
var objects = [];
var floor_size = 400;
var mouse = new THREE.Vector2(),
offset = new THREE.Vector3(), INTERSECTED, SELECTED;
var screenToInchRatio = 0.48;
var z; // test - place holder for array of objects
var wallBracket;

// #################################################


// ################ DEFINITIONS ####################
var def_verticalVolute = {points: [{x: 225,y: 84.62925},{x: 141.0027,y: 85.98086},{x: 124.2885,y: 92.2511},{x: 110.2796,y: 102.663},{x: 99.93589,y: 116.2313},{x: 93.89085,y: 131.769},{x: 92.4158,y: 147.9891},{x: 95.41489,y: 163.6096},{x: 102.4489,y: 177.4536},{x: 112.7854,y: 188.5369},{x: 125.4691,y: 196.1359},{x: 139.4078,y: 199.8326},{x: 153.4637,y: 199.5329},{x: 166.5456,y: 195.4586},{x: 177.692,y: 188.1148},{x: 186.1406,y: 178.2362},{x: 191.3784,y: 166.7179},{x: 193.1693,y: 154.5373},{x: 191.5593,y: 142.672},{x: 186.8577,y: 132.0233},{x: 179.5992,y: 123.3487},{x: 170.4897,y: 117.2096},{x: 160.3408,y: 113.9374},{x: 150,y: 113.6197},{x: 140.2813,y: 116.1069},{x: 131.9023,y: 121.0376},{x: 125.4324,y: 127.8792},{x: 121.2563,y: 135.9808},{x: 119.5539,y: 144.6315},{x: 120.298,y: 153.1218},{x: 123.2697,y: 160.7997},{x: 128.0872,y: 167.1202},{x: 134.2472,y: 171.6818},{x: 141.1735,y: 174.2505},{x: 148.2681,y: 174.7673},{x: 154.9614,y: 173.3414},{x: 160.7566,y: 170.2302},{x: 165.2656,y: 165.808},{x: 168.2328,y: 160.5267},{x: 169.5471,y: 154.8736},{x: 169.24,y: 149.3281},{x: 167.4729,y: 144.3227},{x: 164.5138,y: 140.2103},{x: 160.7061,y: 137.241},{x: 156.4341,y: 135.5488},{x: 152.0871,y: 135.1499},{x: 148.0255,y: 135.9504},{x: 144.5524,y: 137.7645},{x: 141.8923,y: 140.3376},{x: 140.1779,y: 143.3749},{x: 139.447,y: 146.5711},{x: 139.6476,y: 149.6385},{x: 140.6506,y: 152.3311},{x: 142.2692,y: 154.4633},{x: 144.2816,y: 155.9216},{x: 146.4547,y: 156.6676},{x: 148.5685,y: 156.7349},{x: 150.4348,y: 156.2182},{x: 151.9138,y: 155.2581},{x: 152.9225,y: 154.0224},{x: 153.4378,y: 152.6859},{x: 153.4932,y: 151.4114},{x: 153.1692,y: 150.3331},{x: 152.5802,y: 149.545},{x: 151.8583,y: 149.0936},{x: 151.1365,y: 148.9767},{x: 150.5327,y: 149.1474},{x: 150.1366,y: 149.5236},{x: 150,y: 150}]};
var def_wallBracket = "o Plane|v 0.228582 0.245979 0.127102|v -0.237749 0.245979 0.127102|v 0.228582 0.712310 0.127102|v -0.237749 0.712310 0.127102|v 0.185874 0.288687 0.525355|v -0.195041 0.288687 0.525355|v 0.185874 0.669602 0.525356|v -0.195041 0.669602 0.525356|v 0.118761 0.492253 0.999277|v -0.120256 0.484857 1.005300|v 0.111089 0.731074 0.988056|v -0.127928 0.723678 0.994078|v 0.128868 0.731840 1.286297|v -0.110150 0.724445 1.292320|v 0.121196 0.970661 1.275076|v -0.117822 0.963266 1.281098|v 0.131810 0.990021 1.420400|v -0.107207 0.982626 1.426423|v 0.118965 1.077883 1.283545|v -0.103380 1.070400 1.292608|v 0.118965 1.077883 1.273500|v 0.130269 1.311683 1.390599|v -0.108748 1.304287 1.396622|v 0.117424 1.332182 1.253744|v -0.104921 1.324699 1.262806|v 0.526183 1.324293 1.482223|v -0.485064 1.316897 1.488245|v 0.513338 1.344792 1.161094|v -0.481236 1.337309 1.170156|v 0.525476 1.426281 1.488760|v -0.485771 1.418886 1.494783|v 0.512630 1.446781 1.167631|v -0.481944 1.439297 1.176694|usemtl None|s off|f 2 4 3 1|f 5 7 11 9|f 4 2 6 8|f 3 4 8 7|f 2 1 5 6|f 1 3 7 5|f 12 10 14 16|f 8 6 10 12|f 7 8 12 11|f 6 5 9 10|f 13 15 19 17|f 11 12 16 15|f 10 9 13 14|f 9 11 15 13|f 17 19 24 22|f 16 14 18 20|f 15 16 20 19|f 14 13 17 18|f 23 22 26 27|f 20 18 23 25|f 19 20 25 24|f 18 17 22 23|f 29 27 31 33|f 22 24 28 26|f 25 23 27 29|f 24 25 29 28|f 31 30 32 33|f 28 29 33 32|f 27 26 30 31|f 26 28 32 30|l 19 21|o Cylinder|v 0.000000 1.000000 -0.024523|v -0.000000 0.878059 0.125001|v 0.342020 0.939693 -0.024523|v 0.300314 0.825106 0.125001|v 0.642788 0.766044 -0.024523|v 0.564406 0.672632 0.125000|v 0.866025 0.500000 -0.024523|v 0.760422 0.439030 0.125000|v 0.984808 0.173648 -0.024523|v 0.864720 0.152473 0.125000|v 0.984808 -0.173648 -0.024523|v 0.864720 -0.152474 0.125000|v 0.866025 -0.500000 -0.024524|v 0.760422 -0.439030 0.125000|v 0.642788 -0.766044 -0.024524|v 0.564406 -0.672633 0.125000|v 0.342020 -0.939693 -0.024524|v 0.300314 -0.825106 0.124999|v 0.000000 -1.000000 -0.024524|v 0.000000 -0.878059 0.124999|v -0.342020 -0.939693 -0.024524|v -0.300314 -0.825106 0.124999|v -0.642787 -0.766045 -0.024524|v -0.564405 -0.672633 0.125000|v -0.866025 -0.500000 -0.024524|v -0.760421 -0.439030 0.125000|v -0.984808 -0.173649 -0.024523|v -0.864720 -0.152474 0.125000|v -0.984808 0.173648 -0.024523|v -0.864720 0.152473 0.125000|v -0.866026 0.500000 -0.024523|v -0.760422 0.439029 0.125000|v -0.642788 0.766044 -0.024523|v -0.564406 0.672632 0.125000|v -0.342021 0.939692 -0.024523|v -0.300315 0.825106 0.125001|usemtl None|s off|f 34 35 37 36|f 36 37 39 38|f 38 39 41 40|f 40 41 43 42|f 42 43 45 44|f 44 45 47 46|f 46 47 49 48|f 48 49 51 50|f 50 51 53 52|f 52 53 55 54|f 54 55 57 56|f 56 57 59 58|f 58 59 61 60|f 60 61 63 62|f 62 63 65 64|f 64 65 67 66|f 37 35 69 67 65 63 61 59 57 55 53 51 49 47 45 43 41 39|f 68 69 35 34|f 66 67 69 68|f 34 36 38 40 42 44 46 48 50 52 54 56 58 60 62 64 66 68|";

// #################################################

function loadModels() {
	var bLoader = new THREE.OBJLoader();
	wallBracket = bLoader.loadData( def_wallBracket );
	scene.add(wallBracket);
}

function render() {

	var delta = clock.getDelta();

	controls.update();
	Tfcontrol.update( delta );
	console.log("update");
	renderer.render( scene, camera );
}

function inchesToScreen(inches) {
	"use strict";
	return (inches / screenToInchRatio);
}

function screenToInches(screen) {
	"use strict";
	return (screen * screenToInchRatio);
}

// Base "Point" object
function Point(x, y) {
	"use strict";
	this.x = x;
	this.y = y;
} // End Point

// Base "Zone" object - shape and size determined by the user
function Zone (typ, px, py, nrise, nrun, nwidth, nrotation, nrailHeight) {
	"use strict";
	
	var type = typ; //type of zone
	var color = "#3399FF";
	this.rise = nrise;
	var run = nrun;
	var width = nwidth;
	var x = px; // Horizontal location
	var y = py; // Vertical location
	var base_x = px;
	var base_y = py;
	var shape; //actual drawn path
	var rotation = nrotation;
	var height = 0;
	var id = guid();
	var railHeight = nrailHeight;
	var bbox = [];
	bbox.push(new Point(x, y));
	bbox.push(new Point(x, y + width));
	bbox.push(new Point(x + run, y + width));
	bbox.push(new Point(x + run, y));
	
	var useBothRails = false;
	var useRightRail = true;
	var railProfile = { points: [{x: 0, y: 0},{x: 1.875, y: 0},{x: 1.875, y: 0.25},{x: 2, y: 0.375},{x: 1.875, y: 0.5},{x: 1.875, y: 0.75},{x: 2, y: 1.125},{x: 2.125, y: 1.625},{x: 2.125, y: 1.875},{x: 2, y: 2.125},{x: 1.75, y: 2.25},{x: 0.125, y: 2.25},{x: -0.125, y: 2.125},{x: -0.25, y: 1.875},{x: -0.25, y: 1.625},{x: -0.125, y: 1},{x: 0, y: 0.75},{x: 0, y: 0.5},{x: -0.125, y: 0.375},{x: 0, y: 0.25}]};

    var scope = this;
		
	function getId() {
		return id;
	}
	this.id = getId();
	
	function getY() {
		return y;
	}
	this.getY = getY();
	
	var mesh = buildShape(type, inchesToScreen(this.rise), inchesToScreen(run), inchesToScreen(width), inchesToScreen(x), inchesToScreen(y), inchesToScreen(height), inchesToScreen(railHeight), railProfile.points, useBothRails, useRightRail);
	//scene.add(mesh);
	addObject(mesh);
			
	this.build = function build() {
					
			scene.remove(mesh);
			mesh = buildShape(type, inchesToScreen(scope.rise), inchesToScreen(run), inchesToScreen(width), inchesToScreen(x), inchesToScreen(y), inchesToScreen(height), inchesToScreen(railHeight), railProfile.points, useBothRails, useRightRail);
			addObject(mesh);
									
			renderer.render( scene, camera );
		}
	
			
} // End Zone()

function cameraUpdate() {
	"use strict";
	// Setup initial variables
	WIDTH = document.getElementById('container').clientWidth;
	HEIGHT = document.getElementById('container').clientHeight;
	var VIEW_ANGLE = 45, ASPECT = WIDTH / HEIGHT, NEAR = 0.1, FAR = 10000;
	camera.aspect = ASPECT;
	camera.updateProjectionMatrix();
	renderer.setSize( WIDTH, HEIGHT );
	renderer.render( scene, camera );
}

$(document).ready(function () {
	"use strict";
		
	$('#container').css({'width': window.innerWidth - 64, 'height': window.innerHeight - 128});
		
	// Check to see if the browser supports WebGL... if not, display the message.
	if ( ! Detector.webgl ) { Detector.addGetWebGLMessage(); }
	
	// Setup initial variables
	WIDTH = document.getElementById('container').clientWidth;
	HEIGHT = document.getElementById('container').clientHeight;
	var VIEW_ANGLE = 45, ASPECT = WIDTH / HEIGHT, NEAR = 0.1, FAR = 10000;
	container = document.getElementById('container'); // Rendering container
	renderer = new THREE.WebGLRenderer({preserveDrawingBuffer: true, antialias: true}); // Create renderer
	camera =  new THREE.PerspectiveCamera(VIEW_ANGLE, ASPECT, NEAR, FAR);
	scene = new THREE.Scene();
	
	// setup camera
	scene.add(camera);
	// the camera starts at 0,0,0
	// so pull it back and give it a good location
	camera.position.y = 175; //- (WIDTH / 2);
	camera.position.x = 0;
	camera.position.z = 550;
	camera.lookAt(new THREE.Vector3(0, 0, 0));
	
	controls = new THREE.OrbitControls( camera, renderer.domElement );
	controls.maxPolarAngle = (Math.PI / 2) - 0.03;
	controls.addEventListener( 'change', render );
	
	// editor = new THREE.EditorControls( camera, renderer.domElement );
	// editor.addEventListener( 'change', render );
	
	// add subtle ambient lighting
	var ambientLight = new THREE.AmbientLight(0x000000);
	scene.add(ambientLight);
	
	renderer.shadowMapEnabled = true;
	renderer.shadowMapSoft = true;
	renderer.shadowMapType = THREE.PCFShadowMap;
	
	// ***********************************************************
	// Setup directional lighting - this is a parallel stream of
	// light. Can cast shadows, but only mesh by mesh, not on 
	// whole Object3Ds.
	// ***********************************************************
	var directionalLight = new THREE.DirectionalLight(0xffffff, 1);
	directionalLight.position.set(-275, 450, 275);
	directionalLight.castShadow = true;
	//directionalLight.shadowCameraVisible = true;
	directionalLight.intensity = 1.0;
    directionalLight.castShadow = true;
    directionalLight.shadowDarkness = 0.5;
	directionalLight.shadowBias = 0.0001;
    directionalLight.shadowMapWidth = directionalLight.shadowMapHeight = 2048;
	directionalLight.shadowCameraNear = 150;
    directionalLight.shadowCameraFar = 750;
    directionalLight.shadowCameraLeft = -300;
    directionalLight.shadowCameraRight = 300;
    directionalLight.shadowCameraTop = 300;
    directionalLight.shadowCameraBottom = -300;
	scene.add(directionalLight);
	// *************************************************************
		
		
	// put ground down
	var groundTex = base64Texture( checkTex );
	groundTex.repeat.set( 8, 8 );
	groundTex.anisotropy = maxAnisotropy;
	groundTex.wrapS = groundTex.wrapT = THREE.RepeatWrapping;
	var solidGround = new THREE.Mesh(
	new THREE.PlaneGeometry( floor_size, floor_size, 5, 5 ),
	new THREE.MeshPhongMaterial( { color: 0xffffff, side: THREE.DoubleSide, map: groundTex } ) 
	);
	solidGround.rotation.x = 270 * (Math.PI / 180);
	solidGround.receiveShadow = true;
	solidGround.castShadow = false;
	scene.add(solidGround);
	
	// start the renderer
	renderer.setSize(WIDTH, HEIGHT);
	
	// attach the render-supplied DOM element
	container.appendChild(renderer.domElement);
	
	
	maxAnisotropy = renderer.getMaxAnisotropy();
	var texture1 = base64Texture( woodTex );//THREE.ImageUtils.loadTexture( 'image/wood3.jpg' );
	texture1.anisotropy = maxAnisotropy;
	texture1.wrapS = texture1.wrapT = THREE.RepeatWrapping;
	texture1.repeat.set( 0.01, 0.01 );
	base_material = new THREE.MeshPhongMaterial( { color: 0xffffff, map: texture1 } );


	projector = new THREE.Projector();
	
	renderer.domElement.addEventListener( 'mousemove', onDocumentMouseMove, false );
	renderer.domElement.addEventListener( 'mousedown', onDocumentMouseDown, false );
	renderer.domElement.addEventListener( 'mouseup', onDocumentMouseUp, false );
	
	createTransformHandle();

	// TODO: Test data...
	balusterOptions = balusterOptions.concat({"points":[{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":1.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":1.0},{"IsEmpty":false,"x":5.0,"y":67.0},{"IsEmpty":false,"x":6.0,"y":68.0},{"IsEmpty":false,"x":9.0,"y":70.0},{"IsEmpty":false,"x":9.0,"y":71.0},{"IsEmpty":false,"x":11.0,"y":73.0},{"IsEmpty":false,"x":11.0,"y":74.0},{"IsEmpty":false,"x":11.0,"y":76.0},{"IsEmpty":false,"x":10.0,"y":77.0},{"IsEmpty":false,"x":9.0,"y":79.0},{"IsEmpty":false,"x":8.0,"y":80.0},{"IsEmpty":false,"x":6.0,"y":82.0},{"IsEmpty":false,"x":6.0,"y":83.0},{"IsEmpty":false,"x":7.0,"y":92.0},{"IsEmpty":false,"x":6.0,"y":93.0},{"IsEmpty":false,"x":6.0,"y":103.0},{"IsEmpty":false,"x":5.0,"y":104.0},{"IsEmpty":false,"x":5.0,"y":144.0},{"IsEmpty":false,"x":6.0,"y":145.0},{"IsEmpty":false,"x":6.0,"y":158.0},{"IsEmpty":false,"x":7.0,"y":159.0},{"IsEmpty":false,"x":7.0,"y":171.0},{"IsEmpty":false,"x":8.0,"y":172.0},{"IsEmpty":false,"x":8.0,"y":188.0},{"IsEmpty":false,"x":9.0,"y":189.0},{"IsEmpty":false,"x":9.0,"y":203.0},{"IsEmpty":false,"x":10.0,"y":204.0},{"IsEmpty":false,"x":10.0,"y":245.0},{"IsEmpty":false,"x":9.0,"y":246.0},{"IsEmpty":false,"x":9.0,"y":253.0},{"IsEmpty":false,"x":8.0,"y":254.0},{"IsEmpty":false,"x":8.0,"y":258.0},{"IsEmpty":false,"x":7.0,"y":259.0},{"IsEmpty":false,"x":8.0,"y":262.0},{"IsEmpty":false,"x":10.0,"y":265.0},{"IsEmpty":false,"x":8.0,"y":263.0},{"IsEmpty":false,"x":10.0,"y":266.0},{"IsEmpty":false,"x":10.0,"y":272.0},{"IsEmpty":false,"x":8.0,"y":273.0},{"IsEmpty":false,"x":7.0,"y":277.0},{"IsEmpty":false,"x":8.0,"y":279.0},{"IsEmpty":false,"x":9.0,"y":282.0},{"IsEmpty":false,"x":8.0,"y":281.0},{"IsEmpty":false,"x":9.0,"y":284.0},{"IsEmpty":false,"x":10.0,"y":286.0},{"IsEmpty":false,"x":10.0,"y":287.0},{"IsEmpty":false,"x":10.0,"y":288.0},{"IsEmpty":false,"x":10.0,"y":298.0},{"IsEmpty":false,"x":9.0,"y":299.0},{"IsEmpty":false,"x":9.0,"y":302.0},{"IsEmpty":false,"x":8.0,"y":303.0},{"IsEmpty":false,"x":8.0,"y":308.0},{"IsEmpty":false,"x":7.0,"y":307.0},{"IsEmpty":false,"x":8.0,"y":313.0},{"IsEmpty":false,"x":7.0,"y":314.0},{"IsEmpty":false,"x":8.0,"y":317.0},{"IsEmpty":false,"x":7.0,"y":316.0},{"IsEmpty":false,"x":10.0,"y":321.0},{"IsEmpty":false,"x":9.0,"y":320.0},{"IsEmpty":false,"x":10.0,"y":322.0},{"IsEmpty":false,"x":10.0,"y":323.0},{"IsEmpty":false,"x":11.0,"y":326.0},{"IsEmpty":false,"x":10.0,"y":325.0},{"IsEmpty":false,"x":11.0,"y":510.0}],"sections":[{"Points":[{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":1.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":1.0},{"IsEmpty":false,"x":5.0,"y":67.0},{"IsEmpty":false,"x":6.0,"y":68.0},{"IsEmpty":false,"x":9.0,"y":70.0},{"IsEmpty":false,"x":9.0,"y":71.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":9.0,"y":71.0},{"IsEmpty":false,"x":11.0,"y":73.0},{"IsEmpty":false,"x":11.0,"y":74.0},{"IsEmpty":false,"x":11.0,"y":76.0},{"IsEmpty":false,"x":10.0,"y":77.0},{"IsEmpty":false,"x":9.0,"y":79.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":9.0,"y":79.0},{"IsEmpty":false,"x":8.0,"y":80.0},{"IsEmpty":false,"x":6.0,"y":82.0},{"IsEmpty":false,"x":6.0,"y":83.0},{"IsEmpty":false,"x":7.0,"y":92.0},{"IsEmpty":false,"x":6.0,"y":93.0},{"IsEmpty":false,"x":6.0,"y":103.0},{"IsEmpty":false,"x":5.0,"y":104.0},{"IsEmpty":false,"x":5.0,"y":144.0},{"IsEmpty":false,"x":6.0,"y":145.0},{"IsEmpty":false,"x":6.0,"y":158.0},{"IsEmpty":false,"x":7.0,"y":159.0},{"IsEmpty":false,"x":7.0,"y":171.0},{"IsEmpty":false,"x":8.0,"y":172.0},{"IsEmpty":false,"x":8.0,"y":188.0},{"IsEmpty":false,"x":9.0,"y":189.0},{"IsEmpty":false,"x":9.0,"y":203.0},{"IsEmpty":false,"x":10.0,"y":204.0},{"IsEmpty":false,"x":10.0,"y":245.0},{"IsEmpty":false,"x":9.0,"y":246.0},{"IsEmpty":false,"x":9.0,"y":253.0},{"IsEmpty":false,"x":8.0,"y":254.0},{"IsEmpty":false,"x":8.0,"y":258.0},{"IsEmpty":false,"x":7.0,"y":259.0},{"IsEmpty":false,"x":8.0,"y":262.0},{"IsEmpty":false,"x":10.0,"y":265.0},{"IsEmpty":false,"x":8.0,"y":263.0},{"IsEmpty":false,"x":10.0,"y":266.0},{"IsEmpty":false,"x":10.0,"y":272.0},{"IsEmpty":false,"x":8.0,"y":273.0},{"IsEmpty":false,"x":7.0,"y":277.0},{"IsEmpty":false,"x":8.0,"y":279.0},{"IsEmpty":false,"x":9.0,"y":282.0},{"IsEmpty":false,"x":8.0,"y":281.0},{"IsEmpty":false,"x":9.0,"y":284.0},{"IsEmpty":false,"x":10.0,"y":286.0},{"IsEmpty":false,"x":10.0,"y":287.0},{"IsEmpty":false,"x":10.0,"y":288.0},{"IsEmpty":false,"x":10.0,"y":298.0},{"IsEmpty":false,"x":9.0,"y":299.0},{"IsEmpty":false,"x":9.0,"y":302.0},{"IsEmpty":false,"x":8.0,"y":303.0},{"IsEmpty":false,"x":8.0,"y":308.0},{"IsEmpty":false,"x":7.0,"y":307.0},{"IsEmpty":false,"x":8.0,"y":313.0},{"IsEmpty":false,"x":7.0,"y":314.0},{"IsEmpty":false,"x":8.0,"y":317.0},{"IsEmpty":false,"x":7.0,"y":316.0},{"IsEmpty":false,"x":10.0,"y":321.0},{"IsEmpty":false,"x":9.0,"y":320.0},{"IsEmpty":false,"x":10.0,"y":322.0},{"IsEmpty":false,"x":10.0,"y":323.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":10.0,"y":323.0},{"IsEmpty":false,"x":11.0,"y":326.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":11.0,"y":326.0},{"IsEmpty":false,"x":10.0,"y":325.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":10.0,"y":325.0},{"IsEmpty":false,"x":11.0,"y":510.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":4}],"height":34.0,"width":1.75,"geometry":""});
    balusterOptions = balusterOptions.concat({"points":[{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":1.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":1.0},{"IsEmpty":false,"x":5.0,"y":67.0},{"IsEmpty":false,"x":6.0,"y":68.0},{"IsEmpty":false,"x":9.0,"y":70.0},{"IsEmpty":false,"x":9.0,"y":71.0},{"IsEmpty":false,"x":11.0,"y":73.0},{"IsEmpty":false,"x":11.0,"y":74.0},{"IsEmpty":false,"x":11.0,"y":76.0},{"IsEmpty":false,"x":10.0,"y":77.0},{"IsEmpty":false,"x":9.0,"y":79.0},{"IsEmpty":false,"x":8.0,"y":80.0},{"IsEmpty":false,"x":6.0,"y":82.0},{"IsEmpty":false,"x":6.0,"y":83.0},{"IsEmpty":false,"x":7.0,"y":92.0},{"IsEmpty":false,"x":6.0,"y":93.0},{"IsEmpty":false,"x":6.0,"y":103.0},{"IsEmpty":false,"x":5.0,"y":104.0},{"IsEmpty":false,"x":5.0,"y":144.0},{"IsEmpty":false,"x":6.0,"y":145.0},{"IsEmpty":false,"x":6.0,"y":158.0},{"IsEmpty":false,"x":7.0,"y":159.0},{"IsEmpty":false,"x":7.0,"y":171.0},{"IsEmpty":false,"x":8.0,"y":172.0},{"IsEmpty":false,"x":8.0,"y":188.0},{"IsEmpty":false,"x":9.0,"y":189.0},{"IsEmpty":false,"x":9.0,"y":203.0},{"IsEmpty":false,"x":10.0,"y":204.0},{"IsEmpty":false,"x":10.0,"y":245.0},{"IsEmpty":false,"x":9.0,"y":246.0},{"IsEmpty":false,"x":9.0,"y":253.0},{"IsEmpty":false,"x":8.0,"y":254.0},{"IsEmpty":false,"x":8.0,"y":258.0},{"IsEmpty":false,"x":7.0,"y":259.0},{"IsEmpty":false,"x":8.0,"y":262.0},{"IsEmpty":false,"x":10.0,"y":265.0},{"IsEmpty":false,"x":8.0,"y":263.0},{"IsEmpty":false,"x":10.0,"y":266.0},{"IsEmpty":false,"x":10.0,"y":272.0},{"IsEmpty":false,"x":8.0,"y":273.0},{"IsEmpty":false,"x":7.0,"y":277.0},{"IsEmpty":false,"x":8.0,"y":279.0},{"IsEmpty":false,"x":9.0,"y":282.0},{"IsEmpty":false,"x":8.0,"y":281.0},{"IsEmpty":false,"x":9.0,"y":284.0},{"IsEmpty":false,"x":10.0,"y":286.0},{"IsEmpty":false,"x":10.0,"y":287.0},{"IsEmpty":false,"x":10.0,"y":288.0},{"IsEmpty":false,"x":10.0,"y":298.0},{"IsEmpty":false,"x":9.0,"y":299.0},{"IsEmpty":false,"x":9.0,"y":302.0},{"IsEmpty":false,"x":8.0,"y":303.0},{"IsEmpty":false,"x":8.0,"y":308.0},{"IsEmpty":false,"x":7.0,"y":307.0},{"IsEmpty":false,"x":8.0,"y":313.0},{"IsEmpty":false,"x":7.0,"y":314.0},{"IsEmpty":false,"x":8.0,"y":317.0},{"IsEmpty":false,"x":7.0,"y":316.0},{"IsEmpty":false,"x":10.0,"y":321.0},{"IsEmpty":false,"x":9.0,"y":320.0},{"IsEmpty":false,"x":10.0,"y":322.0},{"IsEmpty":false,"x":10.0,"y":323.0},{"IsEmpty":false,"x":11.0,"y":326.0},{"IsEmpty":false,"x":10.0,"y":325.0},{"IsEmpty":false,"x":11.0,"y":510.0}],"sections":[{"Points":[{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":1.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":1.0},{"IsEmpty":false,"x":5.0,"y":67.0},{"IsEmpty":false,"x":6.0,"y":68.0},{"IsEmpty":false,"x":9.0,"y":70.0},{"IsEmpty":false,"x":9.0,"y":71.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":9.0,"y":71.0},{"IsEmpty":false,"x":11.0,"y":73.0},{"IsEmpty":false,"x":11.0,"y":74.0},{"IsEmpty":false,"x":11.0,"y":76.0},{"IsEmpty":false,"x":10.0,"y":77.0},{"IsEmpty":false,"x":9.0,"y":79.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":9.0,"y":79.0},{"IsEmpty":false,"x":8.0,"y":80.0},{"IsEmpty":false,"x":6.0,"y":82.0},{"IsEmpty":false,"x":6.0,"y":83.0},{"IsEmpty":false,"x":7.0,"y":92.0},{"IsEmpty":false,"x":6.0,"y":93.0},{"IsEmpty":false,"x":6.0,"y":103.0},{"IsEmpty":false,"x":5.0,"y":104.0},{"IsEmpty":false,"x":5.0,"y":144.0},{"IsEmpty":false,"x":6.0,"y":145.0},{"IsEmpty":false,"x":6.0,"y":158.0},{"IsEmpty":false,"x":7.0,"y":159.0},{"IsEmpty":false,"x":7.0,"y":171.0},{"IsEmpty":false,"x":8.0,"y":172.0},{"IsEmpty":false,"x":8.0,"y":188.0},{"IsEmpty":false,"x":9.0,"y":189.0},{"IsEmpty":false,"x":9.0,"y":203.0},{"IsEmpty":false,"x":10.0,"y":204.0},{"IsEmpty":false,"x":10.0,"y":245.0},{"IsEmpty":false,"x":9.0,"y":246.0},{"IsEmpty":false,"x":9.0,"y":253.0},{"IsEmpty":false,"x":8.0,"y":254.0},{"IsEmpty":false,"x":8.0,"y":258.0},{"IsEmpty":false,"x":7.0,"y":259.0},{"IsEmpty":false,"x":8.0,"y":262.0},{"IsEmpty":false,"x":10.0,"y":265.0},{"IsEmpty":false,"x":8.0,"y":263.0},{"IsEmpty":false,"x":10.0,"y":266.0},{"IsEmpty":false,"x":10.0,"y":272.0},{"IsEmpty":false,"x":8.0,"y":273.0},{"IsEmpty":false,"x":7.0,"y":277.0},{"IsEmpty":false,"x":8.0,"y":279.0},{"IsEmpty":false,"x":9.0,"y":282.0},{"IsEmpty":false,"x":8.0,"y":281.0},{"IsEmpty":false,"x":9.0,"y":284.0},{"IsEmpty":false,"x":10.0,"y":286.0},{"IsEmpty":false,"x":10.0,"y":287.0},{"IsEmpty":false,"x":10.0,"y":288.0},{"IsEmpty":false,"x":10.0,"y":298.0},{"IsEmpty":false,"x":9.0,"y":299.0},{"IsEmpty":false,"x":9.0,"y":302.0},{"IsEmpty":false,"x":8.0,"y":303.0},{"IsEmpty":false,"x":8.0,"y":308.0},{"IsEmpty":false,"x":7.0,"y":307.0},{"IsEmpty":false,"x":8.0,"y":313.0},{"IsEmpty":false,"x":7.0,"y":314.0},{"IsEmpty":false,"x":8.0,"y":317.0},{"IsEmpty":false,"x":7.0,"y":316.0},{"IsEmpty":false,"x":10.0,"y":321.0},{"IsEmpty":false,"x":9.0,"y":320.0},{"IsEmpty":false,"x":10.0,"y":322.0},{"IsEmpty":false,"x":10.0,"y":323.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":10.0,"y":323.0},{"IsEmpty":false,"x":11.0,"y":326.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":11.0,"y":326.0},{"IsEmpty":false,"x":10.0,"y":325.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":10.0,"y":325.0},{"IsEmpty":false,"x":11.0,"y":510.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":4}],"height":38.0,"width":1.75,"geometry":""});
    balusterOptions = balusterOptions.concat({"points":[{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":1.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":1.0},{"IsEmpty":false,"x":5.0,"y":67.0},{"IsEmpty":false,"x":6.0,"y":68.0},{"IsEmpty":false,"x":9.0,"y":70.0},{"IsEmpty":false,"x":9.0,"y":71.0},{"IsEmpty":false,"x":11.0,"y":73.0},{"IsEmpty":false,"x":11.0,"y":74.0},{"IsEmpty":false,"x":11.0,"y":76.0},{"IsEmpty":false,"x":10.0,"y":77.0},{"IsEmpty":false,"x":9.0,"y":79.0},{"IsEmpty":false,"x":8.0,"y":80.0},{"IsEmpty":false,"x":6.0,"y":82.0},{"IsEmpty":false,"x":6.0,"y":83.0},{"IsEmpty":false,"x":7.0,"y":92.0},{"IsEmpty":false,"x":6.0,"y":93.0},{"IsEmpty":false,"x":6.0,"y":103.0},{"IsEmpty":false,"x":5.0,"y":104.0},{"IsEmpty":false,"x":5.0,"y":144.0},{"IsEmpty":false,"x":6.0,"y":145.0},{"IsEmpty":false,"x":6.0,"y":158.0},{"IsEmpty":false,"x":7.0,"y":159.0},{"IsEmpty":false,"x":7.0,"y":171.0},{"IsEmpty":false,"x":8.0,"y":172.0},{"IsEmpty":false,"x":8.0,"y":188.0},{"IsEmpty":false,"x":9.0,"y":189.0},{"IsEmpty":false,"x":9.0,"y":203.0},{"IsEmpty":false,"x":10.0,"y":204.0},{"IsEmpty":false,"x":10.0,"y":245.0},{"IsEmpty":false,"x":9.0,"y":246.0},{"IsEmpty":false,"x":9.0,"y":253.0},{"IsEmpty":false,"x":8.0,"y":254.0},{"IsEmpty":false,"x":8.0,"y":258.0},{"IsEmpty":false,"x":7.0,"y":259.0},{"IsEmpty":false,"x":8.0,"y":262.0},{"IsEmpty":false,"x":10.0,"y":265.0},{"IsEmpty":false,"x":8.0,"y":263.0},{"IsEmpty":false,"x":10.0,"y":266.0},{"IsEmpty":false,"x":10.0,"y":272.0},{"IsEmpty":false,"x":8.0,"y":273.0},{"IsEmpty":false,"x":7.0,"y":277.0},{"IsEmpty":false,"x":8.0,"y":279.0},{"IsEmpty":false,"x":9.0,"y":282.0},{"IsEmpty":false,"x":8.0,"y":281.0},{"IsEmpty":false,"x":9.0,"y":284.0},{"IsEmpty":false,"x":10.0,"y":286.0},{"IsEmpty":false,"x":10.0,"y":287.0},{"IsEmpty":false,"x":10.0,"y":288.0},{"IsEmpty":false,"x":10.0,"y":298.0},{"IsEmpty":false,"x":9.0,"y":299.0},{"IsEmpty":false,"x":9.0,"y":302.0},{"IsEmpty":false,"x":8.0,"y":303.0},{"IsEmpty":false,"x":8.0,"y":308.0},{"IsEmpty":false,"x":7.0,"y":307.0},{"IsEmpty":false,"x":8.0,"y":313.0},{"IsEmpty":false,"x":7.0,"y":314.0},{"IsEmpty":false,"x":8.0,"y":317.0},{"IsEmpty":false,"x":7.0,"y":316.0},{"IsEmpty":false,"x":10.0,"y":321.0},{"IsEmpty":false,"x":9.0,"y":320.0},{"IsEmpty":false,"x":10.0,"y":322.0},{"IsEmpty":false,"x":10.0,"y":323.0},{"IsEmpty":false,"x":11.0,"y":326.0},{"IsEmpty":false,"x":10.0,"y":325.0},{"IsEmpty":false,"x":11.0,"y":510.0}],"sections":[{"Points":[{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":0.0},{"IsEmpty":false,"x":1.0,"y":0.0},{"IsEmpty":false,"x":5.0,"y":1.0},{"IsEmpty":false,"x":5.0,"y":67.0},{"IsEmpty":false,"x":6.0,"y":68.0},{"IsEmpty":false,"x":9.0,"y":70.0},{"IsEmpty":false,"x":9.0,"y":71.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":9.0,"y":71.0},{"IsEmpty":false,"x":11.0,"y":73.0},{"IsEmpty":false,"x":11.0,"y":74.0},{"IsEmpty":false,"x":11.0,"y":76.0},{"IsEmpty":false,"x":10.0,"y":77.0},{"IsEmpty":false,"x":9.0,"y":79.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":9.0,"y":79.0},{"IsEmpty":false,"x":8.0,"y":80.0},{"IsEmpty":false,"x":6.0,"y":82.0},{"IsEmpty":false,"x":6.0,"y":83.0},{"IsEmpty":false,"x":7.0,"y":92.0},{"IsEmpty":false,"x":6.0,"y":93.0},{"IsEmpty":false,"x":6.0,"y":103.0},{"IsEmpty":false,"x":5.0,"y":104.0},{"IsEmpty":false,"x":5.0,"y":144.0},{"IsEmpty":false,"x":6.0,"y":145.0},{"IsEmpty":false,"x":6.0,"y":158.0},{"IsEmpty":false,"x":7.0,"y":159.0},{"IsEmpty":false,"x":7.0,"y":171.0},{"IsEmpty":false,"x":8.0,"y":172.0},{"IsEmpty":false,"x":8.0,"y":188.0},{"IsEmpty":false,"x":9.0,"y":189.0},{"IsEmpty":false,"x":9.0,"y":203.0},{"IsEmpty":false,"x":10.0,"y":204.0},{"IsEmpty":false,"x":10.0,"y":245.0},{"IsEmpty":false,"x":9.0,"y":246.0},{"IsEmpty":false,"x":9.0,"y":253.0},{"IsEmpty":false,"x":8.0,"y":254.0},{"IsEmpty":false,"x":8.0,"y":258.0},{"IsEmpty":false,"x":7.0,"y":259.0},{"IsEmpty":false,"x":8.0,"y":262.0},{"IsEmpty":false,"x":10.0,"y":265.0},{"IsEmpty":false,"x":8.0,"y":263.0},{"IsEmpty":false,"x":10.0,"y":266.0},{"IsEmpty":false,"x":10.0,"y":272.0},{"IsEmpty":false,"x":8.0,"y":273.0},{"IsEmpty":false,"x":7.0,"y":277.0},{"IsEmpty":false,"x":8.0,"y":279.0},{"IsEmpty":false,"x":9.0,"y":282.0},{"IsEmpty":false,"x":8.0,"y":281.0},{"IsEmpty":false,"x":9.0,"y":284.0},{"IsEmpty":false,"x":10.0,"y":286.0},{"IsEmpty":false,"x":10.0,"y":287.0},{"IsEmpty":false,"x":10.0,"y":288.0},{"IsEmpty":false,"x":10.0,"y":298.0},{"IsEmpty":false,"x":9.0,"y":299.0},{"IsEmpty":false,"x":9.0,"y":302.0},{"IsEmpty":false,"x":8.0,"y":303.0},{"IsEmpty":false,"x":8.0,"y":308.0},{"IsEmpty":false,"x":7.0,"y":307.0},{"IsEmpty":false,"x":8.0,"y":313.0},{"IsEmpty":false,"x":7.0,"y":314.0},{"IsEmpty":false,"x":8.0,"y":317.0},{"IsEmpty":false,"x":7.0,"y":316.0},{"IsEmpty":false,"x":10.0,"y":321.0},{"IsEmpty":false,"x":9.0,"y":320.0},{"IsEmpty":false,"x":10.0,"y":322.0},{"IsEmpty":false,"x":10.0,"y":323.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":10.0,"y":323.0},{"IsEmpty":false,"x":11.0,"y":326.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":11.0,"y":326.0},{"IsEmpty":false,"x":10.0,"y":325.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":10.0,"y":325.0},{"IsEmpty":false,"x":11.0,"y":510.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":4}],"height":42.0,"width":1.75,"geometry":""});
	newelOptions = newelOptions.concat({"points":[{"IsEmpty":false,"x":7.0,"y":0.0},{"IsEmpty":false,"x":1.0,"y":0.0},{"IsEmpty":false,"x":10.0,"y":1.0},{"IsEmpty":false,"x":13.0,"y":3.0},{"IsEmpty":false,"x":16.0,"y":4.0},{"IsEmpty":false,"x":17.0,"y":6.0},{"IsEmpty":false,"x":18.0,"y":7.0},{"IsEmpty":false,"x":20.0,"y":9.0},{"IsEmpty":false,"x":21.0,"y":10.0},{"IsEmpty":false,"x":22.0,"y":12.0},{"IsEmpty":false,"x":23.0,"y":13.0},{"IsEmpty":false,"x":24.0,"y":15.0},{"IsEmpty":false,"x":24.0,"y":16.0},{"IsEmpty":false,"x":25.0,"y":18.0},{"IsEmpty":false,"x":26.0,"y":19.0},{"IsEmpty":false,"x":27.0,"y":21.0},{"IsEmpty":false,"x":27.0,"y":22.0},{"IsEmpty":false,"x":28.0,"y":24.0},{"IsEmpty":false,"x":28.0,"y":25.0},{"IsEmpty":false,"x":28.0,"y":29.0},{"IsEmpty":false,"x":29.0,"y":30.0},{"IsEmpty":false,"x":29.0,"y":34.0},{"IsEmpty":false,"x":30.0,"y":35.0},{"IsEmpty":false,"x":30.0,"y":41.0},{"IsEmpty":false,"x":31.0,"y":42.0},{"IsEmpty":false,"x":31.0,"y":47.0},{"IsEmpty":false,"x":32.0,"y":48.0},{"IsEmpty":false,"x":33.0,"y":50.0},{"IsEmpty":false,"x":34.0,"y":51.0},{"IsEmpty":false,"x":35.0,"y":53.0},{"IsEmpty":false,"x":36.0,"y":54.0},{"IsEmpty":false,"x":38.0,"y":57.0},{"IsEmpty":false,"x":38.0,"y":58.0},{"IsEmpty":false,"x":38.0,"y":68.0},{"IsEmpty":false,"x":37.0,"y":69.0},{"IsEmpty":false,"x":35.0,"y":71.0},{"IsEmpty":false,"x":35.0,"y":72.0},{"IsEmpty":false,"x":31.0,"y":76.0},{"IsEmpty":false,"x":31.0,"y":77.0},{"IsEmpty":false,"x":27.0,"y":79.0},{"IsEmpty":false,"x":25.0,"y":80.0},{"IsEmpty":false,"x":22.0,"y":82.0},{"IsEmpty":false,"x":22.0,"y":83.0},{"IsEmpty":false,"x":22.0,"y":85.0},{"IsEmpty":false,"x":21.0,"y":86.0},{"IsEmpty":false,"x":18.0,"y":89.0},{"IsEmpty":false,"x":18.0,"y":90.0},{"IsEmpty":false,"x":18.0,"y":92.0},{"IsEmpty":false,"x":32.0,"y":101.0},{"IsEmpty":false,"x":33.0,"y":106.0},{"IsEmpty":false,"x":34.0,"y":107.0},{"IsEmpty":false,"x":36.0,"y":109.0},{"IsEmpty":false,"x":36.0,"y":110.0},{"IsEmpty":false,"x":38.0,"y":244.0},{"IsEmpty":false,"x":36.0,"y":245.0},{"IsEmpty":false,"x":33.0,"y":248.0},{"IsEmpty":false,"x":33.0,"y":249.0},{"IsEmpty":false,"x":33.0,"y":252.0},{"IsEmpty":false,"x":32.0,"y":253.0},{"IsEmpty":false,"x":30.0,"y":255.0},{"IsEmpty":false,"x":29.0,"y":256.0},{"IsEmpty":false,"x":28.0,"y":259.0},{"IsEmpty":false,"x":27.0,"y":260.0},{"IsEmpty":false,"x":27.0,"y":263.0},{"IsEmpty":false,"x":32.0,"y":272.0},{"IsEmpty":false,"x":32.0,"y":274.0},{"IsEmpty":false,"x":31.0,"y":275.0},{"IsEmpty":false,"x":31.0,"y":279.0},{"IsEmpty":false,"x":30.0,"y":280.0},{"IsEmpty":false,"x":30.0,"y":285.0},{"IsEmpty":false,"x":29.0,"y":286.0},{"IsEmpty":false,"x":29.0,"y":290.0},{"IsEmpty":false,"x":28.0,"y":291.0},{"IsEmpty":false,"x":28.0,"y":301.0},{"IsEmpty":false,"x":27.0,"y":302.0},{"IsEmpty":false,"x":27.0,"y":309.0},{"IsEmpty":false,"x":26.0,"y":310.0},{"IsEmpty":false,"x":26.0,"y":322.0},{"IsEmpty":false,"x":25.0,"y":323.0},{"IsEmpty":false,"x":25.0,"y":342.0},{"IsEmpty":false,"x":26.0,"y":345.0},{"IsEmpty":false,"x":26.0,"y":357.0},{"IsEmpty":false,"x":27.0,"y":360.0},{"IsEmpty":false,"x":27.0,"y":366.0},{"IsEmpty":false,"x":28.0,"y":369.0},{"IsEmpty":false,"x":28.0,"y":377.0},{"IsEmpty":false,"x":29.0,"y":380.0},{"IsEmpty":false,"x":29.0,"y":385.0},{"IsEmpty":false,"x":30.0,"y":388.0},{"IsEmpty":false,"x":30.0,"y":392.0},{"IsEmpty":false,"x":31.0,"y":395.0},{"IsEmpty":false,"x":31.0,"y":399.0},{"IsEmpty":false,"x":32.0,"y":402.0},{"IsEmpty":false,"x":32.0,"y":407.0},{"IsEmpty":false,"x":33.0,"y":410.0},{"IsEmpty":false,"x":33.0,"y":418.0},{"IsEmpty":false,"x":34.0,"y":421.0},{"IsEmpty":false,"x":34.0,"y":427.0},{"IsEmpty":false,"x":35.0,"y":430.0},{"IsEmpty":false,"x":35.0,"y":436.0},{"IsEmpty":false,"x":36.0,"y":439.0},{"IsEmpty":false,"x":36.0,"y":448.0},{"IsEmpty":false,"x":38.0,"y":454.0},{"IsEmpty":false,"x":38.0,"y":483.0},{"IsEmpty":false,"x":36.0,"y":484.0},{"IsEmpty":false,"x":36.0,"y":499.0},{"IsEmpty":false,"x":35.0,"y":500.0},{"IsEmpty":false,"x":35.0,"y":508.0},{"IsEmpty":false,"x":34.0,"y":509.0},{"IsEmpty":false,"x":34.0,"y":517.0},{"IsEmpty":false,"x":33.0,"y":518.0},{"IsEmpty":false,"x":33.0,"y":522.0},{"IsEmpty":false,"x":32.0,"y":523.0},{"IsEmpty":false,"x":29.0,"y":526.0},{"IsEmpty":false,"x":38.0,"y":557.0},{"IsEmpty":false,"x":38.0,"y":562.0},{"IsEmpty":false,"x":36.0,"y":563.0},{"IsEmpty":false,"x":28.0,"y":570.0},{"IsEmpty":false,"x":28.0,"y":571.0},{"IsEmpty":false,"x":38.0,"y":616.0},{"IsEmpty":false,"x":38.0,"y":617.0},{"IsEmpty":false,"x":38.0,"y":628.0},{"IsEmpty":false,"x":36.0,"y":629.0},{"IsEmpty":false,"x":36.0,"y":633.0},{"IsEmpty":false,"x":35.0,"y":634.0},{"IsEmpty":false,"x":35.0,"y":637.0},{"IsEmpty":false,"x":34.0,"y":638.0},{"IsEmpty":false,"x":34.0,"y":641.0},{"IsEmpty":false,"x":33.0,"y":642.0},{"IsEmpty":false,"x":33.0,"y":645.0},{"IsEmpty":false,"x":32.0,"y":646.0},{"IsEmpty":false,"x":31.0,"y":648.0},{"IsEmpty":false,"x":31.0,"y":649.0},{"IsEmpty":false,"x":33.0,"y":669.0},{"IsEmpty":false,"x":38.0,"y":694.0},{"IsEmpty":false,"x":38.0,"y":1089.0}],"sections":[{"Points":[{"IsEmpty":false,"x":7.0,"y":0.0},{"IsEmpty":false,"x":7.0,"y":0.0},{"IsEmpty":false,"x":1.0,"y":0.0},{"IsEmpty":false,"x":10.0,"y":1.0},{"IsEmpty":false,"x":13.0,"y":3.0},{"IsEmpty":false,"x":16.0,"y":4.0},{"IsEmpty":false,"x":17.0,"y":6.0},{"IsEmpty":false,"x":18.0,"y":7.0},{"IsEmpty":false,"x":20.0,"y":9.0},{"IsEmpty":false,"x":21.0,"y":10.0},{"IsEmpty":false,"x":22.0,"y":12.0},{"IsEmpty":false,"x":23.0,"y":13.0},{"IsEmpty":false,"x":24.0,"y":15.0},{"IsEmpty":false,"x":24.0,"y":16.0},{"IsEmpty":false,"x":25.0,"y":18.0},{"IsEmpty":false,"x":26.0,"y":19.0},{"IsEmpty":false,"x":27.0,"y":21.0},{"IsEmpty":false,"x":27.0,"y":22.0},{"IsEmpty":false,"x":28.0,"y":24.0},{"IsEmpty":false,"x":28.0,"y":25.0},{"IsEmpty":false,"x":28.0,"y":29.0},{"IsEmpty":false,"x":29.0,"y":30.0},{"IsEmpty":false,"x":29.0,"y":34.0},{"IsEmpty":false,"x":30.0,"y":35.0},{"IsEmpty":false,"x":30.0,"y":41.0},{"IsEmpty":false,"x":31.0,"y":42.0},{"IsEmpty":false,"x":31.0,"y":47.0},{"IsEmpty":false,"x":32.0,"y":48.0},{"IsEmpty":false,"x":33.0,"y":50.0},{"IsEmpty":false,"x":34.0,"y":51.0},{"IsEmpty":false,"x":35.0,"y":53.0},{"IsEmpty":false,"x":36.0,"y":54.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":36.0,"y":54.0},{"IsEmpty":false,"x":38.0,"y":57.0},{"IsEmpty":false,"x":38.0,"y":58.0},{"IsEmpty":false,"x":38.0,"y":68.0},{"IsEmpty":false,"x":37.0,"y":69.0},{"IsEmpty":false,"x":35.0,"y":71.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":35.0,"y":71.0},{"IsEmpty":false,"x":35.0,"y":72.0},{"IsEmpty":false,"x":31.0,"y":76.0},{"IsEmpty":false,"x":31.0,"y":77.0},{"IsEmpty":false,"x":27.0,"y":79.0},{"IsEmpty":false,"x":25.0,"y":80.0},{"IsEmpty":false,"x":22.0,"y":82.0},{"IsEmpty":false,"x":22.0,"y":83.0},{"IsEmpty":false,"x":22.0,"y":85.0},{"IsEmpty":false,"x":21.0,"y":86.0},{"IsEmpty":false,"x":18.0,"y":89.0},{"IsEmpty":false,"x":18.0,"y":90.0},{"IsEmpty":false,"x":18.0,"y":92.0},{"IsEmpty":false,"x":32.0,"y":101.0},{"IsEmpty":false,"x":33.0,"y":106.0},{"IsEmpty":false,"x":34.0,"y":107.0},{"IsEmpty":false,"x":36.0,"y":109.0},{"IsEmpty":false,"x":36.0,"y":110.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":36.0,"y":110.0},{"IsEmpty":false,"x":38.0,"y":244.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":4},{"Points":[{"IsEmpty":false,"x":38.0,"y":244.0},{"IsEmpty":false,"x":36.0,"y":245.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":36.0,"y":245.0},{"IsEmpty":false,"x":33.0,"y":248.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":33.0,"y":248.0},{"IsEmpty":false,"x":33.0,"y":249.0},{"IsEmpty":false,"x":33.0,"y":252.0},{"IsEmpty":false,"x":32.0,"y":253.0},{"IsEmpty":false,"x":30.0,"y":255.0},{"IsEmpty":false,"x":29.0,"y":256.0},{"IsEmpty":false,"x":28.0,"y":259.0},{"IsEmpty":false,"x":27.0,"y":260.0},{"IsEmpty":false,"x":27.0,"y":263.0},{"IsEmpty":false,"x":32.0,"y":272.0},{"IsEmpty":false,"x":32.0,"y":274.0},{"IsEmpty":false,"x":31.0,"y":275.0},{"IsEmpty":false,"x":31.0,"y":279.0},{"IsEmpty":false,"x":30.0,"y":280.0},{"IsEmpty":false,"x":30.0,"y":285.0},{"IsEmpty":false,"x":29.0,"y":286.0},{"IsEmpty":false,"x":29.0,"y":290.0},{"IsEmpty":false,"x":28.0,"y":291.0},{"IsEmpty":false,"x":28.0,"y":301.0},{"IsEmpty":false,"x":27.0,"y":302.0},{"IsEmpty":false,"x":27.0,"y":309.0},{"IsEmpty":false,"x":26.0,"y":310.0},{"IsEmpty":false,"x":26.0,"y":322.0},{"IsEmpty":false,"x":25.0,"y":323.0},{"IsEmpty":false,"x":25.0,"y":342.0},{"IsEmpty":false,"x":26.0,"y":345.0},{"IsEmpty":false,"x":26.0,"y":357.0},{"IsEmpty":false,"x":27.0,"y":360.0},{"IsEmpty":false,"x":27.0,"y":366.0},{"IsEmpty":false,"x":28.0,"y":369.0},{"IsEmpty":false,"x":28.0,"y":377.0},{"IsEmpty":false,"x":29.0,"y":380.0},{"IsEmpty":false,"x":29.0,"y":385.0},{"IsEmpty":false,"x":30.0,"y":388.0},{"IsEmpty":false,"x":30.0,"y":392.0},{"IsEmpty":false,"x":31.0,"y":395.0},{"IsEmpty":false,"x":31.0,"y":399.0},{"IsEmpty":false,"x":32.0,"y":402.0},{"IsEmpty":false,"x":32.0,"y":407.0},{"IsEmpty":false,"x":33.0,"y":410.0},{"IsEmpty":false,"x":33.0,"y":418.0},{"IsEmpty":false,"x":34.0,"y":421.0},{"IsEmpty":false,"x":34.0,"y":427.0},{"IsEmpty":false,"x":35.0,"y":430.0},{"IsEmpty":false,"x":35.0,"y":436.0},{"IsEmpty":false,"x":36.0,"y":439.0},{"IsEmpty":false,"x":36.0,"y":448.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":36.0,"y":448.0},{"IsEmpty":false,"x":38.0,"y":454.0},{"IsEmpty":false,"x":38.0,"y":483.0},{"IsEmpty":false,"x":36.0,"y":484.0},{"IsEmpty":false,"x":36.0,"y":499.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":36.0,"y":499.0},{"IsEmpty":false,"x":35.0,"y":500.0},{"IsEmpty":false,"x":35.0,"y":508.0},{"IsEmpty":false,"x":34.0,"y":509.0},{"IsEmpty":false,"x":34.0,"y":517.0},{"IsEmpty":false,"x":33.0,"y":518.0},{"IsEmpty":false,"x":33.0,"y":522.0},{"IsEmpty":false,"x":32.0,"y":523.0},{"IsEmpty":false,"x":29.0,"y":526.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":29.0,"y":526.0},{"IsEmpty":false,"x":38.0,"y":557.0},{"IsEmpty":false,"x":38.0,"y":562.0},{"IsEmpty":false,"x":36.0,"y":563.0},{"IsEmpty":false,"x":28.0,"y":570.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":28.0,"y":570.0},{"IsEmpty":false,"x":28.0,"y":571.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":28.0,"y":571.0},{"IsEmpty":false,"x":38.0,"y":616.0},{"IsEmpty":false,"x":38.0,"y":617.0},{"IsEmpty":false,"x":38.0,"y":628.0},{"IsEmpty":false,"x":36.0,"y":629.0},{"IsEmpty":false,"x":36.0,"y":633.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":36.0,"y":633.0},{"IsEmpty":false,"x":35.0,"y":634.0},{"IsEmpty":false,"x":35.0,"y":637.0},{"IsEmpty":false,"x":34.0,"y":638.0},{"IsEmpty":false,"x":34.0,"y":641.0},{"IsEmpty":false,"x":33.0,"y":642.0},{"IsEmpty":false,"x":33.0,"y":645.0},{"IsEmpty":false,"x":32.0,"y":646.0},{"IsEmpty":false,"x":31.0,"y":648.0},{"IsEmpty":false,"x":31.0,"y":649.0},{"IsEmpty":false,"x":33.0,"y":669.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":12},{"Points":[{"IsEmpty":false,"x":33.0,"y":669.0},{"IsEmpty":false,"x":38.0,"y":694.0},{"IsEmpty":false,"x":38.0,"y":1089.0}],"Points3D":{"IsEmpty":true,"x":0.0,"y":0.0},"Sides":4}],"height":48.0,"width":3.25,"type":"standard"});

    z = new Zone('stair', 0, 64, 48, 96, 48, 0, 34);
	//var o = new Zone('stair', 96, 64, 48, 96, 48, 180, 34);
		
		
	loadModels();	
    // End Global Setup
});

function screenshot() {
	"use strict";
    return renderer.domElement.toDataURL("image/jpeg");
}

function render() {
	"use strict";
	renderer.render( scene, camera );
}

// #####################################################################
// FUNCTION: buildShape 
// #####################################################################
// VARIABLES: shape, rise, run, width, x, y, base_height, railHeight
//            points (rail profile), useBothRails, useRightRail
// RETURN THREE.Mesh
// #####################################################################
// This function builds a mesh for a given predefined shape. Currently
// supports the drawing of stairs procedurally. Most of the heavy
// lifting is done here.
// #####################################################################
function buildShape(shape, rise, run, width, x, y, base_height, railHeight, points, useBothRails, useRightRail, name) {
	"use strict";
	//var material;
	var extrusionSettings;
	var geom;
	var mesh;
	var material;
	var Object = new THREE.Object3D();
	Object.position.x = x;
	Object.position.z = y;
	
	if ( shape === 'stair' ) {
		/*
			//  ####################################################################################
			//  ####################     IBC 2012 Stair Requirements           #####################
			//  ####################################################################################
			//	Stair riser heights shall be 7 inches (178 mm) maximum and 4 inches (102 mm) minimum.
			//	The riser height shall be measured vertically between the nosings of adjacent treads.
			//
			//	Rectangular tread depths shall be 11 inches (279 mm) minimum measured horizontally 
			//	between the vertical planes of the foremost projection of adjacent treads and at a 
			//	right angle to the tread nosing. 
			//
			//  Winder treads shall have a minimum tread depth of 
			//	11 inches (279 mm) between the vertical planes of the foremost projection of adjacent 
			//	treads at the intersections with the walkline and a minimum tread depth of 10 inches 
			//	(254 mm) within the clear width of the stair.
			//
			//  Exceptions: 
			//	
			//	In Group R-3 occupancies; within dwelling units in Group R-2 occupancies; and in 
			//  Group U occupancies that are accessory to a Group R-3 occupancy or accessory to individual
			//	dwelling units in Group R-2 occupancies; the maximum riser height shall be 7-3/4 inches 
			//	(197 mm); the minimum tread depth shall be 10 inches (254 mm); the minimum winder tread 
			//	depth at the walkline shall be 10 inches (254 mm); and the minimum winder tread depth shall
			//	be 6 inches (152 mm). A nosing projection not less than 3/4 inch (19.1 mm) but not more than
			//	11/4 inches (32 mm) shall be provided on stairways with solid risers where the tread depth
			//	is less than 11 inches (279 mm).
			//  #####################################################################################
		 */

		
		 
		 
		var riseLimit, runLimit, codeCompliant = true;
		var requireRails = false;
		if (screenToInches(width) >= 44) {
			requireRails = true;
		}
				
		// If the project is in conformance with IBC2012
		if (IBC2012) {
			riseLimit = 7;
			runLimit = 11;
		} else {
			riseLimit = 7.75;
			runLimit = 10;
		}
			
		// Set the number of stairs to whichever is the higher number required.
		if ( (run / runLimit) < (rise / riseLimit) ) {
			numberOfStairs = Math.ceil((screenToInches(rise) / riseLimit) - 1);
			if (screenToInches(run / (numberOfStairs + 1)) < runLimit) {codeCompliant = false;}
		} else {
			numberOfStairs = Math.ceil((screenToInches(run) / runLimit) - 1);
			if (screenToInches(rise / (numberOfStairs + 1)) > riseLimit) {codeCompliant = false;}
		}
		
		//var geom = new THREE.CubeGeometry();
		
		
		
		
		
		
		
		// Check to see if the stair meets code compliance requirements
		if ( !codeCompliant ) {
			material = new THREE.MeshLambertMaterial( { color: 0xff0000, side: THREE.DoubleSide} );
		} else {
			//material = new THREE.MeshLambertMaterial( { color: 0xA8784D, side: THREE.DoubleSide
			//, map: THREE.ImageUtils.loadTexture('image/beech.jpg') } );
			//material = new THREE.MeshPhongMaterial( { ambient: 0x030303, color: 0xA8784D, specular: 0x664930, shininess: 30, shading: THREE.FlatShading } );
			material = base_material;
		}
		
		var i, j;
		
		// Draw "Stair as an extruded geometry. For this to work right, it has to be done counter-clockwise, otherwise you end up with holes.
		var stairPoints = [];
		stairPoints.push( new THREE.Vector2 ( -(run / 2), rise / numberOfStairs ) );
		stairPoints.push( new THREE.Vector2 ( -(run / 2), 0 ) );
		stairPoints.push( new THREE.Vector2 ( -(run / 2) + (run / numberOfStairs), 0 ) );
		stairPoints.push( new THREE.Vector2 ( run / 2 - (run / numberOfStairs), rise - (rise / numberOfStairs) * 2) );	
		stairPoints.push( new THREE.Vector2 ( run / 2, rise - (rise / numberOfStairs)) );
		for (i = numberOfStairs; i > 0; i -= 1) {
			stairPoints.push( new THREE.Vector2 (  (i) * (run / numberOfStairs) -(run / 2),  (rise / numberOfStairs) * i ) );
			stairPoints.push( new THREE.Vector2 (  (i - 1) * (run / numberOfStairs) -(run / 2),  (rise / numberOfStairs) * i ) );
		}		
		var stairShape = new THREE.Shape( stairPoints );
		
		extrusionSettings = {
			amount: width, curveSegments: 0,
			bevelThickness: 1, bevelSize: 2, bevelEnabled: false,
			material: 0, extrudeMaterial: 1
		};	
		geom = new THREE.ExtrudeGeometry( stairShape, extrusionSettings );
			
		geom.computeFaceNormals();

		THREE.GeometryUtils.center( geom );
		
		mesh = new THREE.Mesh( geom, material );
		//mesh.position.x = x;
		//mesh.position.z = y;
		mesh.position.y = (rise / 2) + base_height;
		
		//axis = new THREE.Vector3(x , (rise / 2), y);
		
		mesh.castShadow = true;
		mesh.receiveShadow = true;
		mesh.name = "stair";
		
		Object.add(mesh);
		
		// Prepare dimensions, rotation for railing.
		var hypotenuse = Math.sqrt( Math.pow(run, 2) + Math.pow(rise, 2) );
		var angle = Math.asin(rise / hypotenuse);
		
		if ( points.length === 0 ) {
			points = []; // default rail shape if none provided
			points.push( new THREE.Vector2 ( inchesToScreen(2.75), inchesToScreen(1.625) ));
			points.push( new THREE.Vector2 ( 0, inchesToScreen(1.625) ));
			points.push( new THREE.Vector2 ( 0, 0));
			points.push( new THREE.Vector2 ( inchesToScreen(2.75), 0 ));
		} /*else {
			points.sort( pointSort );
		} */
		var railWidth = getWidth(points);
		var railThickness = getHeight(points);
		
		var railShape = new THREE.Shape( points );
		var extrusionSettings2 = {
			amount: hypotenuse + 1, curveSegments: 0,
			bevelEnabled: false
		};	
		var rail = new THREE.ExtrudeGeometry( railShape, extrusionSettings2 );
		
		rail.computeFaceNormals();
		
		THREE.GeometryUtils.center( rail );
		
		
		var railObj = new THREE.Object3D();
		
		for ( j = 0; j < balusterOptions.length; j += 1) {
                var scale = inchesToScreen(balusterOptions[j].width) / (getWidth(balusterOptions[j].points) * 2);
				var hscale = inchesToScreen(balusterOptions[j].height) / getHeight(balusterOptions[j].points);
                balusterOptions[j].geometry = drawNewelGeometry( balusterOptions[j] );
                var height = (getHeight(balusterOptions[j].points) * hscale);
	    }
		
		if ( useBothRails || !useRightRail || requireRails) {
			var railMesh = new THREE.Mesh( rail, material );
			railMesh.rotation.x = angle;
			railMesh.position.x = -((width / 2) - railWidth);
			railMesh.position.y = (rise / 2) + railHeight + (rise / numberOfStairs) - (railThickness - 2);
            railMesh.position.z = 0;
			railMesh.castShadow = true;
			railMesh.receiveShadow = true;
			railObj.add(railMesh);

            for ( j = 0; j < balusterOptions.length; j += 1) {
                var scale = inchesToScreen(balusterOptions[j].width) / (getWidth(balusterOptions[j].points) * 2);
				var hscale = inchesToScreen(balusterOptions[j].height) / getHeight(balusterOptions[j].points);

               // var newelGeometry = drawNewelGeometry( balusterOptions[j] );
                var height = (getHeight(balusterOptions[j].points) * hscale) / 2;
				
				
                for (i = 0; i < numberOfStairs; i += 1) {
                    var Newel = new THREE.Mesh( balusterOptions[j].geometry, material );
                    Newel.rotation.x = degreesToRadians(-90);
                    Newel.position.x = (-run / 2) + ((getWidth( balusterOptions[j].points )) * scale) + i * (run / numberOfStairs);
                    
					if (j !== 0) {
                        Newel.position.x += (run / numberOfStairs) / (balusterOptions.length / j);
					} 
						
					var railChange = railHeightAtPoint( Newel.position.x, railHeight, run, rise, numberOfStairs );
					
					
                    Newel.position.z = -((width / 2) - railWidth);//-width / 2 + (getWidth( balusterOptions ))/10;
					
                    Newel.position.y = ((railHeight) - (height + railThickness / 2))  + railChange + (rise / numberOfStairs);
					
                    Newel.castShadow = true;
                    Newel.receiveShadow = true;
                    Object.add(Newel);
                }

            }
			
			for ( j = 0; j < newelOptions.length; j += 1) {
				var scale = inchesToScreen(newelOptions[j].width) / (getWidth(newelOptions[j].points) * 2);
				var hscale = inchesToScreen(newelOptions[j].height) / getHeight(newelOptions[j].points);
			
                var newelGeometry = drawNewelGeometry( newelOptions[j] );
                var height = (getHeight(newelOptions[j].points) * hscale) / 2;
				
                var Newel = new THREE.Mesh( newelGeometry, material );
                    Newel.rotation.x = degreesToRadians(-90);
                    Newel.position.x = (-run / 2) - ((getWidth( newelOptions[j].points )) * scale);
                    Newel.position.z = -((width / 2) - railWidth);//-width / 2 + (getWidth( newelOptions ))/10;
                    Newel.position.y = height;
                    Newel.castShadow = true;
                    Newel.receiveShadow = true;
                    Object.add(Newel);

            }
			
			for ( j = 0; j < newelOptions.length; j += 1) {
				var scale = inchesToScreen(newelOptions[j].width) / (getWidth(newelOptions[j].points) * 2);
				var hscale = inchesToScreen(newelOptions[j].height) / getHeight(newelOptions[j].points);
			
                var newelGeometry = drawNewelGeometry( newelOptions[j] );
                var height = (getHeight(newelOptions[j].points) * hscale) / 2;
				
                var Newel = new THREE.Mesh( newelGeometry, material );
                    Newel.rotation.x = degreesToRadians(-90);
                    Newel.position.x = (run / 2) + ((getWidth( newelOptions[j].points )) * scale);
                    Newel.position.z = -((width / 2) - railWidth);//-width / 2 + (getWidth( newelOptions ))/10;
                    Newel.position.y = height + rise;
                    Newel.castShadow = true;
                    Newel.receiveShadow = true;
                    Object.add(Newel);

            }
			
		}

		if ( useBothRails || useRightRail || requireRails ) {
			var railMesh2 = new THREE.Mesh( rail, material );
			railMesh2.rotation.x = angle;
			railMesh2.position.x = ((width / 2) - railWidth);
			railMesh2.position.y = (rise / 2) + railHeight + (rise / numberOfStairs) - (railThickness / 2);
            railMesh2.position.z = 0;
			railMesh2.castShadow = true;
			railMesh2.receiveShadow = true;
			railObj.add(railMesh2);
			
			for ( j = 0; j < balusterOptions.length; j += 1) {
				var scale = inchesToScreen(balusterOptions[j].width) / (getWidth(balusterOptions[j].points) * 2);
				var hscale = inchesToScreen(balusterOptions[j].height) / getHeight(balusterOptions[j].points);
			
                var height = (getHeight(balusterOptions[j].points) * hscale) / 2;
				
                for (i = 0; i < numberOfStairs; i += 1) {
                    var Newel = new THREE.Mesh( balusterOptions[j].geometry, material );
                    Newel.rotation.x = degreesToRadians(-90);
                    Newel.position.x = (-run / 2) + (getWidth( balusterOptions[j].points )) * scale + i * (run / numberOfStairs);
                    if (j !== 0) {
                        Newel.position.x += (run / numberOfStairs) / (balusterOptions.length / j)
                    }
					var railChange = railHeightAtPoint( Newel.position.x, railHeight, run, rise, numberOfStairs );
                    Newel.position.z = ((width / 2) - railWidth);//-width / 2 + (getWidth( balusterOptions ))/10;
					
                    Newel.position.y = (railHeight - (height + railThickness / 2))  + railChange + (rise / numberOfStairs);
					
					// Get into base position -> height + (rise / numberOfStairs);
					
					
                    Newel.castShadow = true;
                    Newel.receiveShadow = true;
                    Object.add(Newel);
                }

            }
			
			for ( j = 0; j < newelOptions.length; j += 1) {
				var scale = inchesToScreen(newelOptions[j].width) / (getWidth(newelOptions[j].points) * 2);
				var hscale = inchesToScreen(newelOptions[j].height) / getHeight(newelOptions[j].points);
			
                var newelGeometry = drawNewelGeometry( newelOptions[j] );
                var height = (getHeight(newelOptions[j].points) * hscale) / 2;
				
                var Newel = new THREE.Mesh( newelGeometry, material );
                    Newel.rotation.x = degreesToRadians(-90);
                    Newel.position.x = (-run / 2) - ((getWidth( newelOptions[j].points )) * scale);
                    Newel.position.z = ((width / 2) - railWidth);//-width / 2 + (getWidth( newelOptions ))/10;
                    Newel.position.y = height;
                    Newel.castShadow = true;
                    Newel.receiveShadow = true;
                    Object.add(Newel);

            }
			
			for ( j = 0; j < newelOptions.length; j += 1) {
				var scale = inchesToScreen(newelOptions[j].width) / (getWidth(newelOptions[j].points) * 2);
				var hscale = inchesToScreen(newelOptions[j].height) / getHeight(newelOptions[j].points);
			
                var newelGeometry = drawNewelGeometry( newelOptions[j] );
                var height = (getHeight(newelOptions[j].points) * hscale) / 2;
				
                var Newel = new THREE.Mesh( newelGeometry, material );
                    Newel.rotation.x = degreesToRadians(-90);
                    Newel.position.x = (run / 2) + ((getWidth( newelOptions[j].points )) * scale);
                    Newel.position.z = ((width / 2) - railWidth);//-width / 2 + (getWidth( newelOptions ))/10;
                    Newel.position.y = height + rise;
                    Newel.castShadow = true;
                    Newel.receiveShadow = true;
                    Object.add(Newel);

            }

           }
				
		railObj.rotation.y = degreesToRadians(90);
		Object.add(railObj);
		



		Object.castShadow = true;
		Object.receiveShadow = true;
		Object.name = name;
		
		return (Object);
	}
	
	return null;
}
// ################################################################
// END FUNCTION: buildShape
// ################################################################

function railHeightAtPoint( x, railHeight, run, rise, numberOfStairs ) {
	var angle = Math.asin( (rise / Math.sqrt( Math.pow(run, 2) + Math.pow(rise, 2) ))  );
	//x = x + (run / 2);
	//x = x / (run / (numberOfStairs)); // Get height relative to the step...
	
	var hypotenuse = x / Math.cos(angle);
	var y = (Math.sin(angle) * hypotenuse) + (rise / 2);
	//console.log( 'Height: ' + y + ' Rise: ' + rise + ' Run: ' + run );
	return ( y );
	
	// x = cos(angle) * hypotenuse
	// hypotenuse = x / cos(angle)
	// y = sin(angle) * hypotenuse)
}

function max( values ) {
	var m = -999999;
	for ( var i = 0; i < values.length; i += 1) {
		if ( m < values[i] ) { m = values[i] };
	}
	return m;
}

function min( values ) {
	var m = 999999;
	for ( var i = 0; i < values.length; i += 1) {
		if ( m > values[i] ) { m = values[i] };
	}
	return m;
}

function drawNewelGeometry( options ) {
	"use strict";
	// Lathing posts - check to see if the point.x = the maximum x of the points array... 
	// if so, this is part of a 4-sided section, otherwise, it it round.Looping through 
	// the points vertically from bottom to top, at every change from full width to less, 
	// or vice versa, initiate a new section, and close out the last section, including 
	// the current point. Then, lathe each section as a geometry object, then use the 
	// Merge function to make a single mesh:
	
	var i, j;
    var scale = inchesToScreen(options.width) / (getWidth(options.points) * 2);
	var hscale = inchesToScreen(options.height) / getHeight(options.points);

    var geo = (new THREE.CylinderGeometry( 0.1, 0.1, 0.1, 4 ));

    for ( i = 0; i < options.sections.length; i += 1) {

        options.sections[i].Points3D = [];
        for ( j = 0; j < options.sections[i].Points.length; j += 1) {
            var pt = new THREE.Vector3( options.sections[i].Points[j].x * scale , 0, (options.sections[i].Points[j].y) * hscale );
            options.sections[i].Points3D.push(pt);
        }

        if (options.sections[i].length !== 0) {
            var g;
                if ( options.sections[i].Sides === 4 || options.type === "square" ) {
                    g = new THREE.Mesh( buildPost( options.sections[i].Points3D ) );
                    g.position.z = (options.sections[i].Points3D[0].z);
                } else {
                    g = new THREE.LatheGeometry(options.sections[i].Points3D, options.sections[i].Sides);
                }

            THREE.GeometryUtils.merge( geo, g );
        }
    }
	
	THREE.GeometryUtils.center ( geo );

    return geo;
}

function drawVolute( options ) {
	
}

function buildPost( points ) {
    "use strict";

    var i = points.length - 1; // Get last item in the array, since we only need top and bottom points
    var pts = [];
    pts.push( new THREE.Vector2 (points[i].x, points[i].x));
    pts.push( new THREE.Vector2 (-points[i].x, points[i].x));
    pts.push( new THREE.Vector2 (-points[i].x, -points[i].x));
    pts.push( new THREE.Vector2 (points[i].x, -points[i].x));

    var postShape = new THREE.Shape( pts );
    var extrusionSettings2 = {
        amount: Math.abs(points[i].z - points[0].z), curveSegments: 0,
        bevelEnabled: false
    };
    var post = new THREE.ExtrudeGeometry( postShape, extrusionSettings2 );

    post.computeFaceNormals();

    return post;
}

function buildLanding( width, x, y, base_height, railHeight, points, railOptions ) {
	"use strict";
	
}

function buildWall( width, x, y, height, thickness ) {
	"use strict";
	
}

function getWidth(points) {
	"use strict";
	var MaxWidth = 0;
	var i;
	for ( i = 0; i < points.length; i += 1) {
		if (MaxWidth < points[i].x) {
			MaxWidth = points[i].x;
		}
	}
	return MaxWidth;
}

function getHeight(points) {
	"use strict";
    var MaxHeight = 0;
	var i;
	for ( i = 0; i < points.length; i += 1) {
		if (MaxHeight < points[i].y) {
			MaxHeight = points[i].y;
		}
	}
	return MaxHeight;
}

function getHeightThree(points) {
    "use strict";
    var MaxHeight = 0;
    var i;
    for ( i = 0; i < points.length; i += 1) {
        if (MaxHeight < points[i].z) {
            MaxHeight = points[i].z;
        }
    }
    return MaxHeight;
}


function base64Texture(dat) {
		"use strict";
		var img = new Image();
		var t = new THREE.Texture(img);
		t.wrapS = THREE.RepeatWrapping;

		img.onload = function() {
			t.needsUpdate = true;
			render();
		};
		img.src = dat;
		return t;
	}

// A representation of a 2D Point.
function SortablePoint( x, y ) {
	"use strict";
    this.x = x;
    this.y = y;

    this.distance = function(that) {
        var dX = that.x - this.x;
        var dY = that.y - this.y;
        return Math.sqrt((dX*dX) + (dY*dY));
    };

    this.slope = function(that) {
        var dX = that.x - this.x;
        var dY = that.y - this.y;
        return dY / dX;
    };

}

// A custom sort function that sorts p1 and p2 based on their slope
// that is formed from the upper most point from the array of points.
function pointSort(p1, p2) {
   "use strict";
   // Exclude the 'upper' point from the sort (which should come first).
    if(p1 === upper) {return -1;}
    if(p2 === upper) {return 1;}

    // Find the slopes of 'p1' and 'p2' when a line is 
    // drawn from those points through the 'upper' point.
    var m1 = upper.slope(p1);
    var m2 = upper.slope(p2);

    // 'p1' and 'p2' are on the same line towards 'upper'.
    if(m1 === m2) {
        // The point closest to 'upper' will come first.
        return p1.distance(upper) < p2.distance(upper) ? -1 : 1;
    }

    // If 'p1' is to the right of 'upper' and 'p2' is the the left.
    if(m1 <= 0 && m2 > 0) {return -1;}

    // If 'p1' is to the left of 'upper' and 'p2' is the the right.
    if(m1 > 0 && m2 <= 0) {return 1;}

    // It seems that both slopes are either positive, or negative.
    return m1 > m2 ? -1 : 1;
}

// Find the upper most point. In case of a tie, get the left most point.
function upperLeft(points) {
	"use strict";
    var top = points[0];
	var temp, i;
    for(i = 1; i < points.length; i += 1) {
        temp = points[i];
        if(temp.y > top.y || (temp.y === top.y && temp.x < top.x)) {
            top = temp;
        }
    }
    return top;
}


// Add object to scene - but also to mouse-clickable list...
function addObject( obj ) {
	scene.add( obj );
	objects = [];
	// All clickable items are stored initially as Object3Ds, so only items with children are valid...
	for ( var i = 0; i < scene.children.length; i += 1 ) {
		if ( scene.children[i].children.length > 0 ) {
			for ( var j = 0; j < scene.children[i].children.length; j += 1 ) {
				if ( scene.children[i].children[j].children.length > 0 ) {
					for ( var c = 0; c < scene.children[i].children[j].children.length; c += 1 ) {
						objects.push( scene.children[i].children[j].children[c] );
					}
				} else {
					objects.push( scene.children[i].children[j] );
				}
			}
			
		}
	}
} // End Function addObject


function createTransformHandle(){
				Tfcontrol = new THREE.TransformControls( camera, renderer.domElement );
				Tfcontrol.addEventListener( 'change', render );
				scene.add( Tfcontrol );
				window.addEventListener( 'keydown', function ( event ) {
		        switch ( event.keyCode ) {
		              case 81: // Q
		                Tfcontrol.setSpace( Tfcontrol.space == "local" ? "world" : "local" );
		                break;
		              case 87: // W
		                Tfcontrol.setMode( "translate" );
		                break;
		              case 69: // E
		                Tfcontrol.setMode( "rotate" );
		                break;
		              case 82: // R
		                Tfcontrol.setMode( "scale" );
		                break;
					case 187:
					case 107: // +,=,num+
						Tfcontrol.setSize( Tfcontrol.size + 0.1 );
						break;
					case 189:
					case 10: // -,_,num-
						Tfcontrol.setSize( Math.max(Tfcontrol.size - 0.1, 0.1 ) );
						break;
		            }            
        		});
			}


// #####################################
// Mouse interactivity
// #####################################
function onDocumentMouseMove( event ) {
	
	
	
	event.preventDefault();
	

	
	var x = event.x;
    var y = event.y;

    var canvas = document.getElementById("container");

    x -= canvas.offsetLeft;
    y -= canvas.offsetTop;

	mouse.x = ( x / WIDTH ) * 2 - 1;
	mouse.y = - ( y / HEIGHT ) * 2 + 1;
	
	var vector = new THREE.Vector3( mouse.x, mouse.y, 0.5 );
	projector.unprojectVector( vector, camera );

	var raycaster = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );

	var intersects = raycaster.intersectObjects( objects );
		
		if ( intersects.length > 0 ) {
			if ( INTERSECTED != intersects[ 0 ].object ) {
				if ( INTERSECTED ) INTERSECTED.material.color.setHex( INTERSECTED.currentHex );
				INTERSECTED = intersects[ 0 ].object;
				INTERSECTED.currentHex = INTERSECTED.material.color.getHex();
			}

			container.style.cursor = 'pointer';

		} else {

			if ( INTERSECTED ) INTERSECTED.material.color.setHex( INTERSECTED.currentHex );
			INTERSECTED = null;
			container.style.cursor = 'auto';
		}

	

}

function onDocumentMouseDown( event ) {

	event.preventDefault();
	
	var eventType;
	
	if ( event.button === 0 ) {
        eventType = 'select';
    } else if ( event.button === 1 ) {
    
    } else if ( event.button === 2 ) {
    
	}

	if ( eventType === 'select' ) {
		var vector = new THREE.Vector3( mouse.x, mouse.y, 0.5 );
		projector.unprojectVector( vector, camera );

		var raycaster = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );

		var intersects = raycaster.intersectObjects( objects );
		
		
		if ( intersects.length > 0 ) {
								
			controls.enabled = false;
			controls.noZoom = true;
			controls.noPan = true;
			controls.noRotate = true;
			
			SELECTED = intersects[ 0 ].object;				
			Tfcontrol.attach( SELECTED.parent );		
			render();
			return;
			
		} else {
			if ( !Tfcontrol.transforming ) {

				Tfcontrol.detach( SELECTED );
				SELECTED = null;
					
				controls.enabled = true;
											
				render();
				return;
			} else {
				render();
				return;
			}
		}
	}

}

function onDocumentMouseUp( event ) {

	event.preventDefault();

	//controls.enabled = true;
	
	if ( controls.enabled ) {
		controls.noZoom = false;
		controls.noPan = false;
		controls.noRotate = false;
	}

	if ( INTERSECTED ) {

		

		//SELECTED = null;

	}

	container.style.cursor = 'auto';

}
// ##################################
// END MOUSE INTERACTIVITY
// ##################################


function degreesToRadians(degrees) {
	"use strict";
	return -degrees * Math.PI / 180;
}

function radiansToDegrees(radians) {
	"use strict";
	return ( -radians * 180 ) / Math.PI;
}

function guid() {
	"use strict";
    function p8(s) {
        var p = (Math.random().toString(16)+"000000000").substr(2,8);
        return s ? "-" + p.substr(0,4) + "-" + p.substr(4,4) : p ;
    }
    return p8() + p8(true) + p8(true) + p8();
}

function getIdxById(arr, id) {
	"use strict";
	var i, iLen = arr.length;
	for ( i = 0; i < iLen; i += 1) {
		if (arr[i].id === id) {return i;}
	}
}

// Default textures - stored as Base64 data
var woodTex = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/4RFYRXhpZgAASUkqAAgAAAAIABoBBQABAAAAbgAAABsBBQABAAAAdgAAACgBAwABAAAAAgAAADEBAgALAAAAfgAAADIBAgAUAAAAigAAAGmHBAABAAAAthAAABzqBwAMCAAAngAAABzqBwAMCAAAqggAAAAAAABIAAAAAQAAAEgAAAABAAAAR0lNUCAyLjguMgAAMjAxMzoxMDozMCAxNToyNDo1NwAc6gAAAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABzqAAAACAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACQAAkAcABAAAADAyMTADkAIAFAAAACgRAAAEkAIAFAAAADwRAACRkgIAAwAAADAwAOmSkgIAAwAAADAwAOoAoAcABAAAADAxMDABoAMAAQAAAP//6usCoAQAAQAAAAACAAADoAQAAQAAAAACAAAAAAAAMjAwNzowOTowNCAxMjozNjowMQAyMDA3OjA5OjA0IDEyOjM2OjAxAP/hBbNodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvADw/eHBhY2tldCBiZWdpbj0n77u/JyBpZD0nVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkJz8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0nYWRvYmU6bnM6bWV0YS8nPgo8cmRmOlJERiB4bWxuczpyZGY9J2h0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMnPgoKIDxyZGY6RGVzY3JpcHRpb24geG1sbnM6eG1wPSdodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvJz4KICA8eG1wOkNyZWF0b3JUb29sPkFkb2JlIFBob3Rvc2hvcCBDUzIgV2luZG93czwveG1wOkNyZWF0b3JUb29sPgogIDx4bXA6Q3JlYXRlRGF0ZT4yMDA3LTA5LTA0VDEyOjM2OjAxPC94bXA6Q3JlYXRlRGF0ZT4KIDwvcmRmOkRlc2NyaXB0aW9uPgoKIDxyZGY6RGVzY3JpcHRpb24geG1sbnM6ZXhpZj0naHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8nPgogIDxleGlmOk9yaWVudGF0aW9uPlRvcC1sZWZ0PC9leGlmOk9yaWVudGF0aW9uPgogIDxleGlmOlhSZXNvbHV0aW9uPjcyPC9leGlmOlhSZXNvbHV0aW9uPgogIDxleGlmOllSZXNvbHV0aW9uPjcyPC9leGlmOllSZXNvbHV0aW9uPgogIDxleGlmOlJlc29sdXRpb25Vbml0PkluY2g8L2V4aWY6UmVzb2x1dGlvblVuaXQ+CiAgPGV4aWY6U29mdHdhcmU+QWRvYmUgUGhvdG9zaG9wIENTMiBXaW5kb3dzPC9leGlmOlNvZnR3YXJlPgogIDxleGlmOkRhdGVUaW1lPjIwMTA6MDc6MTMgMDA6MTU6NTI8L2V4aWY6RGF0ZVRpbWU+CiAgPGV4aWY6UGFkZGluZz4yMDYwIGJ5dGVzIHVuZGVmaW5lZCBkYXRhPC9leGlmOlBhZGRpbmc+CiAgPGV4aWY6RXhpZlZlcnNpb24+RXhpZiBWZXJzaW9uIDIuMTwvZXhpZjpFeGlmVmVyc2lvbj4KICA8ZXhpZjpEYXRlVGltZU9yaWdpbmFsPjIwMDc6MDk6MDQgMTI6MzY6MDE8L2V4aWY6RGF0ZVRpbWVPcmlnaW5hbD4KICA8ZXhpZjpEYXRlVGltZURpZ2l0aXplZD4yMDA3OjA5OjA0IDEyOjM2OjAxPC9leGlmOkRhdGVUaW1lRGlnaXRpemVkPgogIDxleGlmOlN1YlNlY1RpbWVPcmlnaW5hbD4wMDwvZXhpZjpTdWJTZWNUaW1lT3JpZ2luYWw+CiAgPGV4aWY6U3ViU2VjVGltZURpZ2l0aXplZD4wMDwvZXhpZjpTdWJTZWNUaW1lRGlnaXRpemVkPgogIDxleGlmOkZsYXNoUGl4VmVyc2lvbj5GbGFzaFBpeCBWZXJzaW9uIDEuMDwvZXhpZjpGbGFzaFBpeFZlcnNpb24+CiAgPGV4aWY6Q29sb3JTcGFjZT5VbmNhbGlicmF0ZWQ8L2V4aWY6Q29sb3JTcGFjZT4KICA8ZXhpZjpQaXhlbFhEaW1lbnNpb24+MTAwMDwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgPGV4aWY6UGl4ZWxZRGltZW5zaW9uPjEwMDA8L2V4aWY6UGl4ZWxZRGltZW5zaW9uPgogIDxleGlmOlBhZGRpbmc+MjA2MCBieXRlcyB1bmRlZmluZWQgZGF0YTwvZXhpZjpQYWRkaW5nPgogPC9yZGY6RGVzY3JpcHRpb24+Cgo8L3JkZjpSREY+CjwveDp4bXBtZXRhPgo8P3hwYWNrZXQgZW5kPSdyJz8+Cv/iDFhJQ0NfUFJPRklMRQABAQAADEhMaW5vAhAAAG1udHJSR0IgWFlaIAfOAAIACQAGADEAAGFjc3BNU0ZUAAAAAElFQyBzUkdCAAAAAAAAAAAAAAABAAD21gABAAAAANMtSFAgIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEWNwcnQAAAFQAAAAM2Rlc2MAAAGEAAAAbHd0cHQAAAHwAAAAFGJrcHQAAAIEAAAAFHJYWVoAAAIYAAAAFGdYWVoAAAIsAAAAFGJYWVoAAAJAAAAAFGRtbmQAAAJUAAAAcGRtZGQAAALEAAAAiHZ1ZWQAAANMAAAAhnZpZXcAAAPUAAAAJGx1bWkAAAP4AAAAFG1lYXMAAAQMAAAAJHRlY2gAAAQwAAAADHJUUkMAAAQ8AAAIDGdUUkMAAAQ8AAAIDGJUUkMAAAQ8AAAIDHRleHQAAAAAQ29weXJpZ2h0IChjKSAx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";

var checkTex = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD//gATQ3JlYXRlZCB3aXRoIEdJTVD/2wBDABALDA4MChAODQ4SERATGCgaGBYWGDEjJR0oOjM9PDkzODdASFxOQERXRTc4UG1RV19iZ2hnPk1xeXBkeFxlZ2P/2wBDARESEhgVGC8aGi9jQjhCY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2P/wgARCAAgACADASIAAhEBAxEB/8QAFgABAQEAAAAAAAAAAAAAAAAAAAcG/8QAFAEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEAMQAAAB0AJ+ACgA/8QAFBABAAAAAAAAAAAAAAAAAAAAQP/aAAgBAQABBQIH/8QAFBEBAAAAAAAAAAAAAAAAAAAAIP/aAAgBAwEBPwEf/8QAFBEBAAAAAAAAAAAAAAAAAAAAIP/aAAgBAgEBPwEf/8QAFBABAAAAAAAAAAAAAAAAAAAAQP/aAAgBAQAGPwIH/8QAFBABAAAAAAAAAAAAAAAAAAAAQP/aAAgBAQABPyEH/9oADAMBAAIAAwAAABAAAAD/xAAUEQEAAAAAAAAAAAAAAAAAAAAg/9oACAEDAQE/EB//xAAUEQEAAAAAAAAAAAAAAAAAAAAg/9oACAECAQE/EB//xAAUEAEAAAAAAAAAAAAAAAAAAABA/9oACAEBAAE/EAf/2Q==";


