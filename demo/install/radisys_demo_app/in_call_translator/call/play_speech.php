<?php

//-------------------------------------------------------------------------------------------------
// This PHP file is part of the ICT (in-call translator) application. This file is responsible to 
// read the speech_file from caller or callee, translate, and play the TTS to the callee or caller.
//
// Role = 1: 
// 
// This is caller side translator, who will collect speech from caller, transcribe, and save text in
// caller_speech file; at the same time, it reads the text from callee_speech file, translate to 
// caller's language, and play TTS to caller. 
//
// Role = 2: 
//
// This is callee side translator, who will collect speech from callee, transcribe, and save text in
// callee_speech file; at the same time, it reads the text from caller_speech file, translate to
// callee's language, and play TTS to callee.
//
// Play: 0-x
//
// This is the index of the array which we need to read from the speech_file and play. 
//
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

// If this is caller's translator, read callee_speech, set my and peer language accordingly
if ($role == 1) 
{
   $speech_file = "../tmp/{$caller_id}_callee_speech.json";
   $ml = $lang1;
   $pl = $lang2;
} 
// If this is callee's translator, read caller_speech, set my and peer language accordingly 
else if ($role == 2) 
{
   $speech_file = "../tmp/{$caller_id}_caller_speech.json";
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

echo "<Response>";

// Read contents from the speech_file, with LOCK_SH 
$tmp = fopen($speech_file, 'rb');
@flock($tmp, LOCK_SH);
$data = json_decode(file_get_contents($speech_file), true);
@flock($tmp, LOCK_UN);
fclose($tmp);

// The speech file contains the speech collected from other party in order, $play is the index where we need to start
for ($i=$play;$i<sizeof($data);$i++) {
   $message = translate($data[$i]['text'], $conf[$pl]['code2'], $conf[$ml]['code2']);
   echo "<Say language='{$conf[$ml]['code1']}'>{$message}</Say>";
}

// If any new speech from other party is received and played, next document is to gather_speech (switch to speak mode)
if ($i != $play) 
{
   $next_uri  = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/gather_speech.php";
}
// If nothing new is received from other party, next document is to play_speech (continue to listen mode) 
else {
   $next_uri  = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/play_speech.php";
}
$ampersand = htmlspecialchars('&');
$next_uri .= "?CallerID={$caller_id}{$ampersand}Lang1={$lang1}{$ampersand}Lang2={$lang2}{$ampersand}Role={$role}{$ampersand}Play={$i}";

echo "<Redirect>{$next_uri}</Redirect>";
echo "</Response>";

//-------------------------------------------------------------------------------------------------
// This function is used to call Google translation API to translate text from one language to another
//
// Input parameters:
//
//      $q: input text, string value, written in source language
//      $sl: source language
//      $tl: target language
//
// Return:
//      Output text in target language (string value)
//------------------------------------------------------------------------------------------------
function translate($q, $sl, $tl){
    if ($sl == $tl)     return $q;

    $api_key = '<MY_GOOGLE_API_KEY>';
    $url = 'https://translation.googleapis.com/language/translate/v2?key='.$api_key.'&q='.rawurlencode($q).'&target='.$tl.'&source='.$sl;

    $response = file_get_contents($url);
    $obj =json_decode($response,true);

    if($obj != null)
    {
        if(isset($obj['error']))
        {
                return "Error is : ".$obj['error']['message'];
        }
        else
        {
                return $obj['data']['translations'][0]['translatedText'];
        }
    }
    else
    {
        return "UNKNOW ERROR";
    }

}


?>
