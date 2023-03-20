<?php

//------------------------------------------------------------------------------------------------
// This PHP file is used to verify user input for callee_language
//------------------------------------------------------------------------------------------------

// Make sure that MIME type is set to XML. This will ensure that XML is rendered properly
header('Content-type: application/xml');

// This is the list of supported lanaguage for callee
$supported_language = array("Chinese", "Hindi", "French", "German", "Japanese", "Spanish");

// Get the caller_id & callee_number from the URI 
$caller_id = $_REQUEST['CallerID'];
$callee_number = $_REQUEST['callee_number'];

// Need to encode & before including this character in URL
$ampersand = htmlspecialchars('&');

// Next url (if provided language is not supported)
$next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/gather_callee_language.php?CallerID={$caller_id}";
$next_url = $next_url . $ampersand . "callee_number=". $callee_number;

// Build EML document
echo "<Response>";

// Get user input 
$index = intval(@$_REQUEST['Digits']);

// If it is valid input
if ($index >=1 && $index <= sizeof($supported_language)) {
 	$callee_language=$supported_language[$index-1];
        $next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/gather_user_confirmation.php?CallerID={$caller_id}";
        $next_url = $next_url . $ampersand . "callee_number=". $callee_number . $ampersand . "callee_language=" . $callee_language;
}
// Otherwise it is invalid input 
else {
        echo "<Say>Sorry, {$index} is not a supported Language option</Say>";
}

echo "<Redirect>{$next_url}</Redirect>";
echo "</Response>";

//--------------------------------------------------------------------------------------------------
// This function is to check if provided language is supported or not
//--------------------------------------------------------------------------------------------------
function isLanguageSupported($supported, $language) {
   for ($i=0; $i<sizeof($supported); $i++) {
    	if (strcasecmp($language, $supported[$i]) == 0)  return true;
   }
   return false;  
}

?>
