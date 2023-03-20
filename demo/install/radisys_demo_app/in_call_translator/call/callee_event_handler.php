<?php

//---------------------------------------------------------------------------------------------------------
// This PHP file is part of ICT (in-call translator) application. The corresponding URL is specified as 
// StatusCallback parameter in the REST API make call request. After receiving the call status event from
// callee, it will save the event into a json file which will be read by web application later to display 
// the event information on the web page to the user.
//---------------------------------------------------------------------------------------------------------

// Get caller_id 
$caller_id = @$_REQUEST['CallerID'];

// Get CallStatus
// $call_status = "ringing";
$call_status = strtolower(@$_REQUEST['CallStatus']);

// If this is one of the following events, means callee did not answer the call or disconnected the call
if ($call_status == "completed" || 
    $call_status == "no-answer" ||
    $call_status == "busy" ||
    $call_status == "canceled" ||
    $call_status == "failed") {
   // delete the active_file
   unlink("../tmp/{$caller_id}_active.txt");
}

// This array holds the newly received event information  
$new_event = array(
	"timestamp" => time(),
	"name" => "call_status",
	"value" => $call_status
); 

// This is the name of json file to contain all the call status events received from EDP on a given call ID
$json_file = "../tmp/{$caller_id}_callee_event.json";

// If this file is not existed, it means call has been disconnected, ignore the event and do nothing
if (!file_exists($json_file)) {
   exit;
}

// Append the new event to the existing list and update the file
$event_list = json_decode(file_get_contents($json_file), true);
array_push($event_list, $new_event);
$event_list = json_encode($event_list);
file_put_contents($json_file, $event_list);

?>
