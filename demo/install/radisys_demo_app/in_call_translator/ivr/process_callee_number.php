<?php

//---------------------------------------------------------------------------------------
// This PHP file is used to verify the user input for callee_number
//---------------------------------------------------------------------------------------

// Make sure that MIME type is set to XML. This will ensure that XML is rendered properly
header('Content-type: application/xml');

// Get caller_id from URI
$caller_id = $_REQUEST['CallerID'];

// Get callee_number
$callee_number = @$_REQUEST['Digits'];

// Build EML document
echo "<Response>";

// Move to next EML document 
$ampersand = htmlspecialchars('&');
$next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/gather_callee_language.php?CallerID={$caller_id}";
$next_url = $next_url . $ampersand . "callee_number={$callee_number}";

echo "<Redirect>{$next_url}</Redirect>";
echo "</Response>";

?>
