<?php

if ($argc != 2) {
   echo "Usage: php parse_speech_file <caller_id>\n";
   exit;
}

$caller_speech_file = $argv[1]."_caller_speech.json";
$callee_speech_file = $argv[1]."_callee_speech.json";

if (!file_exists($caller_speech_file)) {
   echo "{$caller_speech_file} is not found\n";
   exit;
}

if (!file_exists($callee_speech_file)) {
   echo "{$callee_speech_file} is not found\n";
   exit;
}

// Read both speech files and convert the contents into array
$caller_speech_data = json_decode(file_get_contents($caller_speech_file), true);
$callee_speech_data = json_decode(file_get_contents($callee_speech_file), true);

// This array is used to build the complete conversation
$conversation = array();

// Fetch the first speech from each array
$speech1 = array_shift($caller_speech_data);
$speech2 = array_shift($callee_speech_data);

// Mix caller and callee speech in one conversation based on timestamp
while (1) {

   // If this is the end for both array, done
   if ($speech1 == null && $speech2 == null) 
   {
	break;
   }
   // If this is the end for callee speech
   else if ($speech1 != null && $speech2 == null)
   {
        $message = array(
                "role" => "caller",
                "timestamp" => gmdate('Y-m-d H:i:s \G\M\T', intval($speech1['timestamp'])),
                "text" => $speech1['text']
        );
        $speech1 = array_shift($caller_speech_data);
   }
   // If this is the end for caller speech
   else if ($speech1 == null && $speech2 != null)
   {
        $message = array(
                "role" => "callee",
                "timestamp" => gmdate('Y-m-d H:i:s \G\M\T', intval($speech2['timestamp'])),
                "text" => $speech2['text']
        );
        $speech2 = array_shift($callee_speech_data);
   }
   // If both speech are available
   else 
   {   
   	if ($speech1['timestamp'] <= $speech2['timestamp']) {
   		$message = array(
			"role" => "caller",
			"timestamp" => gmdate('Y-m-d H:i:s \G\M\T', intval($speech1['timestamp'])),
        		"text" => $speech1['text']
   		);
   		$speech1 = array_shift($caller_speech_data);   
   	} else {
   		$message = array(
        		"role" => "callee",
        		"timestamp" => gmdate('Y-m-d H:i:s \G\M\T', intval($speech2['timestamp'])),
        		"text" => $speech2['text']
   		);
   		$speech2 = array_shift($callee_speech_data);
   	}
   }

   // Add message into conversation 
   array_push($conversation, $message);

} 

// Dump the conversation history
for ($i=0; $i<sizeof($conversation); $i++) {
   if ($conversation[$i]['role'] == 'caller') {
	echo "[Caller]: {$conversation[$i]['timestamp']} -> {$conversation[$i]['text']}\n";
   } else {
        echo "[Callee]: {$conversation[$i]['timestamp']} -> {$conversation[$i]['text']}\n";
   }
}


?>
