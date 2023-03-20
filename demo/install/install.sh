#!/bin/bash

echo "##################################################################################################"
echo "#                                                                                                #"
echo "# This shell script is used to install Radisys demo applications on your web server for TADHack  #"
echo "# or any similar event. Prior to run this installation script, please make sure you have a valid #"
echo "# EDP account, your web server has been configured properly to run the Engage application, and a #"
echo "# valid Google API key (if you want to try out the in-call translation demo). You need to run    #"
echo "# this script only once, unless you want to re-install the demo applications to factory default. #" 
echo "# Enter Ctrl-C if you want to quit this setup.                                                   #"
echo "#                                                                                                #"
echo "##################################################################################################"
echo

# Collect server environment and EDP account information 
echo
read -p 'Your web server public IP: ' public_ip
read -p 'Your web server document root (e.g.: /usr/share/nginx/html) : ' document_root
read -p 'Your EDP account ID: ' account_id
read -p 'Your EDP API key: ' api_key
read -p 'Your Google API key: ' google_api_key
echo

# Install radisys demo application
sudo rm -rf $document_root/radisys_demo_app
cp -r radisys_demo_app $document_root/.
grep -RiIl '<MY_PUBLIC_IP>' $document_root/radisys_demo_app/ | xargs sed -i "s/<MY_PUBLIC_IP>/$public_ip/g"
grep -RiIl '<MY_EDP_ACCOUNT_ID>' $document_root/radisys_demo_app/ | xargs sed -i "s/<MY_EDP_ACCOUNT_ID>/$account_id/g"
grep -RiIl '<MY_EDP_API_KEY>' $document_root/radisys_demo_app/ | xargs sed -i "s/<MY_EDP_API_KEY>/$api_key/g"
grep -RiIl '<MY_GOOGLE_API_KEY>' $document_root/radisys_demo_app/ | xargs sed -i "s/<MY_GOOGLE_API_KEY>/$google_api_key/g"
sudo chown -R nginx:nginx $document_root/radisys_demo_app/in_call_translator/tmp
sudo chown -R nginx:nginx $document_root/radisys_demo_app/appointment_reminder/server/events

# Verify the installation and run the demo 
echo "Installation is completed. Please follow the instuctions below to test the demo application. "
echo

echo "Insurance Bot Demo (P2A):"
echo "- Open https://$public_ip/radisys_demo_app/insurance_bot/website/index.html?bot_number=XXX&agent_number=YYY in your browser to run insurance bot demo."
echo "- The actual insurance bot should have been already created in ESMP under your trial account, and mapped to an Engage phone number."
echo "- Replace XXX with the Engage phone number that is mapped to your insurance bot, and replace YYY with a RTC client number for agent transfer."
echo "- Click the button to speak with EVA. Enable microphone and camera access when running this web demo for the first time."
echo

echo "In-call Translation Demo (P2A2P):"
echo "- Subscribe a phone number (virtual or PSTN) from EDP and map this number to this app: http://$public_ip/radisys_demo_app/in_call_translator/ivr/main.php"
echo "- Make an audio call to this IVR app number, and then follow the DTMF-based IVR flow to provide the called party information before the 2-party call." 
echo "- This demo app translates the call between English and one of the following languages: French, Chinese, Japanese, German, Hindi, Spanish"
echo "- If the IVR app number is a virtual number, you need to call from RTC client and the called party need to be another RTC client."
echo "- If the IVR app number is a PSTN number, you can test this demo app in PSTN-2-PSTN call."
echo

echo "Appointment Reminder Demo (A2P):"
echo "- Open http://$public_ip/radisys_demo_app/appointment_reminder/client/index.html in your browser to run doctor appointment reminder demo"
echo "- Subscribe a phone number (virtual or PSTN) from EDP and use it as your From number to make A2P call"
echo "- If From number is a virtual number, To number must be a RTC client number"
echo "- If From number is a PSTN number, To number can be virtual or PSTN"
echo
