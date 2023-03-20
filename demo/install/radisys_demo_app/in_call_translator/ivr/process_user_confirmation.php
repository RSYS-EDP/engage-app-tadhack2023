<?php

//------------------------------------------------------------------------------------------------
// This PHP file is used to process user confirmation (Yes/No) on their input 
//------------------------------------------------------------------------------------------------

// Make sure that MIME type is set to XML. This will ensure that XML is rendered properly
header('Content-type: application/xml');

// Get the caller_id, callee_number, callee_language from the URI 
$caller_id = $_REQUEST['CallerID'];
$callee_number = $_REQUEST['callee_number'];
$callee_language = $_REQUEST['callee_language'];

// Get user input
$confirmation = null;

// If user input is DTMF
if (@$_REQUEST['Digits'] != null) {
   if (@$_REQUEST['Digits'] == 1) {
	$confirmation = "yes";
   } else if (@$_REQUEST['Digits'] == 2) {
        $confirmation = "no";
   } 
}
// Otherwise this is speech input.
else {
   $confirmation = substr_replace(@$_REQUEST['SpeechResult'], "", -1);
}

// Need to encode & before including this character in URL
$ampersand = htmlspecialchars('&');

// Next url (if need to reprompt)
$next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/confirm_user_input.php?CallerID={$caller_id}";
$next_url = $next_url . $ampersand . "callee_number=". $callee_number . $ampersand . "callee_language=" . $callee_language;

// Build EML document
echo "<Response>";

switch (@$_REQUEST['Digits']) {

   // If user input is to proceed 
   case 1:
   	// Save user data
   	$user_data = array(
		"pstn"   => (strpos($_REQUEST['To'], "+") === 0),
        	"number" => $callee_number,
        	"lang1"  => 'English',
        	"lang2"  => $callee_language
   	);

   	// This is the name of json file to save the user data
   	$file = "../tmp/{$caller_id}_user_data.json";

   	// Save the user data in the file
   	file_put_contents($file, json_encode($user_data));

   	// Next URL is to make A2P call
   	$next_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/make_call.php?CallerID={$caller_id}";
   	echo "<Say>Thank you for the confirmation. I'm connecting you to {$callee_number} now and in-call translation service will be enabled in this call.</Say>";
	break;

   // If user input is to starover again 
   case 2:
   	$next_url =  "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/ivr/gather_callee_number.php?CallerID={$caller_id}";
	break;

   default:
   	echo "<Say>Sorry, I don't understand your input.</Say>";
	break;
}

echo "<Redirect>{$next_url}</Redirect>";
echo "</Response>";

//--------------------------------------------------------------------------------------------------
// This function is to check if provided language is supported or not
//--------------------------------------------------------------------------------------------------
function isLanguageSupported($language) {
   $supported_language = array("Chinese", "French", "Hindi", "German", "Japanese", "Spanish");

   for ($i=0; $i<sizeof($supported_language); $i++) {
    	if (strcasecmp($language, $supported_language[$i]) == 0)  return true;
   }

   return false;  
}

?>
