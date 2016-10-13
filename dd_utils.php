<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Utilities library
 * WHMCS version 5.2.x / 5.3.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */

/**
 * Detect WHMCS version.
 * @return integer
 */
function dd_get_whmcs_version()
{
	$q_version = full_query( "SELECT value FROM tblconfiguration WHERE setting = 'version'" );
	
	list( $version ) = mysql_fetch_row( $q_version );
	
	$version_components = explode( '.', $version );
	
	return intval( $version_components[0] );
}
 
/**
 * Get a value from the settings table.
 * @param string $key Key of the value to get
 * @return string
 */
function dd_get( $key )
{
	$settings = full_query( "SELECT `value` FROM `mod_dondominio_settings` WHERE `key`='$key'" );
	
	$result = mysql_fetch_array( $settings, MYSQL_ASSOC );
	
	return $result['value'];
}


/**
 * Set a value in the settings table.
 * @param string $key Key of the value
 * @param string $value Value to save
 * @return boolean
 */
function dd_set( $key, $value )
{
	$update = full_query( "REPLACE INTO `mod_dondominio_settings` (`key`, `value`) VALUES ( '$key', '$value')" );
	
	return $update;
}

/**
 * Get a value from the WHMCS settings table.
 * @param string $key Name of the setting
 * @return string
 */
function dd_get_setting( $key )
{
	$q_setting = full_query( "SELECT value FROM tblconfiguration WHERE setting='" . $key . "'" );
	
	list( $setting ) = mysql_fetch_row( $q_setting );
	
	return $setting;
}

/**
 * Send an Email using mail().
 * @param string $subject Subject of the Email
 * @param string $html Contents of the email
 * @return boolean
 */
function dd_send_email( $subject, $html )
{
	$headers  = "From: DonDominio WHMCS Addon <no-reply@" . php_uname( 'n' ) . ">\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html;r charset=iso-8859-1\r\n";
	
	$notifications = dd_get( "notifications_enabled" );
	$notifications_email = dd_get( "notifications_email" );
	
	if( $notifications == '1' && !empty( $notifications_email )){
		mail( $notifications_email, $subject, $html, $headers );
	}
	
	return true;
}

function dd_getVersion()
{
	$versionFile = __DIR__ . '/version.json';
	
	if( !file_exists( $versionFile )){
		return 'unknown';
	}
	
	$json = @file_get_contents( $versionFile );
	
	if( empty( $json )){
		return 'unknown';
	}
	
	$versionInfo = json_decode( $json, true );
	
	if( !is_array( $versionInfo ) || !array_key_exists( 'version', $versionInfo )){
		return 'unknown';
	}
	
	return $versionInfo['version'];
}

/**
 * Initialize DonDominio API Client.
 * @return DonDominioAPI
 */
function dd_init()
{
	if( !strlen( dd_get( 'api_username' ))){
		header( "Location: addonmodules.php?module=dondominio&action=settings" );
		exit();
	}
	
	$options = array(
		'apiuser' => dd_get( 'api_username' ),
		'apipasswd' => base64_decode( dd_get( 'api_password' )),
		'autoValidate' => false,
		'versionCheck' => true,
		'response' => array(
			'throwExceptions' => true
		),
		'userAgent' => array(
			'DomainManagementAddonForWHMCS' => dd_getVersion()
		)
	);
	
	$dondominio = new DonDominioAPI( $options );
	
	return $dondominio;
}

/**
 * Initial sync with the database to create the domain pricing cache.
 * @return boolean
 */
