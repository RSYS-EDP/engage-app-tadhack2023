<?php

//----------------------------------------------------------------------------------------
// This PHP file is used to Gather user input for callee_number
//----------------------------------------------------------------------------------------

// Make sure that MIME type is set to XML. This will ensure that XML is rendered properly
header('Content-type: application/xml');

// Get the caller_id from the URI. 
$caller_id = $_REQUEST['CallerID'];

// Prompt message
$prompt_message = "Please enter the RTC client number you want to dial";

// If the To number (i.e.: IVR app number) is a PSTN number, you can make a call to PSTN number. Otherwise, callee is RTC client.
if (strpos($_REQUEST['To'], "+") === 0) {
   $prompt_message = "Please enter the PSTN number you want to dial, start with the country code";
}

// Next url 
$action_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/process_callee_number.php?CallerID={$caller_id}";
$next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/gather_callee_number.php?CallerID={$caller_id}";

echo "<Response>";
echo "<Gather input='dtmf' action='{$action_url}' numDigits='20' finishOnKey='#' timeout='5'>";
echo "<Say>{$prompt_message}</Say>";
echo "</Gather>";
echo "<Say>Sorry, I did not receive your input</Say>";
echo "<Redirect>{$next_url}</Redirect>";
echo "</Response>";

?>
