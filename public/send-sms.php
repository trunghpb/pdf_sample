<?php
 
require "../vendor/twilio/sdk/Services/Twilio.php";
 
// set your AccountSid and AuthToken from www.twilio.com/user/account
$AccountSid = "AC0514d3dbb80d89fb7da5c191f76ed349";
$AuthToken = "b61b46812624572ee8dfb96b9d4abf81";
 
$client = new Services_Twilio($AccountSid, $AuthToken);
 
try {
    $message = $client->account->messages->create(array(
        "From" => "050 3177 4239",
        "To" => "08091862703",
        "Body" => "Test message!",
    ));
} catch (Services_Twilio_RestException $e) {
    echo $e->getMessage();
}