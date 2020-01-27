<?php

require('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::create(__DIR__,);
$dotenv->load();

/**
 *  NOTE: Needs two bits of data to be submitted each time.
 *
 *  event_name = name of event
 *  event_data = any additional info on the event
 *
 *  OPTIONAL - if you have the user's email, then send the email too
 */
$event_name = $_POST['event_name'];
$event_data = $_POST['event_data'];
$email = $_POST['email'];


if( $event_name !== null || $event_name !== '' ) {

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://trackcmp.net/event');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array(
        'actid' => getenv('AC_EVENT_ID'),
        'key' => getenv('AC_EVENT_KEY'),
        'event' => $event_name,
        'eventdata' => $event_data,
        'visit' => json_encode(array(
            // If you have an email address, assign it here.
            'email' => $email,
        )),
    ));

    $result = curl_exec($curl);
    if ($result !== false) {
        $result = json_decode($result);
        if ($result->success) {
            echo 'Success! ';
        } else {
            echo 'Error! ';
        }

        echo $result->message;
    } else {
        echo 'cURL failed to run: ', curl_error($curl);
    }
}
