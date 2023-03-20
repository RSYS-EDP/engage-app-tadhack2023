<?php

//-------------------------------------------------------------------------------------------------
// This PHP file is part of the ICT (in-call translator) application. This file is responsible to 
// play a welcome message to caller / callee before the real converstation starts
//-------------------------------------------------------------------------------------------------

header('Content-type: application/xml');

// Get caller's language code for TTS and translation
$conf = json_decode(file_get_contents("./language.conf"), true);

// Get uri-parameters
$caller_id = $_REQUEST['CallerID'];
$lang1 = $_REQUEST['Lang1'];
$lang2 = $_REQUEST['Lang2'];
$role = intval($_REQUEST['Role']);

// If this is caller, starts with listen mode
if ($role == 1) 
{
   $code = $conf[$lang1]['code1'];
   $message = $conf[$lang1]['welcome1'];
   $next_uri  = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/play_speech.php";
} 
// If this is callee, starts with speak mode, callee needs to say something to start the converstation 
else if ($role == 2) 
{
   $code = $conf[$lang2]['code1'];
   $message = $conf[$lang2]['welcome2'];
   $next_uri  = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/gather_speech.php";
} 
// Otherwise, impossible
else {
   echo "<Response>";
   echo "<Say>Sorry, there's an application error, please try again later.</Say>";
   echo "</Response>";
   exit;
}

// This is the next document we need to execute
$ampersand = htmlspecialchars('&');
$next_uri .= "?CallerID={$caller_id}{$ampersand}Lang1={$lang1}{$ampersand}Lang2={$lang2}{$ampersand}Role={$role}{$ampersand}Play=0";

echo "<Response>";
echo "<Say language='{$code}'>{$message}</Say>";
echo "<Redirect>{$next_uri}</Redirect>";
echo "</Response>";

?>
