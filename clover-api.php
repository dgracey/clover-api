<?php

date_default_timezone_set("UTC");

/*
 * Plugin Name:       clover-api
 * Description:       Custom plugin used to create charges to Clover. For any questions, please send me an email (anthonygonzalez8102@gmail.com).
 * Author:            Anthony Gonzalez
*/

include('settings.php');
include ('card.php');

require __DIR__ . '/vendor/autoload.php';

function start_transaction(){
  if ( $_SERVER['REQUEST_METHOD'] === 'POST'){

    global $wpdb;

    $amount = ($_POST['amount'] * 100);

    $email = $_POST["email"];

    $invoice = $_POST['invoice'];

    $date = explode("/", $_POST["exp"]);

    $apiKey = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . "clover"." where id=1");

    $card = array (
      'name' => sanitize_text_field($_POST['cardholder_name']),
      'number' => sanitize_text_field($_POST['card_number']),
      'expiry_month' => sanitize_text_field($date[0]),
      'expiry_year' => sanitize_text_field($date[1]),
      'cvv' => sanitize_text_field($_POST['cvv']),
      'address_1' => sanitize_text_field($_POST['address_1']),
      'address_2' => sanitize_text_field($_POST['address_2']),
      'city' => sanitize_text_field($_POST['city']),
      'state' => sanitize_text_field($_POST['state']),
      'zip' => sanitize_text_field($_POST['zip']),
      'last4' => substr(sanitize_text_field($_POST['card_number']), -4, 4),
      'first6' => substr(sanitize_text_field($_POST['card_number']), 0, 6)
    );

  }

  create_token($card, $apiKey->apiKey, $amount, $invoice, $email);
}

function create_token($card, $apiKey, $amount, $invoice, $email){

  $curl = curl_init();

  //trigger exception in a "try" block
  try {
    $payload = json_encode([
      'card' => [
          'brand' => get_brand($card['number']),
          'encrypted_pan' => encrypt_pan($card['number']),
          'exp_month' => $card['expiry_month'],
          'exp_year' => $card['expiry_year'],
          'cvv' => $card['cvv'],
          'last4' => $card['last4'],
          'first6' => $card['first6'],
          'name' => $card['name'],
          'address_line1' => $card["address_1"],
          'address_line2' => $card["address_2"],
          'address_city' => $card["city"],
          'address_state' => $card["state"],
          'address_zip' => $card["zip"]
      ]
      ], JSON_UNESCAPED_UNICODE);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('[Clover] JSON encoding failed: ' . json_last_error_msg());
    return;
}
  }catch(Exception $e) {
    json_encode([
      'card' => [
          'brand' => get_brand($card['number']),
          'number' => $card['number'],
          'exp_month' => $card['expiry_month'],
          'exp_year' => $card['expiry_year'],
          'cvv' => $card['cvv'],
          'last4' => $card['last4'],
          'first6' => $card['first6'],
          'name' => $card['name'],
          'address_line1' => $card["address_1"],
          'address_line2' => $card["address_2"],
          'address_city' => $card["city"],
          'address_state' => $card["state"],
          'address_zip' => $card["zip"]
      ]
      ]);
  };

  curl_setopt_array($curl, [
    CURLOPT_URL => getCloverURL('token') . 'v1/tokens',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
      "accept: application/json",
      "apikey: ". "$apiKey",
      "content-type: application/json"
    ],
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    $card_token = json_decode($response, true);

    error_log('[Clover] Token response received. ID: ' . ($card_token["id"] ?? 'none') . ' Status: ' . ($card_token["object"] ?? 'unknown'));

    if (empty($card_token["id"])) {
        error_log('[Clover] Token creation failed. Response: ' . $response);
        error_log('[Clover] API Key used: ' . substr($apiKey, 0, 6) . '...');
        return;
    }

    create_charge($card_token["id"], $amount, $invoice, $email);
  }
}

function create_charge($token, $amount, $invoice, $email){

  global $wpdb;

  $values = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . "clover"." where id=1");

  $ip = get_client_ip();

  $curl = curl_init();

  curl_setopt_array($curl, [
    CURLOPT_URL => getCloverURL('scl'). 'v1/charges',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
      'ecomind' => 'ecom',
      'metadata' => [
          'existingDebtIndicator' => false,
          'customerAddress' => 'test to see'
      ],
      'amount' => $amount,
      'currency' => 'usd',
      'source' => $token,
      'external_reference_id' => $invoice
    ]),
    CURLOPT_HTTPHEADER => [
      "accept: application/json",
      "authorization: Bearer ". $values->apiToken,
      "content-type: application/json",
      "x-forwarded-for: ".$ip
    ],
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  $json = json_decode($response, true);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {

    if ($json["status"] == "succeeded"){

      $subtotal = $json["amount"];
      $fees = 0;

      if (array_key_exists("additional_charges", $json)){
        $fees = $json["additional_charges"][0]["amount"];
      }

      $total = ($subtotal + $fees);

      $invoice_res = strval($json["external_reference_id"]);
      $card_res = strval($json["source"]["brand"] . " " . strval($json["source"]["last4"]));
      $paymentID_res = strval($json["id"]);
      $authID_res = strval($json['auth_code']);
      $refID_res = strval($json['ref_num']);

      $subtotal_res = $subtotal / 100;
      $fees_res = $fees;
      $total_res = $total / 100;

      if ($fees_res != 0){
        $fees_res = $fees / 100;
      }

      $subtotal_res = number_format((float)$subtotal_res, 2, '.', '');
      $fees_res = number_format((float)$fees_res, 2, '.', '');
      $total_res = number_format((float)$total_res, 2, '.', '');
      
      send_reciept($invoice_res, $total_res, $card_res, $paymentID_res, $authID_res, $refID_res, $subtotal_res, $fees_res, $email);

      echo $response;
      die();
    }else{
      echo $response;
      die();
    }
  }
}

