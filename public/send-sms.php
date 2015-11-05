<?php
$cookie_name = 'limit-sending';
if (isset($_COOKIE[$cookie_name])) {
    $limitSending = $_COOKIE[$cookie_name];
    $limitSending--;
} else {
    $limitSending = 20;
}

setcookie($cookie_name, $limitSending, time() + (86400 * 30), "/"); // 86400 = 1 day
?>


<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (isset($_GET['send'])) {
    require "../vendor/twilio/sdk/Services/Twilio.php";

    $messageContent = isset($_GET['message-content']) ? $_GET['message-content'] : "Test message";
    $phonenumberTo = isset($_GET['phonenumber-to']) ? $_GET['phonenumber-to'] : "";

    //08091862703
    $phonenumberTo = '+81' . substr($phonenumberTo, 1);
    
    // set your AccountSid and AuthToken from www.twilio.com/user/account
    $AccountSid = "AC0514d3dbb80d89fb7da5c191f76ed349";
    $AuthToken = "b61b46812624572ee8dfb96b9d4abf81";

    $client = new Services_Twilio($AccountSid, $AuthToken);

    try {
        $message = $client->account->messages->create(array(
            "From" => "+14807393455",
            "To" => $phonenumberTo, //"+818091862703",
            "Body" => $messageContent,
        ));

        echo "Sent message {$message->sid} to number $phonenumberTo";
    } catch (Services_Twilio_RestException $e) {
        echo $e->getMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
?>
<form action="/send-sms.php" method="GET">
    <span style="float: bottom;">
        Limit for sending is <?= $limitSending ?> messages
    </span>
    <div style="padding-top: 10px">
        Phone Number (TO):
        <input type="text" name="phonenumber-to" value="<?= $phonenumberTo?>">
    </div>
    <div style="padding-top: 10px">
        Message Content:
        <input type="text" name="message-content" value="<?= $messageContent?>">
    </div>
    <input type="hidden" name="send" value="1">
    <div style="padding-top: 10px">
        <input type="submit">
    </div>
</form>