<?php

//-------------------------------------------------------------------------------------------------
// This PHP file is excuted when IVR flow (P2A call between caller and app) completes and user data
// is ready. This function will issue REST API request to make A2P call to the called party, which
// will start another application on the server side to handle the callee side communication.
//
// There're 2 engage applications involved in ICT demo. One application is to handle P2A call from 
// the caller, and another application is to handle the A2P call to the callee. Both applications
// are running in parallel to communicate with caller and callee on behalf of the other party.
//-------------------------------------------------------------------------------------------------

header('Content-type: application/xml');

// Load the language code configuration and convert to an associative array
$conf = json_decode(file_get_contents("./language.conf"), true);

// Get the caller_id from URI 
$caller_id = $_REQUEST['CallerID'];

// Get the IVR app number, use this number as from number in A2P call
$app_number = $_REQUEST['To'];

// This is the name of the file contains the user data
$file = "../tmp/{$caller_id}_user_data.json";

// If user data file is not found (not expected)
if (!file_exists($file))
{
   echo "<Response>";
   echo "<Say>Sorry, there's an application error, please try again later.</Say>";
   echo "</Response>";
   exit;
}

// Read the user_data from the json file
$user_data = json_decode(file_get_contents($file), true);
$pstn = $user_data['pstn'];
$number = $user_data['number'];
$lang1 = $user_data['lang1'];
$lang2 = $user_data['lang2'];

// Get caller's language code for TTS and translation
$lang_code1 = $conf[$lang1]['code1'];
$lang_code2 = $conf[$lang1]['code2'];

// Create following files at the beginning of the call. These files along with the user data will be deleted at the end of the call
//
// _active.txt: no content, the existance of the file indicates call is active, otherwise call is disconnected
// _caller_speech.json: json file contains speech collected from caller
// _callee_speech.json: json file contains speech collected from callee
// _callee_event.json: json file contains call status events from callee side of the call
//
file_put_contents("../tmp/{$caller_id}_active.txt", "");
file_put_contents("../tmp/{$caller_id}_caller_speech.json", "[]");
file_put_contents("../tmp/{$caller_id}_callee_speech.json", "[]");
file_put_contents("../tmp/{$caller_id}_callee_event.json", "[]");

// Make A2P call
if (!make_call($caller_id, $app_number, $pstn, $number, $lang1, $lang2))
{
   $err = "Sorry, I'm not able to connect you to {$number} at this moment. Please try again later.";
   $err = translate($err, 'en', $lang_code2);

   echo "<Response>";
   echo "<Say language='{$lang_code1}'>{$err}</Say>";
   echo "</Response>";
   exit;
}

// This is the next document we need to execute
$next_uri  = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/welcome.php";
$ampersand = htmlspecialchars('&');
$next_uri .= "?CallerID={$caller_id}{$ampersand}Lang1={$lang1}{$ampersand}Lang2={$lang2}{$ampersand}Role=1";

echo "<Response>";
echo "<Redirect>{$next_uri}</Redirect>";
echo "</Response>";

//----------------------------------------------------------------------------------------------
// This function is to make REST API call request to make A2P call to the given number
//-----------------------------------------------------------------------------------------------
function make_call($caller_id, $from_number, $pstn, $to_number, $lang1, $lang2) {

   // EDP account-id and api-key are used here
   $account_id = "<MY_EDP_ACCOUNT_ID>";
   $api_key = "<MY_EDP_API_KEY>";

   // This is the uri for HTTP post request
   $url = "https://apigateway.engagedigital.ai/api/v1/accounts/" . $account_id . "/call";

   $curl = curl_init();

   curl_setopt($curl, CURLOPT_URL, $url);
   curl_setopt($curl, CURLOPT_POST, true);
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

   $headers = array(
	"apikey: " . $api_key,
   	"Content-Type: application/x-www-form-urlencoded;charset=UTF-8"
   );
   curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

   // This is the from number and corresponding EML app URL to be enabled on this A2P call
   $from = $from_number;
   $app_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/welcome.php";
   $app_url .= "?CallerID={$caller_id}&Lang1={$lang1}&Lang2={$lang2}&Role=2";
   $statuscallback_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/in_call_translator/call/callee_event_handler.php";
   $statuscallback_url .= "?CallerID={$caller_id}";

   $data  = "From=".urlencode($from);
   $data .= "&To=".($pstn?urlencode('+'.$to_number):urlencode('sip:'.$to_number.'@sipaz1.engageio.com'));
   $data .= "&Type=voice";
   $data .= "&Url=".urlencode($app_url);
   $data .= "&Method=POST";
   $data .= "&StatusCallback=".urlencode($statuscallback_url);
   $data .= "&StatusCallbackMethod=POST";
   $data .= "&StatusCallbackEvent=initiated,ringing,answered,completed";

   // Build the POST request data
   curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

   // Send the REST API request and get the response JSON body (in raw string format)
   $response = curl_exec($curl);

   // Get the HTTP response code
   $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

   $err = curl_error($curl);

   curl_close($curl);

   // If failed to send REST API request for any reason
   if ($err) {
        // echo "cURL Error #:" . $err;
        return false;
   }

   if ($httpcode != 200) {
        // echo "HTTP response code: " . $httpcode . "\n";
        // echo $response . "\n";
        return false;
   }

   return true;
}

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

