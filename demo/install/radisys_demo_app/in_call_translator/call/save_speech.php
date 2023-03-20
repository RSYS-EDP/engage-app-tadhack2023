<?php

//-------------------------------------------------------------------------------------------------
// This PHP file is part of the ICT (in-call translator) application. This file is responsible to 
// collect the speech from user (caller or callee) based on the role, and save the speech in the 
// corresponding speech file. Once it is done, back to the play_speech.php 
//-------------------------------------------------------------------------------------------------

header('Content-type: application/xml');

// Get caller's language code for TTS and translation
$conf = json_decode(file_get_contents("./language.conf"), true);

// Get uri-parameters
$caller_id = $_REQUEST['CallerID'];
$lang1 = $_REQUEST['Lang1'];
$lang2 = $_REQUEST['Lang2'];
$role = intval($_REQUEST['Role']);
$play = intval($_REQUEST['Play']);

// This is the active file name
$active_file = "../tmp/{$caller_id}_active.txt";

// This is the new speech collected from the caller
$speech = @$_REQUEST['SpeechResult'];

// If this is caller 
if ($role == 1) 
{
   $speech_file = "../tmp/{$caller_id}_caller_speech.json";
   $ml = $lang1;
   $pl = $lang2;
} 
// If this is callee 
else if ($role == 2) 
{
   $speech_file = "../tmp/{$caller_id}_callee_speech.json";
   $ml = $lang2;
   $pl = $lang1;
} 
// Otherwise, impossible
else {
   echo "<Response>";
   echo "<Say>Sorry, there's an application error, please try again later.</Say>";
   echo "</Response>";
   exit;
}

// If active_file or speech_file is not existed, call must have been disconnected by the other party, play last message and then exit 
if (!file_exists($active_file) || !file_exists($speech_file)) {
   echo "<Response>";
   echo "<Say language='{$conf[$ml]['code1']}'>{$conf[$ml]['hangup']}</Say>";
   echo "</Response>";
   exit;
}

// Add the new speech to the speech_file
$data = json_decode(file_get_contents($speech_file), true);
$new = array(
   "timestamp" => time(),
   "text" => $speech
);
array_push($data, $new);
file_put_contents($speech_file, json_encode($data), LOCK_EX);

// This is the next document (play_speech) we need to execute
$next_uri  = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/play_speech.php";
$ampersand = htmlspecialchars('&');
$next_uri .= "?CallerID={$caller_id}{$ampersand}Lang1={$lang1}{$ampersand}Lang2={$lang2}{$ampersand}Role={$role}{$ampersand}Play={$play}";

echo "<Response>";
echo "<Redirect>{$next_uri}</Redirect>";
echo "</Response>";

?>
