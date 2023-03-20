<?php

//---------------------------------------------------------------------------------------------------------
// This PHP file provides the function to delete the event file on certain call id. Sending HTTP DELETE 
// directly from browser to nginx web server does not work due to security reason. As a workaround, we 
// send HTTP GET request from browser to nginx web server to run this PHP code to delete the file. 
//---------------------------------------------------------------------------------------------------------

// Get CallID
$call_id = @$_REQUEST['CallID'];

// This is the name of json file
$json_file = "./events/" . $call_id . ".json";

// This is the name of txt file (containing user_input)
$txt_file = "./events/" . $call_id . "_user_input.txt";

// Read the user_input and set the HTTP response code accordingly
$user_input = file_get_contents($txt_file);
if ($user_input === false) {
	http_response_code(200);
} else if ($user_input == 1) {
	http_response_code(201);
} else if ($user_input == 2) {
	http_response_code(202);
} else {
	http_response_code(200);
}

// Delete the file
unlink($json_file);
unlink($txt_file);

?>
