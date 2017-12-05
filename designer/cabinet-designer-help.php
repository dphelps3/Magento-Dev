<?php 
include('bitbucket.lib.php');
?>
<!DOCTYPE html>
<html>
<head>
<title>Cabinet Design Layout Tool Help</title>
	<style>
	 body{
			font-family: Arial, Helvetica, sans-serif;
			font-size:13px;
		}
		
		/* Example styling the bug form and messages. */
		.bugformsuccess{
			color:#00cc00;
		}
		.bugformerror{
			color:#dd0000;
		}
		.bugform input,
		.bugform textarea{
			width:260px;
			border: 1px solid #0000aa;
			background-color:#f6f6f6;
			padding: 5px;
			font-family: Arial, Helvetica, sans-serif;
			font-size:1.1em;
			border-radius: 5px;
		}
		.bugform input:focus,
		.bugform textarea:focus {
			background: #fff;
			border: 1px solid #9999ff;
		}
		.bugformsubmit{
			width:100px !important;
			border-width:2px !important;
		}
		.bugformsubmit:hover{
			cursor:pointer;
			background: #fff;
			border: 1px solid #9999ff;
		}
		.bugform label{
			padding-top:4px;
		}
	</style>
</head>
<body>
<a href="http://cshardware.com"><img src="http://www.cshardware.com/skin/frontend/enterprise/wood/images/cshardware_logo.png" title="Custom Service Hardware" /></a>
<h1>How to use this designer</h1>
<h3>1. Cabinet Lines</h3>
This is a list of several cabinet lines available from Custom Service Hardware. Select one to use in your design by clicking on the image. Each cabinet line varies in finish and price. Your price estimate will be shown in the upper right header.
<h3>2. Products</h3>
Each cabinet line has a number of available products. Some are only available in the selected lines, while others are standard products in all lines. You may search for products by portions of their descriptions. To add a product to your design, simply click on the image. It will be added to the middle of your design. You can click and drag it from here to anywhere on the design.
<h3>3. Design</h3>

<h3>4. Tools</h3>
<ul>
<li>Snapshot: Takes a screenshot of the 3D designer that you can save as an image. Right click the image and select "Save as..." to save it to your computer.</li>
<li>Shopping Cart: Allows you to add your design directly to your cshardware.com shopping cart.</li>
<li>Download: Download a PDF with your design and a list of cabinet items.</li>
<li>Share: Send an email link to a friend, family member, or contractor with the design.</li>
<li>Clear All: Removes everything from the design so you can start from scratch.</li>
</ul>
<h1>The designer is not working on my computer!</h1>
Custom Service Hardware's cabinet design tool is unique in that it does not require you to download anything - it just runs directly in your browser using a technology called WebGL. This is good news for everyone running up-to-date browsers, but older browsers such as Internet Explorer 7 do not support WebGL. The following browsers support WebGL and have been tested with our design tool:
<ul>
<li><a href="http://windows.microsoft.com/en-us/internet-explorer/ie-11-worldwide-languages">Microsoft Internet Explorer 11 (Requires Windows 7 or higher)</a></li>
<li><a href="https://www.google.com/chrome/browser/">Google Chrome</a></li>
<li><a href="https://download.mozilla.org/?product=firefox-32.0-SSL&os=win&lang=en-US">Mozilla Firefox</a></li>
<li><a href="http://www.opera.com/computer">Opera Browser</a></li>
</ul>

<h1>Using an iOS device?</h1>
iPhones, iPads, and other iOS devices can support WebGL only in iOS version 8 or newer. If you are running a device with iOS 7 or lower, WebGL will not function in your web browser. Please visit <a href="https://www.apple.com/ios/">Apple's iOS page</a> for upgrade details. Please note that this application is not yet optimized for mobile devices.

<h1>Using an Android device?</h1>
Most new Android devices will display this tool, but full functionality is not supported at this time.

<h1>Why are the cabinets red?</h1>
The red color indicates that the cabinet line you have chosen does not have the exact cabinet that you wanted in your design. In some cases there are equivalent or very similar models available.

<h1>How do I change the height of where the cabinet sits?</h1>
When you select the cabinet in the designer, a set of tools will appear on the left hand side of the grid view. You will see a slider labeled "ELEVATION(in)". This represents the elevation of the cabinet product in inches. You may drag this up or down to adjust the cabinet's current elevation.

<h1>Having issues not addressed here?</h1>
<?php

// Config Values:
$basicAuth = 'dmJuZXQzZDpAUGl6emEwMQ==';
$bitBucketAccount = 'vbnet3d';
$bitBucketRepo = 'cabinet-designer';
$issueComponent = 'Unsorted';
$companyName = 'Custom Service Hardware';
$fromEmail = 'service@cshardware.com';

// Process any POST.
if( isset($_POST) && !empty($_POST['bugformtitle']) && !empty($_POST['bugformdescription']) ){
    $status = submitBug($_POST['bugformtitle'], $_POST['bugformdescription'], $_POST['bugformuser'], $bitBucketAccount, $bitBucketRepo, $basicAuth, $issueComponent);
    if($status === FALSE){
        echo("<span class='bugformerror'>Sorry, there was an error submitting your issue. Please try again later or contact Support directly.</span>");
    }else{
        echo("<span class='bugformsuccess'>Thank you, your issue <b># ".$status['issueid']."</b> has been submitted.</span>");
        sendBugEmail($_POST['bugformuser'], $status['issueid'], $companyName, $fromEmail, $status['issueurl']);  // Leave URL parameter blank if you don't want it in the email.
    }      
}

// Display bug submission form.
showBugForm();

?>
</body>

</html>