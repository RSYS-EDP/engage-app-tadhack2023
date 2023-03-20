<?php

//----------------------------------------------------------------------------------------
// This PHP file is used to Gather user input for callee_language
//----------------------------------------------------------------------------------------

// Make sure that MIME type is set to XML. This will ensure that XML is rendered properly
header('Content-type: application/xml');

// Get the caller_id and callee_number from the URI 
$caller_id = $_REQUEST['CallerID'];
$callee_number = $_REQUEST['callee_number'];

// Need to encode & before including this character in URL 
$ampersand = htmlspecialchars('&');

$action_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/process_callee_language.php?CallerID={$caller_id}";
$action_url = $action_url . $ampersand . "callee_number=". $callee_number;

$next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/gather_callee_language.php?CallerID={$caller_id}";
$next_url = $next_url . $ampersand . "callee_number=". $callee_number;

echo "<Response>";
echo "<Gather input='dtmf' action='{$action_url}' numDigits='1' finishOnKey='#' timeout='2'>";
echo "<Say>
 	What language does the called party speak?
        For Chinese language, press 1.
	For Hindi language, press 2.
	For French language, press 3.
	For German language, press 4.
	For Japanese language, press 5.
	For Spanish language, press 6.
      </Say>";
echo "</Gather>";
echo "<Say>Sorry, I did not receive your input</Say>";
echo "<Redirect>{$next_url}</Redirect>";
echo "</Response>";

?>
