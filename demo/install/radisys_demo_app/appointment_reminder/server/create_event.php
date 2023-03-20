<?php

//---------------------------------------------------------------------------------------------------------
// This PHP file provides the function to receive call status event from EDP. The corresponding URL is 
// specified as StatusCallback parameter in the REST API make call request. After receiving the call status
// event, it will save the event into a json file which will be read by web application later to display 
// the event information on the web page to the user.
//---------------------------------------------------------------------------------------------------------

// Get CallID
$call_id = @$_REQUEST['CallID'];

// Get CallStatus
$call_status = strtolower(@$_REQUEST['CallStatus']);

// This array holds the newly received event information  
$new_event = array(
	"timestamp" => time(),
	"name" => "call_status",
	"value" => $call_status
); 

// This is the name of json file to contain all the call status events received from EDP on a given call ID
$json_file = "./events/" . $call_id . ".json";

// Read the existing content from the json file
$old_content = file_get_contents($json_file);

// If the json file is not existed (it is possible when this is a the first call-status event received from
// a new call), then create file and add first event into the file. Otherwise, append the new event to the
// existing file 
if ($old_content === false) {
	$array[0] = $new_event;
} else { 
	$array=json_decode($old_content, true);	
	$array[sizeof($array)] = $new_event;
}

// Convert the array into json content 
$new_content = json_encode($array);

// Save the new content into the json file
file_put_contents($json_file, $new_content);

?>
