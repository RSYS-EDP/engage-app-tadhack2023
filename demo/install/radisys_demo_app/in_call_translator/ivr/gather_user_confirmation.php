<?php

//----------------------------------------------------------------------------------------
// This PHP file is used to Gather user's confirmation (yes/no) for their input
//----------------------------------------------------------------------------------------

// Make sure that MIME type is set to XML. This will ensure that XML is rendered properly
header('Content-type: application/xml');

// Get the caller_id, callee_number and callee_language from the URI 
$caller_id = $_REQUEST['CallerID'];
$callee_number = $_REQUEST['callee_number'];
$callee_language = $_REQUEST['callee_language'];

// Need to encode & before including this character in URL 
$ampersand = htmlspecialchars('&');

$action_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/process_user_confirmation.php?CallerID={$caller_id}";
$action_url = $action_url . $ampersand . "callee_number=". $callee_number . $ampersand . "callee_language=" . $callee_language;

$next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/gather_user_confirmation.php?CallerID={$caller_id}";
$next_url = $next_url . $ampersand . "callee_number=". $callee_number . $ampersand . "callee_language=" . $callee_language;

echo "<Response>";
echo "<Gather input='dtmf' action='{$action_url}' numDigits='1' finishOnKey='#' timeout='2'>";
echo "<Say>
	Based on your input, the called party's phone number is {$callee_number}, and speaking {$callee_language}, 
	To confirm, press 1. If you want to make a change, press 2.
      </Say>";
echo "</Gather>";
echo "<Say>Sorry, I did not receive your input</Say>";
echo "<Redirect>{$next_url}</Redirect>";
echo "</Response>";

?>