function dd_initial_sync()
{
	full_query( "DELETE FROM mod_dondominio_pricing" );
	
	$dondominio = dd_init();
	
	$prices_array = array();
	
	try{
		$i = 1;
		$total = 0;
		
		do{
			$prices = $dondominio->account_zones( array(
				'pageLength' => 100,
				'page' => $i
			));
			
			$prices_array = array_merge( $prices_array, $prices->get( "zones" ));
			
			$total = $prices->get( "queryInfo" )['total'];
			
			$i++;
		}while( $total > count( $prices_array ));
	}catch(DonDominioAPI_Error $e){
		return false;
	}
	
	foreach( $prices_array as $data ){
		if(array_key_exists( 'create', $data )){
			$create_price = "'" . $data['create']['price'] . "'";
			$create_range = "'" . $data['create']['years'] . "'";
		}else{
			$create_price = 'NULL';
			$create_range = 'NULL';
		}
		
		if(array_key_exists( 'transfer', $data )){
			$transfer_price = "'" . $data['transfer']['price'] . "'";
			$transfer_range = "'" . $data['transfer']['years'] . "'";
		}else{
			$transfer_price = 'NULL';
			$transfer_range = 'NULL';
		}
		
		if(array_key_exists( 'renew', $data )){
			$renew_price = "'" . $data['renew']['price'] . "'";
			$renew_range = "'" . $data['renew']['years'] . "'";
		}else{
			$renew_price = 'NULL';
			$renew_range = 'NULL';
		}
		
		$authcode_required = ( $data['authcodereq'] ) ? 1 : 0;
		
		$s_insert = "
			INSERT INTO mod_dondominio_pricing(
				tld,
				register_price,
				transfer_price,
				renew_price,
				register_range,
				transfer_range,
				renew_range,
				old_register_price,
				old_transfer_price,
				old_renew_price,
				authcode_required,
				last_update
			) VALUES (
				'." . $data['tld'] . "',
				$create_price,
				$transfer_price,
				$renew_price,
				$create_range,
				$transfer_range,
				$renew_range,
				NULL,
				NULL,
				NULL,
				$authcode_required,
				NOW()
			)
		";
		
		$q_insert = full_query($s_insert);
	}
	
	return true;
}

/**
 * Create currency entries for domain pricing on missing currencies.
 * @return boolean
 */
function dd_create_currency_placeholders()
{
	$tlds = full_query( "SELECT id FROM tbldomainpricing WHERE autoreg = 'dondominio'" );
	
	while( list( $tld_id ) = mysql_fetch_row( $tlds )){
		$currencies = full_query( "SELECT id FROM tblcurrencies WHERE NOT code = 'EUR'" );
		
		while( list( $currency_id ) = mysql_fetch_row( $currencies )){
			$create = full_query( "SELECT * FROM tblpricing WHERE relid = $tld_id AND currency = $currency_id AND type='domainregister'" );
			$transfer = full_query( "SELECT * FROM tblpricing WHERE relid = $tld_id AND currency = $currency_id AND type='domaintransfer'" );
			$renew = full_query( "SELECT * FROM tblpricing WHERE relid = $tld_id AND currency = $currency_id AND type='domainrenew'" );
			
			if( mysql_num_rows( $create ) == 0 ){
				dd_insert_currency( 'domainregister', $tld_id, $currency_id );
			}
			
			if( mysql_num_rows( $transfer ) == 0 ){
				dd_insert_currency( 'domaintransfer', $tld_id, $currency_id );
			}
			
			if( mysql_num_rows( $renew ) == 0 ){
				dd_insert_currency( 'domainrenew', $tld_id, $currency_id );
			}
		}
	}
	
	return true;
}

/**
 * Create a new currency entry for a TLD.
 * @param string $type Type of price (register, renew, transfer)
 * @param integer $tld_id Identifier of the TLD
 * @param integer $currency_id Identifier of the currency
 * @return boolean
 */
