'use strict';

// ---------------------------------------------------------------------------------------------------
// Initialize global variables
// ---------------------------------------------------------------------------------------------------

// This is the default URL for EML-based application which will be executed when A2P call is connected 
const g_defaultAppUrl = "http://<MY_PUBLIC_IP>/radisys_demo_app/appointment_reminder/server/main.xml";

// This is the default URL for call status callback
const g_defaultStatusCallbackUrl = "http://<MY_PUBLIC_IP>/radisys_demo_app/appointment_reminder/server/create_event.php";

// This is the default folder of the json file containing the call status events
const g_defaultCallStatusEventFolder = "http://<MY_PUBLIC_IP>/radisys_demo_app/appointment_reminder/server/events/";

// This is the default URL to delete the json file. The actual HTTP GET request will attach a query parameter "?CallID=xxxxx" to the uri
const g_defaultCallStatusEventDeleteUrl = "http://<MY_PUBLIC_IP>/radisys_demo_app/appointment_reminder/server/delete_event.php";


// These are element ID for the inputs and outputs
const Form = document.getElementById("form");
const Caller = document.getElementById("caller");
const Callee = document.getElementById("callee");
const SubmitButton = document.getElementById("submitButton");
const Output = document.getElementById("output");

// This is the event listener to handle 'submit' event of the form
Form.addEventListener('submit', (e) => {
   e.preventDefault();
   clickSubmitButton();
});

//---------------------------------------------------------------------------------------------------
// This function checks user inputs. Return true if all valid, false otherwise
// --------------------------------------------------------------------------------------------------
function checkInputs() {

   const caller_number = Caller.value.trim();
   const callee_number = Callee.value.trim();
   console.log("from number: ", caller_number);
   console.log("to number: ", callee_number);

   var input1 = false;
   var input2 = false;

   if (caller_number === '') {
        setErrorFor(Caller, "This field cannot be blank");
   } else if (!caller_number.startsWith("+") && callee_number.startsWith("+")) {
        setErrorFor(Caller, "From number must be PSTN #, if To number is PSTN #");
   } else {
        setSuccessFor(Caller);
        input1 = true;
   }

   if (callee_number === '') {
        setErrorFor(Callee, "This field cannot be blank");
   } else if (!caller_number.startsWith("+") && callee_number.startsWith("+")) {
        setErrorFor(Callee, "To number must be virtual #, if From number is virtual #");
   } else {
        setSuccessFor(Callee);
        input2 = true;
   }

   return input1 && input2;
}

function setErrorFor(input, message) {
   const formControl = input.parentElement;
   const small = formControl.querySelector('small');

   small.innerText = message;
   formControl.className = 'form-control error';
}

function setSuccessFor(input) {
   const formControl = input.parentElement;
   formControl.className = 'form-control success';
}

//------------------------------------------------------------------------------------------
// This function is used to validate if it is a valid E164 phone number
//------------------------------------------------------------------------------------------
function validatePhoneForE164(phoneNumber) {
   const regEx = /^\+[1-9]\d{10,14}$/;
   return regEx.test(phoneNumber);
};


// ---------------------------------------------------------------------------------------------------
// This function is called when user clicks the submit button 
// ---------------------------------------------------------------------------------------------------
function clickSubmitButton() {
   console.log("user clicks submit button");

   // Validate user input   
   if (!checkInputs()) {
        console.log("input value validation is failed");
        return;
   }

   // Disable submit button
   disableSubmitButton();

   // Clear the output textarea and restart the log message
   appendOutputText(">> sending request ......\n\n", false);
   appendOutputText("{\n");

   // Create the body message for REST API request
   var body = {
        "From": Caller.value.trim(),
	// To number can be virtual or PSTN, if virtual, then a sip-address need to be provided
        "To": Callee.value.trim().startsWith("+") ? Callee.value.trim(): "sip:"+Callee.value.trim()+"@sipaz1.engageio.com",
        "Type": "voice",
        "Bridge": "none",
        "Url": g_defaultAppUrl,
        "Method": "POST",
        "StatusCallback": g_defaultStatusCallbackUrl,
        "StatusCallbackMethod": "POST",
        "StatusCallbackEvent": "initiated, ringing, answered, completed" 
   };

   // Print the body message in output area 
   for (var key in body) {
        appendOutputText("'" + key + "': '" + body[key] + "'\n");
   }
   appendOutputText("}\n");

   // Send REST API request to EDP (note: this is async call, function returns before REST API response is received)
   makeCall(body);

   return;
}

//---------------------------------------------------------------------------------------------------------------------
// This function is used to send makeCall API request and receive response
//---------------------------------------------------------------------------------------------------------------------
function makeCall(body) {

   // This is your EDP account ID and API key
   const account_id = "<MY_EDP_ACCOUNT_ID>";
   const api_key = "<MY_EDP_API_KEY>";

   console.log("account_id = ", account_id);
   console.log("api_key = ", api_key);
   console.log("body = ", JSON.stringify(body));

   const url = "https://apigateway.engagedigital.ai/api/v1/accounts/" + account_id + "/call";
   console.log("uri = ", url);

   // Build body for content-type = application/x-www-form-urlencoded
   var formBody = [];
   for (var property in body) {
        var encodedKey = encodeURIComponent(property);
        var encodedValue = encodeURIComponent(body[property]);
        formBody.push(encodedKey + "=" + encodedValue);
   }
   formBody = formBody.join("&");

   // Configure the HTTP POST request uri, header and body
   const options = {
        method: "POST",
        headers: {
                "apikey": api_key,
                "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"
        },
        body: formBody
   };

   var response_code = 0;
   var call_id = "";
   var t0 = Date.now();

   // Fetch API is an async call to make HTTP request
   fetch(url, options)
   .then(function(response) {
        var t = Date.now() - t0;
        console.log("time = ", t);

        response_code = response.status;
        appendOutputText("\n>> response is received in " + t + " milliseconds\n\n");
        appendOutputText(response.status + " " + response.statusText + "\n");

        return response.json();
   })
   .then(function(data) {
        appendOutputText("{\n");
        for (var key in data) {
                appendOutputText("'" + key + "': '" + data[key] + "'\n");
                if (key === "CallID")   call_id = data[key];
        }
        appendOutputText("}\n");

        if (response_code != 200) {
                resumeSubmitButton();
        } else {
                appendOutputText("\n>> start to monitor call status ......\n\n");
                setTimeout(checkCallStatus, 1000, call_id);
        }

   })
   .catch(err => console.log(err));

}

