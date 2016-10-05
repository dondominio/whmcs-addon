<?php

require_once( __DIR__ . '/../../../../init.php' );
require_once( __DIR__ . '/../dd_utils.php' );
require_once( __DIR__ . "/../lib/sdk/DonDominioAPI.php" );

$options = array(
	'endpoint' => 'https://simple-api.dondominio.net',
	'apiuser' => dd_get( 'api_username' ),
	'apipasswd' => base64_decode( dd_get( 'api_password' )),
	'autoValidate' => true,
	'versionCheck' => true,
	'response' => array(
		'throwExceptions' => true
	),
	'userAgent' => array(
		'WhoisProxyAddonForWHMCS' => '1.0'
	)
);

//Checking allowed IPs
$ip = dd_get( 'whois_ip' );

$ip_array = explode( ';', $ip );

$current_ip = $_SERVER['REMOTE_ADDR'];

if( !in_array( $current_ip, $ip_array )){
	die( "Error: $current_ip not allowed to access this script." );
}

try{
	$dondominio = new DonDominioAPI( $options );
}catch( \DonDominioAPI_Error $e ){
	die( $e->getMessage());
}

try{
	$whois = $dondominio->domain_check( $_REQUEST['domain'] );
}catch( \DonDominioAPI_Error $e ){
	die( $e->getMessage());
}

$domain = $whois->get( "domains" )[0];

if( $domain['available'] ){
	die( "DDAVAILABLE" );
}else{
	die( "Not Available" );
}

?>
