<?php

global $wpdb;

$table_name = $wpdb->prefix . "clover";

$redirect = get_site_url() . '/wp-json/clover-api/v1/oauth-start';

//BASIC FUNCTIONS

function getCloverURL($type){

    global $wpdb, $table_name;

    $values = $wpdb->get_row("SELECT * FROM ".$table_name." where id=1");

    if ($values->development == 1){
        
        $uris = array(
            "clover" => "https://sandbox.dev.clover.com/",
            "scl" => "https://scl-sandbox.dev.clover.com/",
            "token" => "https://token-sandbox.dev.clover.com/"
        );

        return ($uris[$type]);
    }else{
        $uris = array(
            "clover" => "https://api.clover.com/",
            "scl" => "https://scl.clover.com/",
            "token" => "https://token.clover.com/"
        );

        return ($uris[$type]);
    }
}

function apiCall($token){

    $curl = curl_init();

    curl_setopt_array($curl, [
    CURLOPT_URL => getCloverURL("scl") . 'pakms/apikey',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Bearer " . $token
    ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {
    return $response;
    }
}

function authenticate($token){

    global $wpdb, $table_name, $redirect, $clover_uri;

    $apiResponse = json_decode(apiCall($token), true);

    $wpdb->query("UPDATE ".$table_name." set apiKey='".$apiResponse["apiAccessKey"]."' where id=1");

    header("Location: ". get_admin_url() . "admin.php?page=clover");
}

function clover_request(WP_REST_Request $request){

    global $wpdb, $table_name, $redirect, $clover_uri;

    $values = $wpdb->get_row("SELECT * FROM ".$table_name." where id=1");

    authenticate($values->apiToken);
}
  
function register_routes(){
    register_rest_route( 'clover-api/v1', '/oauth-start', array(
      'methods' => 'GET',
      'callback' => 'clover_request',
      'permission_callback' => '__return_true',
    ) );
    register_rest_route( 'clover-api/v1', '/pay', array(
        'methods' => 'POST',
        'callback' => 'start_transaction',
        'permission_callback' => '__return_true',
      ) );
}

add_action( 'rest_api_init', 'register_routes');


?>