//--------------------------------------------------------------------------------------------------------
// For A2P call, call status events are generated by EDP and sent to StatusCallback URL, if specified in
// REST API makeCall() request. In order to display the call status information in the output textarea, we
// have created a PHP file (g_defaultStatusCallbackUrl) on the same web server to receive call status
// events from EDP and save the events into a local file. Then the JS code running on the browser need to
// read this file periodically to get the latest information and update the output textarea accordingly.
//
// This function is used to check the call status event information. If there's any new event is received,
// display this new event in the output textarea. Because the file holding event information is located on
// web server, but JS code is running locally on browser, we need to use Fetch API to get this information
// from the web server. This function is executed repeatly, until last call status event is received,
// or 5 mins max monitor duration has been reached, or user clicks the reset or submit button.
//
// Input parameters:
//
//      call_id: string value, call ID
//      index: 0-n, this is the position of the event in the json file to start with
//
//-------------------------------------------------------------------------------------------------------
function checkCallStatus(call_id, index = 0) {

   const json_file = g_defaultCallStatusEventFolder + call_id + ".json";
   console.log("check call status");
   console.log("fetch from json file: ", json_file);
   console.log("index = ", index);

   var response_code = 0;

   fetch(json_file)
        .then(response => {
                console.log(response.status + " " + response.statusText);
                response_code = response.status;
                if (response_code == 200)       return response.json();
                else                            return response.text();
        })
        .then(data => {
                // If file is not existed yet, just do nothing and check again later
                if (response_code != 200) {
                        setTimeout(checkCallStatus, 1000, call_id, index);
                        return;
                }

                // If file is empty for some reason, just do nothing and check again later
                if (data.length == 0) {
                        setTimeout(checkCallStatus, 1000, call_id, index);
                        return;
                }

                // Display the new events from the file
                for (var i=index; i<data.length; i++) {
                        appendOutputText("[" + unixTimestampToDate(data[i]["timestamp"]) + "]: " + "call is " + data[i]["value"] + "\n");
                }

                // If latest recevied event is completed | no-answer | busy | canceled | failed, then stop the monitoring and delete the json file
                if ((data[data.length-1]["value"] === "completed")
                        || (data[data.length-1]["value"] === "no-answer")
                        || (data[data.length-1]["value"] === "busy")
                        || (data[data.length-1]["value"] === "canceled")
                        || (data[data.length-1]["value"] === "failed")) {

                        var delete_uri = g_defaultCallStatusEventDeleteUrl + "?CallID=" + call_id;
                        console.log("delete_uri = ", delete_uri);

                        // Issue HTTP-GET request to delete the call status file on the server and fetch the user_input in HTTP response_code
                        fetch(delete_uri)
                        .then(function(response) {
                                switch (response.status) {
                                        case 201:
                                                appendOutputText("\n>> Appointment has been confirmed by user\n");
                                                break;
                                        case 202:
                                                appendOutputText("\n>> Appointment has been canceled by user\n");
                                                break;
                                        default:
                                                appendOutputText("\n>> Appointment is still pending for user input\n");
                                                break;

                                }

                                resumeSubmitButton();

                                return response.text();
                        })
                        .then(data => {
                                return data;
                        })
                        .catch(err => console.log(err));

                } else {
                        setTimeout(checkCallStatus, 1000, call_id, i);
                }
        });

}

//---------------------------------------------------------------------------------------------------------------------------
// This function is used to output text to textarea in HTML.
//
//      text:
//              string value, the new text to be displayed
//
//      append:
//              boolean value, true if append the new text, false if display the new text from beginning.
//              optional, if not specified, true is assumed. this parameter must be the last in the lst
//
//---------------------------------------------------------------------------------------------------------------------------
function appendOutputText(text, append = true) {

   const textarea = document.getElementById("output");

   if (append) {
        Output.value += text;
        Output.scrollTop = textarea.scrollHeight;
   } else {
        Output.value = text;
   }
}

//--------------------------------------------------------------------------------------------------------
// Followings are utility functions
//-------------------------------------------------------------------------------------------------------

function disableSubmitButton() {
   SubmitButton.className = "button disabled";
   SubmitButton.innerText = "Your request is being processed, please wait ....";
   SubmitButton.disabled = true;
}

function resumeSubmitButton() {

   appendOutputText("\n>> The current request is completed. You can submit a new request now.\n");

   SubmitButton.className = "button green";
   SubmitButton.innerText = "Submit";
   SubmitButton.disabled = false;

}

function unixTimestampToDate(unix_timestamp) {

   var date = new Date(unix_timestamp * 1000);
   var h = date.getHours();
   var m = date.getMinutes();
   var s = date.getSeconds();

   var ampm = (h >= 12) ? 'pm' : 'am';
   h = h % 12;
   h = h ? h : 12;

   m = checkTime(m);
   s = checkTime(s);

   var t = h + ":" + m + ":" + s + " " + ampm;

   return t;
}

function checkTime(i) {
   if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
   return i;
}

