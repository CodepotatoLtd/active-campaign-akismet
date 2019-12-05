<?php

require('vendor/autoload.php');
require('./includes/SpamKilla.php');

use Respect\Validation\Validator as v;

$dotenv = Dotenv\Dotenv::create(__DIR__,);
$dotenv->load();

$ac = new ActiveCampaign(getenv('API_URL'), getenv('API_KEY'));

if (!(int) $ac->credentials_test()) {
    throw new Exception('Credentials incorrect', '403');
}

$email = $_POST['email'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$company_name = $_POST['company_name'];
$phone = $_POST['phone'];

$spamKilla = new SpamKilla($first_name.' '.$last_name, $email, '');
$validated = $spamKilla->SendTheBoysRound();

if ($validated) {

    if (v::email()->validate($email)) {

        // check if a firm exists with that exact name

        $orgs = $ac->api('organizations');

        $org = null;

        foreach ($orgs as $organisation) {
            if (strtolower($org['name']) === strtolower($company_name)) {
                $org = $organisation;
                break;
            }
        }

        $contact = array(
            'email' => $email,
            'firstName' => $first_name,
            'lastName' => $last_name,
            'phone' => $phone,
        );

        if ($org !== null) {
            $contact['orgid'] = $org['id'];
        }

        $contact_sync = $ac->api('contact/sync', $contact);

        if (!(int) $contact_sync->success) {
            // request failed
            echo '<p>Syncing contact failed. Error returned: '.$contact_sync->error.'</p>';
            exit();
        }

    }
}