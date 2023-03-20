<?php

//--------------------------------------------------------------------------------------------
// This PHP file is the entry function of ICT (in-call translation) demo application. A phone
// number is mapped to this Engage application. To run the in-call translation demo, user can
// call this number to start an IVR flow to collect necessary information. 
//--------------------------------------------------------------------------------------------

// Make sure that MIME type is set to XML. This will ensure that XML is rendered properly
header('Content-type: application/xml');

// Get the from number
$from = $_REQUEST['From'];

// If from number contains + character in the front, strip it off. Otherwise you need to use
// urlencode to encode this character when including it in HTTP-POST uri
if (strpos($from, "+") === 0) {
   $from = substr($from, 1);
}

// Build the caller_id based on GMT timestamp and From number
$caller_id = gmdate("Y-m-d") . "_" . gmdate("H:i:s") . "_" . $from;

// Next url 
$next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/gather_callee_number.php?CallerID={$caller_id}";

echo "<Response>";
echo "<Say>Hi, welcome to use Radisys in-call translation service</Say>";
echo "<Redirect>{$next_url}</Redirect>";
echo "</Response>";

?>
