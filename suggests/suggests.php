<?php

/**
 * Generate suggests for a word using the DonDominio API.
 *
 * @author DonDominio <http://www.dondominio.com>
 * @link https://github.com/dondominio/whmcs-suggests-addon
 * @package DonDominioWHMCSSuggestionsAddon
 * @license GNU LESSER GENERAL PUBLIC LICENSE <https://raw.githubusercontent.com/dondominio/whmcs-addon/master/LICENSE>
 */

require_once( __DIR__ . '/../../../../init.php' );
require_once( __DIR__ . '/../lib/sdk/DonDominioAPI.php' );

if( !array_key_exists( 'uid', $_SESSION ) || !$_SESSION['uid'] ){
	$captcha = md5( $_REQUEST['captcha'] );
	
	if( $captcha != $_SESSION['captchaValue'] ){
		die( json_encode( array( 'error' => '1', 'reason' => 'captcha_failed' )));
	}
}

$dd = new \DonDominioAPI( array(
	'apiuser' => dd_get( 'api_username' ),
	'apipasswd' => base64_decode( dd_get( 'api_password' )),
	'endpoint' => 'https://simple-api.dondominio.net',
	'userAgent' => array(
		'DomainSuggestionsAddonForWHMCS' => dd_getVersion(),
		'WHMCS' => dd_get_whmcs_version()
	)
));

try{
	$suggestions = $dd->tool_domainSuggests( array(
		'query' => $_REQUEST['text'],
		'language' => ddsuggests_get( 'language' ),
		'tlds' => ddsuggests_get( 'tlds' )
	));
}catch( \DonDominioAPI_Error $e ){
	die( $e->getMessage());
}

$results = $suggestions->get( 'suggests' );

$suggestions_array = array();

if( !array_key_exists( 'currency', $_SESSION ) || !is_numeric( $_SESSION['currency'] )){
	$_SESSION['currency'] = 1;
}

foreach( $results as $sld=>$result ){
	foreach( $result as $tld=>$available ){
		if( $available ){
			$price = full_query("
				SELECT
					msetupfee AS 1Y,
					qsetupfee AS 2Y,
					ssetupfee AS 3Y,
					asetupfee AS 4Y,
					bsetupfee AS 5Y,
					monthly AS 6Y,
					quarterly AS 7Y,
					semiannually AS 8Y,
					annually AS 9Y,
					biennially AS 10Y,
					( SELECT prefix FROM tblcurrencies WHERE id = '" . $_SESSION['currency'] . "' ) AS currency_prefix,
					( SELECT suffix FROM tblcurrencies WHERE id = '" . $_SESSION['currency'] . "' ) AS currency_suffix
				FROM tblpricing
				WHERE
					type = 'domainregister'
					AND currency = '" . $_SESSION['currency'] . "'
					AND relid = ( SELECT id FROM tbldomainpricing WHERE extension = '.$tld' )
			");
			
			if( mysql_num_rows( $price ) > 0 ){
				$y = mysql_fetch_array( $price, MYSQL_ASSOC );
				
				$suggestions_array[] = array(
					'domain' => $sld . '.' . $tld,
					'price' => $y,
					'status' => 'available'
				);
			}
		}
	}
}

die( json_encode( $suggestions_array ));

?>