function dd_insert_currency($type, $tld_id, $currency_id)
{
	full_query("
		INSERT INTO tblpricing
		(
			type,
			relid,
			currency,
			msetupfee,
			qsetupfee,
			ssetupfee,
			asetupfee,
			bsetupfee,
			tsetupfee,
			monthly,
			quarterly,
			semiannually,
			annually,
			biennially,
			triennially
		) VALUES (
			'$type',
			$tld_id,
			$currency_id,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0
		)
	");
	
	return true;
}

/**
 * Apply rate conversion to all currencies configured for all TLDs.
 * @return boolean
 */
function dd_update_currencies()
{
	$s_base = "
		SELECT
			type,
			relid,
			msetupfee,
			qsetupfee,
			ssetupfee,
			asetupfee,
			bsetupfee,
			monthly,
			quarterly,
			semiannually,
			annually,
			biennially
		FROM tblpricing
		WHERE
			(type = 'domainregister' OR type = 'domaintransfer' OR type = 'domainrenew')
			AND relid IN (SELECT id FROM tbldomainpricing WHERE autoreg = 'dondominio')
			AND currency = (SELECT id FROM tblcurrencies WHERE code = 'EUR')
		ORDER BY relid ASC
	";
	
	$base = full_query($s_base);
	
	if(mysql_num_rows($base)){
		while(list($type, $relid, $a, $b, $c, $d, $e, $f, $g, $h, $i, $j) = mysql_fetch_row($base)){
			$s_update = "
				UPDATE tblpricing
				SET
			";
			
			//msetupfee - 1Y
			if($a >= 0){
				$s_update .= "
					msetupfee = (($a / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
				";
			}else{
				$s_update .= "
					msetupfee = -1,
				";
			}
			
			//qsetupfee - 2Y
			if($b >= 0){
				$s_update .= "
					qsetupfee = (($b / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
				";
			}else{
				$s_update .= "
					qsetupfee = -1,
				";
			}
			
			//ssetupfee - 3Y
			if($c >= 0){
				$s_update .= "
					ssetupfee = (($c / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
				";
			}else{
				$s_update .= "
					ssetupfee = -1,
				";
			}
			
			//asetupfee - 4Y
			if($d >= 0){
				$s_update .= "
					asetupfee = (($d / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
				";
			}else{
				$s_update .= "
					asetupfee = -1,
				";
			}
			
			//bsetupfee - 5Y
			if($e >= 0){
				$s_update .= "
					bsetupfee = (($e / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
				";
			}else{
				$s_update .= "
					bsetupfee = -1,
				";
			}
			
			//monthly - 6Y
			if($f >= 0){
				$s_update .= "
					monthly = (($f / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
					";
			}else{
				$s_update .= "
					monthly = -1,
				";
			}
			
			//quarterly - 7Y
			if($g >= 0){
				$s_update .= "
					quarterly = (($g / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
				";
			}else{
				$s_update .= "
					quarterly = -1,
				";
			}
			
			//semianually - 8Y
			if($h >= 0){
				$s_update .= "
					semiannually = (($h / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
				";
			}else{
				$s_update .= "
					semiannually = -1,
				";
			}
			
			//annually - 9Y
			if($i >= 0){
				$s_update .= "
					annually = (($i / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency)),
				";
			}else{
				$s_update .= "
					annually = -1,
				";
			}
			
			//biennially - 10Y
			if($j >= 0){
				$s_update .= "
					biennially = (($j / (SELECT rate FROM tblcurrencies WHERE code = 'EUR')) * (SELECT rate FROM tblcurrencies WHERE id = currency))
				";
			}else{
				$s_update .= "
					biennially = -1
				";
			}
			
			$s_update .= "
				WHERE
					relid = $relid
					AND type = '$type'
					AND NOT currency = (SELECT id FROM tblcurrencies WHERE code = 'EUR')
			";
			
			$update = full_query($s_update);
		}
	}
	
	return true;
}

/**
 * Update prices for already registered domains accordingly.
 */
function dd_update_domain_prices()
{
	$q_domains = full_query( "SELECT id, domain FROM tbldomains" );
	
	while( list( $domain_id, $domain_name ) = mysql_fetch_row( $q_domains )){
		$cname_components = explode( '.', $domain_name );
		$last_component = count( $cname_components ) - 1;
		$tld = $cname_components[$last_component];
		
		full_query( "UPDATE tbldomains SET recurringamount = (SELECT msetupfee FROM tblpricing WHERE type = 'domainrenew' AND currency = (SELECT id FROM tblcurrencies WHERE code = 'EUR') AND relid = (SELECT id FROM tbldomainpricing WHERE extension = '." . $tld . "')) WHERE id = $domain_id" );
	}
}

?>