function get_client_ip() {
  if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
    return  $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
    return $_SERVER['REMOTE_ADDR'];
  } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
    return $_SERVER['HTTP_CLIENT_IP'];
  }

  return '';
}

function send_reciept($invoice_res, $total_res, $card_res, $paymentID_res, $authID_res, $refID_res, $subtotal_res, $fees_res, $email){

  global $wpdb;

  $values = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . "clover"." where id=1");

  ob_start();

  echo '<div style="font-family: Arial, sans-serif; background-color: #f9f9f9; margin: 0; padding: 20px;">';
  echo '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="max-width: 600px; background-color: #ffffff; border-radius: 8px;">';
  echo '<tr>';
  echo '<td style="padding: 20px;">';
  echo '<h2 style="text-align: center; color: #333333;">Payment Successful</h2>';
  echo '<div style="text-align: center; color: #333333;">';
  echo '<p style="margin: 5px 0;">Midwest Tile</p>';
  echo '<p style="margin: 5px 0;">200 W Industrial Lake Drive, Lincoln, NE 68528</p>';
  echo '<p style="margin: 5px 0;">402-476-2542</p>';
  echo '</div>';
  
  echo '<div style="text-align: center; margin: 20px 0;">';
  echo '<h5 style="margin: 0;">Invoice Number</h5>';
  echo '<h5 style="margin: 0;">' . $invoice_res . '</h5>';
  echo '</div>';
  
  echo '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 20px 0;">';
  echo '<tr>';
  echo '<td style="padding: 10px; border: 1px solid #ddd;">Subtotal:</td>';
  echo '<td style="padding: 10px; border: 1px solid #ddd; text-align: right;">$' . $subtotal_res . '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td style="padding: 10px; border: 1px solid #ddd;">Fees:</td>';
  echo '<td style="padding: 10px; border: 1px solid #ddd; text-align: right;">$' . $fees_res . '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td style="padding: 10px; border-top: 2px solid #ddd; font-weight: bold; font-size: 18px;">Total:</td>';
  echo '<td style="padding: 10px; border-top: 2px solid #ddd; text-align: right; font-weight: bold; font-size: 18px;">$' . $total_res . '</td>';
  echo '</tr>';
  echo '</table>';
  
  echo '<div style="margin: 20px 0; text-align: center; border-top: 2px solid #ddd; padding-top: 10px;">';
  echo '<h4 style="margin: 10px 0;">Payment Details</h4>';
  echo '<p style="margin: 5px 0;">Card: ' . $card_res . '</p>';
  echo '<p style="margin: 5px 0;">Payment ID: ' . $paymentID_res . '</p>';
  echo '<p style="margin: 5px 0;">Auth ID: ' . $authID_res . '</p>';
  echo '<p style="margin: 5px 0;">Reference ID: ' . $refID_res . '</p>';
  echo '</div>';
  
  echo '<div style="text-align: center; font-size: 12px; color: #777777; border-top: 2px solid #ddd; padding-top: 20px; margin-top: 20px;">';
  echo '<p>Midwest Tile</p>';
  echo '</div>';
  
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';

  $html = ob_get_contents();
  ob_end_clean();

  $to = $email;
  $subject = 'Midwest Tile Transaction Reciept';
  $headers = array('Content-Type: text/html; charset=UTF-8', 'From: Midwest Tile <'.$values->email.'>');

  wp_mail($to, $subject, $html, $headers);
}

function initiate_db () {
  global $wpdb;

  $table_name = $wpdb->prefix . "clover";

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    apiKey varchar(120) DEFAULT '' NOT NULL,
    apiToken varchar(120) DEFAULT '' NOT NULL,
    email varchar(100) DEFAULT '' NOT NULL,
    development bit NOT NULL,
    PRIMARY KEY  (id)
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
  
  $wpdb->query("INSERT INTO ".$table_name." set apiKey='',apiToken='',email='',development=1");
}

function remove_db(){
  global $wpdb;

  $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}clover" );
}

function plugin_activate(){
  initiate_db();
}

function plugin_deactivate(){
  remove_db();
}

function plugin_uninstall(){
  remove_db();
}

register_activation_hook( __FILE__, 'plugin_activate' );
register_deactivation_hook(__FILE__, 'plugin_deactivate' );
register_uninstall_hook(__FILE__,'plugin_uninstall');

add_filter( 'page_template', 'fw_reserve_page_template' );

function fw_reserve_page_template( $page_template )
{
    if ( is_page( 'Clover Payment Page' ) ) {

        $page_template = dirname( __FILE__ ) . '/templates/payment-template.php';
    }
    return $page_template;
}

add_action("wp_ajax_nopriv_start_transaction", "start_transaction");
add_action("wp_ajax_get_start_transaction", "start_transaction");
add_action( 'wp_ajax_start_transaction', "start_transaction");

?>