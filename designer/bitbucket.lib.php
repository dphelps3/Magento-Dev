<?php
/* ******************************************************************************
 * Bitbucket External Issue Submission Library
 * Author - Sherri Wheeler - Avinus Web Services - http://SyntaxSeed.com/
 * License - GPLv3 (http://www.gnu.org/licenses/quick-guide-gplv3.html)
 * Version - 1.0
 * ****************************************************************************** */


/* Function submitBug - sends the bug to the bitbucket API. Must contain title and content. User name/email is optional.*/
function submitBug($title, $content, $user='Anonymous', $bbAccount, $bbRepo, $basicAuth, $component='', $status='new', $priority='major', $kind='bug'){

	// Spam avoidance
	if(isset($_POST['url']) && $_POST['url'] == "") {
		
		$url = 'https://api.bitbucket.org/1.0/repositories/'.$bbAccount.'/'.$bbRepo.'/issues/';
		$ch = curl_init($url);
		 
		if (get_magic_quotes_gpc()) {
			$title = stripslashes($title);
			$content = stripslashes($content);
			$user = stripslashes($user);
			$component = stripslashes($component);
			$bbAccount = stripslashes($bbAccount);
			$bbRepo = stripslashes($bbRepo);
		} 
		 
		$fields = array(
								'title' => urlencode($title),
								'content' => urlencode($content."\n\nSubmitted By: ".$user),
								'status' => urlencode($status),
								'priority' => urlencode($priority),
								'kind' => urlencode($kind),
								'component' => urlencode($component)
						); 
		 
		// Build POST url:
		$fieldsStr = '';
		foreach($fields as $key=>$value) { 
			$fieldsStr .= $key.'='.$value.'&'; 
		}
		rtrim($fieldsStr, '&');
		
		

		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$basicAuth));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsStr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		$response = curl_exec($ch);
		curl_close($ch);
		
		if( $response !== FALSE){
			$response = json_decode($response);        
			if(isset($response->local_id) && intval($response->local_id) > 0 ){
				$bugurl = "https://bitbucket.org/".ltrim($response->resource_uri, "/1.0/repositories/");
				$bugurl = str_replace('/issues/', '/issue/', $bugurl);
				return( array('issueid'=>$response->local_id, 'issueurl'=>$bugurl) );
			}else{
				return(FALSE);
			}
		}else{
			return(FALSE);
		}
    } else {
		return(FALSE);
	}
}

/* Function showBugForm - outputs a basic form for bug submission. Can be styled with CSS. */
function showBugForm(){
    ?>
    <style type="text/css">
        .bugform{
            width:450px;
        }
        .bugformfields{
            clear:both;
            margin:10px 0;
        }
        .bugform label{
            float:left;
            font-weight:bold;
            display:inline-block;
            width:140px;
            text-align:right;
            padding-right:10px;
        }
        .bugform label span{
            font-weight:normal;
            font-size:0.85em;
        }
		.antispam { display:none;}
    </style>
    <div class="bugform">
    <form method="post" action="<?=$_SERVER['PHP_SELF'];?>">

    <div class="bugformfields">
        <label>Title Of Issue*</label>
        <input type="text" name="bugformtitle" id="bugformtitle" size="40" value="" />
    </div>
    
    <div class="bugformfields">
        <label>Issue Description*<br /><span>(As much detail as possible.)</span></label>
        <textarea name="bugformdescription" id="bugformdescription" rows="6" cols="30"></textarea>
    </div>
    
    <div class="bugformfields">
        <label>Your Email</label>
        <input type="text" name="bugformuser" id="bugformuser" size="40" value="" />
    </div>
	
	<div class="antispam">
        <input class="antispam" type="text" name="url" />
    </div>
    
    <div class="bugformfields">
        <label>&nbsp;</label>
        <input type="submit" class="bugformsubmit" value="Submit Issue" />
    </div>
    </form>
    </div>
    <?php
}

/* Function - sendBugEmail sends an email with details of the submitted bug to the user for their reference. 
 * Leave the $url parameter blank for private repos. 
 */
function sendBugEmail($email, $bugNum, $siteName, $fromAddr, $url=FALSE){
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {   
        $subject = "Bug submitted to ".$siteName." (# ".$bugNum.")";
        $body = "Thank you for submitting your bug to ".$siteName."\n\n";
        $body .= "Your bug (# ".$bugNum.") has been received and we will notify you of any updates for this issue. Please refer to your bug # in future correspondence.\n\n";
        if($url !== FALSE && !empty($url)){
            $body .= "View bug status: ".$url.".\n\n";
        }
        
        $body .= "Sincerely,\n\nThe Support Team\n".$siteName;
        mail($email, $subject, $body, "From: ".$fromAddr);
    }
}