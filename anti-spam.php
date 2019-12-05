<?php

require('./includes/SpamKilla.php');

$name = $_POST['ac_name'];
$email = $_POST['ac_email'];
$message = $_POST['ac_msg'];

$sk = new SpamKilla($name, $email, $message);
$valid = $sk->SendTheBoysRound();

if( !$valid ){
    throw new Exception('Invalid spammy mc spammyton');
}