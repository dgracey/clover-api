<?php

include_once('vendor/autoload.php');

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use phpseclib3\Math\BigInteger;

function get_brand($ccNum) {

    $ccNum = str_replace(array('-', ' '), '', $ccNum);

    if (mb_strlen($ccNum) < 13) {
        return false;
    }

    $cards = array(
        'amex'		=> '/^3[4|7]\\d{13}$/',
        'diners'	=> '/^(?:3(0[0-5]|[68]\\d)\\d{11})|(?:5[1-5]\\d{14})$/',
        'disc'		=> '/^(?:6011|650\\d)\\d{12}$/',
        'jcb'		=> '/^(3\\d{4}|2100|1800)\\d{11}$/',
        'mc'		=> '/^5[1-5]\\d{14}$/',
        'visa'		=> '/^4\\d{12}(\\d{3})?$/'
    );

    foreach ($cards as $card => $value) {
        $regex = $value;

        if (is_string($regex) && preg_match($regex, $ccNum)) {
            return $card;

        }
    }
}

function encrypt_pan($cardnumber){

    global $wpdb;

    $table_name = $wpdb->prefix . "clover";

    $values = $wpdb->get_row("SELECT * FROM ".$table_name." where id=1");

    $data = wp_remote_get( 'https://checkout.clover.com/assets/keys.json' );

    $keys = json_decode($data['body'],true);

    if ($values->development == 1){
        $ta_public_key = bin2hex(base64_decode($keys['TA_PUBLIC_KEY_DEV']));
    }else{
        $ta_public_key = bin2hex(base64_decode($keys['TA_PUBLIC_KEY_PROD']));
    }

    $modulus  = substr($ta_public_key, 0, 512);
    $exponent = substr($ta_public_key, -5);
    $prefix = "00000000";
    $rsa = PublicKeyLoader::load([
        'e' => new BigInteger($exponent, 16),
        'n' => new BigInteger($modulus, 16)
    ]);
    openssl_public_encrypt($prefix.$cardnumber, $encrypted, "$rsa", OPENSSL_PKCS1_OAEP_PADDING);

    $encode = base64_encode($encrypted);

    return ($encode);
}
?>