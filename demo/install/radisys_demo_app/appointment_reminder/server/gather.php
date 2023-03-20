<?php

// Make sure that MIME type is set to XML. This will ensure that XML is rendered properly
header('Content-type: application/xml');

// Get CallID
$call_id = @$_REQUEST['CallID'];

// This is the name of text file to contain user input (1 or 2) from the IVR, initialize the content with 0 indicates noinput 
$user_input_file = "./events/" . $call_id . "_user_input.txt";
file_put_contents($user_input_file, "0");

// URL for the main menu
$main_menu_url = "http://<MY_PUBLIC_IP>/radisys_demo_app/appointment_reminder/server/main.xml"; 

echo "<Response>";

switch(@$_REQUEST['Digits']) {
	// confirm the appointment  
	case 1:
		file_put_contents($user_input_file, "1");
		echo "<Say>Your appointment has been confirmed. Goodbye!</Say>";
		break;

        // cancel the appointment  
        case 2:
                file_put_contents($user_input_file, "2");
                echo "<Say>Your appointment has been canceled. Goodbye!</Say>";
                break;

        default:
	        echo "<Redirect>{$main_menu_url}</Redirect>";
		break;
}

echo "</Response>";

?>
