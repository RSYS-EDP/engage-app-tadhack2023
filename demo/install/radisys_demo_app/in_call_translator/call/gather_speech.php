<?php

//-------------------------------------------------------------------------------------------------
// This PHP file is part of the ICT (in-call translator) application. This file is responsible to 
// to gather user input.
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

// If this is caller
if ($role == 1) 
{
   $ml = $lang1;
} 
// If this is callee 
else if ($role == 2) 
{
   $ml = $lang2;
} 
// Otherwise, impossible
else {
   echo "<Response>";
   echo "<Say>Sorry, there's an application error, please try again later.</Say>";
   echo "</Response>";
   exit;
}

// If active_file is not existed, call must have been disconnected by the other party, play last message and then exit 
if (!file_exists($active_file)) {
   echo "<Response>";
   echo "<Say language='{$conf[$ml]['code1']}'>{$conf[$ml]['hangup']}</Say>";
   echo "</Response>";
   exit;
}

// This is the next document (itself) we need to execute when noinput
$next_uri  = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/gather_speech.php";
$ampersand = htmlspecialchars('&');
$next_uri .= "?CallerID={$caller_id}{$ampersand}Lang1={$lang1}{$ampersand}Lang2={$lang2}{$ampersand}Role={$role}{$ampersand}Play={$play}";

// This is the next docuement (save_speech) we need to execute when user input is collected
$action_uri  = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/save_speech.php";
$action_uri .= "?CallerID={$caller_id}{$ampersand}Lang1={$lang1}{$ampersand}Lang2={$lang2}{$ampersand}Role={$role}{$ampersand}Play={$play}";

// Build EML document 
echo "<Response>";
echo "<Gather language='{$conf[$ml]['code1']}' input='speech' action='{$action_uri}' speechTimeout='1' timeout='5'>";
echo "<Play>http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/clip/beep_AMR.3gp</Play>";
echo "</Gather>";
echo "<Say language='{$conf[$ml]['code1']}'>{$conf[$ml]['noinput']}</Say>";
echo "<Redirect>{$next_uri}</Redirect>";
echo "</Response>";

?>
