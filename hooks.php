<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: Autoupdater hook
 * WHMCS version 5.2.x / 5.3.x / 6.x / 7.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */
 
require_once( "lib/sdk/DonDominioAPI.php" );
require_once( "dd_utils.php" );

add_hook( 'ClientAreaFooterOutput', 1, function( $vars )
{
	if( dd_get( 'suggests_enabled' ) != '1' ){
		return false;
	}
	
	$current_language = $vars['language'];
	
	$lang_file = __DIR__ . '/lang/' . $current_language . '.php';
	
	if( file_exists( $lang_file )){
		include( $lang_file );
	}else{
		require( __DIR__ . '/lang/english.php' );
	}
	
	$LANG = $_ADDONLANG;
	
	$currency = ( array_key_exists( 'currency', $_GET )) ? $_GET['currency'] : '1';
	
	$html = "
	<input type=\"hidden\" name=\"currency\" id=\"currency\" value=\"" . $currency . "\" />
	
	<script id=\"suggestions_template\" type=\"text/html\">
	";
	
	/*
	 * Modify the `template.html` file to match your templates as you need.
	 */
	$html .= include( __DIR__ . '/suggests/template.php' );
	
	$html .= "
    </script>
	
	<script src='modules/addons/dondominio/suggests/suggests.js'></script>
	";
	
	return $html;
});

/**
 * Hook to sync TLDs and prices.
 * Runs alongside the WHMCS Daily Cron.
 */
function hook_daily_cron_job()
{
	$dondominio = dd_init();
	
	$prices_array = array();
	
	try{
		$i = 1;
		$total = 0;
		
		do{
			$prices = $dondominio->account_zones(array(
				'pageLength' => 100,
				'page' => $i
			));
			
			$prices_array = array_merge($prices_array, $prices->get("zones"));
			
			$total = $prices->get("queryInfo")['total'];
			
			$i++;
		}while($total > count($prices_array));
	}catch(DonDominioAPI_Error $e){
		echo $e->getMessage();
	}
	
	$new_tlds = dd_get( "notifications_new_tlds" );
	$prices = dd_get( "notifications_prices" );
	
	foreach($prices_array as $data){
		$check = full_query( "SELECT id, register_price, transfer_price, renew_price FROM mod_dondominio_pricing WHERE tld = '." . $data['tld'] . "' ORDER BY tld ASC" );
		
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
		
		if(mysql_num_rows($check) == 0){
			//Not found; create & notify
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
					authcode_req,
					last_update
				) VALUES (
					'." . $data['tld'] . "',
					" . $create_price . ",
					" . $transfer_price . ",
					" . $renew_price . ",
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
			
			$q_insert = full_query( $s_insert );
			
			$added_to_db[$data['tld']] = array(
				'register_price' => $create_price,
				'transfer_price' => $transfer_price,
				'renew_price' => $renew_price
			);
		}else{
			$old_data = mysql_fetch_array($check, MYSQL_ASSOC);
			
			$old_register_price = ( empty($old_data['register_price'] )) ? 'NULL' : "'" . $old_data['register_price'] . "'";
			$old_transfer_price = ( empty($old_data['transfer_price'] )) ? 'NULL' : "'" . $old_data['transfer_price'] . "'";
			$old_renew_price = ( empty($old_data['renew_price'] )) ? 'NULL' : "'" . $old_data['renew_price'] . "'";
			
			//Updating
			$s_update = "
				UPDATE mod_dondominio_pricing
				SET
					register_price = " . $create_price . ",
					transfer_price = " . $transfer_price . ",
					renew_price = " . $renew_price . ",
					old_register_price = " . $old_register_price . ",
					old_transfer_price = " . $old_transfer_price . ",
					old_renew_price = " . $old_renew_price . ",
					last_update = NOW()
				WHERE
					tld = '." . $data['tld'] . "'
			";
			
			$q_update = full_query( $s_update );
			
			if( array_key_exists( 'create', $data )){
				$create_price = (int) $data['create']['price'];
				$create_range = (int) $data['create']['years'];
			}else{
				$create_price = 0;
				$create_range = 0;
			}
			
			if( array_key_exists( 'transfer', $data )){
				$transfer_price = (int) $data['transfer']['price'];
				$transfer_range = (int) $data['transfer']['years'];
			}else{
				$transfer_price = 0;
				$transfer_range = 0;
			}
			
			if( array_key_exists( 'renew', $data )){
				$renew_price = (int) $data['renew']['price'];
				$renew_range = (int) $data['renew']['years'];
			}else{
				$renew_price = 0;
				$renew_range = 0;
			}
			
			if(
				$old_data['register_price'] != $create_price ||
				$old_data['transfer_price'] != $transfer_price ||
				$old_data['renew_price'] != $renew_price
			){
				$register_difference = $old_data['register_price'] - $create_price;
				$transfer_difference = $old_data['transfer_price'] - $transfer_price;
				$renew_difference = $old_data['renew_price'] - $renew_price;
				
				if( $old_data['register_price'] < $create_price ){
					$register_difference = '+ ' . number_format(($register_difference * -1), 2, '.', ',');
				}else{
					$register_difference = '- ' . number_format($register_difference, 2, '.', ',');
				}
				
				if( $old_data['transfer_price'] < $transfer_price ){
					$transfer_difference = '+ ' . number_format(($transfer_difference * -1), 2, '.', ',');
				}else{
					$transfer_difference = '- ' . number_format($transfer_difference, 2, '.', ',');
				}
				
				if( $old_data['renew_price'] < $renew_price ){
					$renew_difference = '+ ' . number_format(($renew_difference * -1), 2, '.', ',');
				}else{
					$renew_difference = '- ' . number_format($renew_difference, 2, '.', ',');
				}
				
				if( dd_add_tld_to_list( $data['tld'])){
					$prices_updated[$data['tld']] = array(
						'register_price' => $create_price,
						'transfer_price' => $transfer_price,
						'renew_price' => $renew_price,
						'old_register_price' => $old_data['register_price'],
						'old_transfer_price' => $old_data['transfer_price'],
						'old_renew_price' => $old_data['renew_price'],
						'register_difference' => $register_difference,
						'transfer_difference' => $transfer_difference,
						'renew_difference' => $renew_difference
					);
				}
			}
		}
	}
	
	if($new_tlds == '1' && count($added_to_db) > 0){
		dd_send_tlds_email($added_to_db);
	}
	
	if($prices == '1' && count($prices_updated) > 0){
		dd_send_prices_email($prices_updated);
	}
	
	//Updating prices on WHMCS database
	if(dd_get("prices_autoupdate") == '1'){
		dd_update_prices();
	}
}

/**
 * Add a TLD to the list considering watchlist preferences.
 * @param string $tld TLD to add
 * @param array $tld_list Array containing the TLDs
 * @return array
 */
function dd_add_tld_to_list($tld)
{
	$watchlist_mode = dd_get("watchlist_mode");
	
	if($watchlist_mode == "disable"){
		return true;
	}
	
	$watchlist_items = full_query("SELECT tld FROM mod_dondominio_watchlist WHERE tld = '.". $tld . "'");
	
	if(
		(mysql_num_rows($watchlist_items) == 1 && $watchlist_mode == 'watch') ||
		(mysql_num_rows($watchlist_items) == 0 && $watchlist_mode == 'ignore')
	){
		return true;
	}
	
	return false;
}


/**
 * Send Email with new TLDs added to the cache.
 * @param array $new_tlds Associative array containing new TLDs and their prices
 * @return boolean
 */
function dd_send_tlds_email($new_tlds)
{
	$html = "
		<!doctype>
		<html>
			<head>
				<style>
				<!--
				TABLE {
					border: 0px solid black;
					border-width: 0px 0px 1px 0px;
				}
				
				TD {
					border: 1px solid black;
					border-width: 1px 0px 0px 1px;
					text-align: center;
				}
				
				TH {
					border: 1px solid black;
					border-width: 1px 0px 0px 1px;
					text-align: center;
					font-weight: bold;
					background-color: #d0d0d0;
				}
				
				.right {
					border-width: 1px 1px 0px 1px;
				}
				-->
				</style>
			</head>
			<body>
				<h2>New TLDs available</h2>
				
				<p>The following TLDs have been added to DonDominio and are now available to register:</p>
				<p>
				
				<table border='1' width='100%' cellspacing='0' cellpadding='5'>
				<thead>
					<tr>
						<th>TLD</th>
						<th>Registration</th>
						<th>Transfer</th>
						<th class='right'>Renewal</th>
					</tr>
				</thead>
				<tbody>
	";
	
	foreach($new_tlds as $tld=>$data){
		$html .= "
					<tr>
						<td>$tld</td>
						<td>" . $data['register_price'] . "&nbsp;</td>
						<td>" . $data['transfer_price'] . "&nbsp;</td>
						<td class='right'>" . $data['renew_price'] . "&nbsp;</td>
					</tr>
		";
	}
	
	$html .= "
				</tbody>
				</table>
			</body>
		</html>
	";
	
	dd_send_email("New TLDs available", $html);
	
	return true;
}

/**
 * Send Email with updated prices for TLDs already on the database.
 * @param array $prices_updated Associative array of updated prices
 * @return boolean
 */
function dd_send_prices_email($prices_updated)
{
	$html = "
		<!doctype>
		<html>
			<head>
				<style>
				<!--
				TABLE {
					border: 0px solid black;
					border-width: 0px 0px 2px 0px;
				}
				
				TD {
					border: 1px solid black;
					border-width: 1px 1px 1px 0px;
					text-align: center;
				}
				
				TH {
					border: 1px solid black;
					border-width: 4px 1px 2px 0px;
					text-align: center;
					font-weight: bold;
					background-color: #d0d0d0;
				}

				.th-highlight {
					background-color: #c0c0c0;
					border-width: 4px 2px 2px 1px;
				}
				
				.th-left {
					border-width: 4px 1px 2px 4px;
				}
				
				.th-right {
					border-width: 4px 4px 2px 0px;
				}
				
				.tld-name {
					border-width: 2px 1px 2px 4px;
					vertical-align: middle;
				}

				.tld-first-row {
					border-width: 2px 1px 0px 0px;
				}
				
				.tld-first-end {
					border-width: 2px 4px 0px 0px;
				}
				
				.tld-middle-end {
					border-width: 1px 4px 1px 0px;
				}
				
				.tld-last-row {
					border-width: 0px 1px 2px 0px;
				}
				
				.tld-last-end {
					border-width: 0px 4px 2px 0px;
				}

				.tld-highlight {
					background-color: #c0c0c0;
					border-left: 1px solid #000;
					border-right: 2px solid #000;
				}
				-->
				</style>
			</head>
			<body>
				<h2>TLD Prices Update Report</h2>
				
				<p>The following TLDs have changed prices recently:</p>
				<p>
				
				<table border='1' width='100%' cellspacing='0' cellpadding='5'>
				<thead>
					<tr>
						<th class='th-left'>TLD</th>
						<th>Type</th>
						<th>Old Price</th>
						<th class='th-highlight'>New Price</th>
						<th class='th-right'>Difference (+/-)</th>
					</tr>
				</thead>
				<tbody>
	";
	
	foreach($prices_updated as $tld=>$data){
		$html .= "
					<tr>
						<td rowspan='3' class='tld-name'><strong>$tld</strong></td>
						<td class='tld-first-row'>Registration</td>
						<td class='tld-first-row'>" . $data['old_register_price'] . "&nbsp;</td>
						<td class='tld-first-row tld-highlight'>" . $data['register_price'] . "&nbsp;</td>
						<td class='tld-first-end'>" . $data['register_difference'] . "&nbsp;</td>
					</tr>
					
					<tr>
						<td class='tld-middle-row'>Transfer</td>
						<td class='tld-middle-row'>" . $data['old_transfer_price'] . "&nbsp;</td>
						<td class='tld-middle-row tld-highlight'>" . $data['transfer_price'] . "&nbsp;</td>
						<td class='tld-middle-end'>" . $data['transfer_difference'] . "&nbsp;</td>
					</tr>
					
					<tr>
						<td class='tld-last-row'>Renewal</td>
						<td class='tld-last-row'>" . $data['old_renew_price'] . "&nbsp;</td>
						<td class='tld-last-row tld-highlight'>" . $data['renew_price'] . "&nbsp;</td>
						<td class='tld-last-end'>" . $data['renew_difference'] . "&nbsp;</td>
					</tr>
		";
	}
	
	$html .= "
				</tbody>
				</table>
			</body>
		</html>
	";
	
	dd_send_email("TLD prices updated", $html);
	
	return false;
}

/**
 * Update prices on WHMCS database using the DonDominio domain price cache.
 * @return boolean
 */
function dd_update_prices()
{
	//Check if EUR is in currencies; if not, fail
	$q_currency = full_query( "SELECT id FROM tblcurrencies WHERE code='EUR'" );
	
	list( $currency ) = mysql_fetch_row( $q_currency );
	
	$domains = full_query("
		SELECT
			tld,
			register_price,
			transfer_price,
			renew_price,
			register_range,
			transfer_range,
			renew_range
		FROM mod_dondominio_pricing
		WHERE
			tld IN (SELECT extension FROM tbldomainpricing WHERE autoreg = 'dondominio' AND extension = tld)
	");
	
	if( mysql_num_rows( $domains ) == 0 ){
		return false;
	}
	
	while( list( $extension, $register_price, $transfer_price, $renew_price, $register_range, $transfer_range, $renew_range ) = mysql_fetch_row( $domains )){
		$register = array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );
		
		if( dd_get_whmcs_version() >= 6 ){
			$register = array( -1, -1, -1, -1, -1, -1, -1, -1, -1, -1 );
		}
		
		$transfer = array( -1, -1, -1, -1, -1, -1, -1, -1, -1, -1 );
		$renew = array( -1, -1, -1, -1, -1, -1, -1, -1, -1, -1 );
		
		//Adding increment to register price
		$register_increment = dd_get( "register_increase" );
		$register_increment_type = dd_get( "register_increase_type" );
		
		//Adding increment to transfer price
		$transfer_increment = dd_get( "transfer_increase" );
		$transfer_increment_type = dd_get( "transfer_increase_type" );
		
		//Adding increment to renew price
		$renew_increment = dd_get( "renew_increase" );
		$renew_increment_type = dd_get( "renew_increase_type" );
		
		/*
		 * Domain custom settings
		 */
		$custom_settings = full_query( "
			SELECT
				`id`,
				`tld`,
				`ignore`,
				`enabled`,
				`register_increase`,
				`register_increase_type`,
				`renew_increase`,
				`renew_increase_type`,
				`transfer_increase`,
				`transfer_increase_type`
			FROM `mod_dondominio_tld_settings` WHERE `tld` = '$extension' AND `ignore` = 0 AND `enabled` = 1
		" );
		
		if( mysql_num_rows( $custom_settings ) == 1 ){
			list( $id, $tld, $ignore, $enabled, $reg_inc, $reg_inc_type, $ren_inc, $ren_inc_type, $tra_inc, $tra_inc_type ) = mysql_fetch_row( $custom_settings );
			
			if( $enabled == 1 ){
				$register_increment = $reg_inc;
				$register_increment_type = $reg_inc_type;
				
				$transfer_increment = $tra_inc;
				$transfer_increment_type = $tra_inc_type;
				
				$renew_increment = $ren_inc;
				$renew_increment_type = $ren_inc_type;
			}
		}
		
		if( $register_increment > 0 ){
			if( $register_increment_type == 'fixed' && $register_increment > 0 ){
				$register_price += $register_increment;
			}elseif( $register_increment_type == 'no_increase' ){
				$register_price = $register_increment;
			}else{
				$register_price = $register_price * (( $register_increment / 100 ) + 1 );
			}
		}
		
		if( $transfer_increment > 0 ){
			if( $transfer_increment_type == 'fixed' && $transfer_increment > 0 ){
				$transfer_price += $transfer_increment;
			}elseif( $transfer_increment_type == 'no_increase' ){
				$transfer_price = $transfer_increment;
			}else{
				$transfer_price = $transfer_price * (( $transfer_increment / 100 ) + 1 );
			}
		}
		
		if( $renew_increment > 0 ){
			if( $renew_increment_type == 'fixed' && $renew_increment > 0 ){
				$renew_price += $renew_increment;
			}elseif( $renew_increment_type == 'no_increase' ){
				$renew_price = $renew_increment;
			}else{
				$renew_price = $renew_price * (( $renew_increment / 100 ) + 1 );
			}
		}
		
		//Register
		$register_terms = explode( ",", $register_range );
		
		foreach( $register_terms as $term ){
			if( strpos( $term, '-' )){
				$range = explode("-", $term);
				
				for( $i=$range[0]; $i<=$range[1]; $i++ ){
					$register[$i - 1] = $register_price * $i;
				}
			}else{
				$register[$term - 1] = $register_price * $term;
			}
		}
		
		//Transfer
		$transfer_terms = explode( ",", $transfer_range );
		
		foreach( $transfer_terms as $term ){
			if(strpos( $term, '-' )){
				$range = explode( "-", $term );
				
				for( $i=$range[0]; $i<=$range[1]; $i++ ){
					$transfer[$i - 1] = $transfer_price * $i;
				}
			}else{
				$transfer[$term - 1] = $transfer_price * $term;
			}
		}
		
		//Renew
		$renew_terms = explode( ",", $renew_range );
		
		foreach( $renew_terms as $term ){
			if( strpos( $term, '-' )){
				$range = explode( "-", $term );
				
				for($i=$range[0]; $i<=$range[1]; $i++){
					$renew[$i - 1] = $renew_price * $i;
				}
			}else{
				$renew[$term - 1] = $renew_price * $term;
			}
		}
		
		//Updating register prices
		$s_register = "
			UPDATE tblpricing
			SET
				msetupfee 		= '" . $register[0] . "',
				qsetupfee 		= '" . $register[1] . "',
				ssetupfee 		= '" . $register[2] . "',
				asetupfee 		= '" . $register[3] . "',
				bsetupfee 		= '" . $register[4] . "',
				monthly 		= '" . $register[5] . "',
				quarterly 		= '" . $register[6] . "',
				semiannually 	= '" . $register[7] . "',
				annually 		= '" . $register[8] . "',
				biennially 		= '" . $register[9] . "'
			WHERE
				type = 'domainregister'
				AND currency = '" . $currency . "'
				AND relid = (SELECT id FROM tbldomainpricing WHERE extension = '$extension' AND autoreg = 'dondominio')
				AND (
					relid NOT IN (SELECT id FROM tbldomainpricing WHERE extension IN (SELECT tld FROM mod_dondominio_tld_settings WHERE `ignore` = 1))
				)
		";
				
		//Updating transfer prices
		$s_transfer = "
			UPDATE tblpricing
			SET
				msetupfee 		= '" . $transfer[0] . "',
				qsetupfee 		= '" . $transfer[1] . "',
				ssetupfee 		= '" . $transfer[2] . "',
				asetupfee 		= '" . $transfer[3] . "',
				bsetupfee 		= '" . $transfer[4] . "',
				monthly 		= '" . $transfer[5] . "',
				quarterly 		= '" . $transfer[6] . "',
				semiannually 	= '" . $transfer[7] . "',
				annually 		= '" . $transfer[8] . "',
				biennially 		= '" . $transfer[9] . "'
			WHERE
				type = 'domaintransfer'
				AND currency = '" . $currency . "'
				AND relid = (SELECT id FROM tbldomainpricing WHERE extension = '$extension' AND autoreg = 'dondominio')
				AND (
					relid NOT IN (SELECT id FROM tbldomainpricing WHERE extension IN (SELECT tld FROM mod_dondominio_tld_settings WHERE `ignore` = 1))
				)
		";
		
		full_query( $s_transfer );
		
		//Updating renew prices
		$s_renew = "
			UPDATE tblpricing
			SET
				msetupfee 		= '" . $renew[0] . "',
				qsetupfee 		= '" . $renew[1] . "',
				ssetupfee 		= '" . $renew[2] . "',
				asetupfee 		= '" . $renew[3] . "',
				bsetupfee 		= '" . $renew[4] . "',
				monthly 		= '" . $renew[5] . "',
				quarterly 		= '" . $renew[6] . "',
				semiannually 	= '" . $renew[7] . "',
				annually 		= '" . $renew[8] . "',
				biennially 		= '" . $renew[9] . "'
			WHERE
				type = 'domainrenew'
				AND currency = '" . $currency . "'
				AND relid = (SELECT id FROM tbldomainpricing WHERE extension = '$extension' AND autoreg = 'dondominio')
				AND (
					relid NOT IN (SELECT id FROM tbldomainpricing WHERE extension IN (SELECT tld FROM mod_dondominio_tld_settings WHERE `ignore` = 1))
				)
		";
		
		full_query( $s_renew );
	}
	
	dd_update_domain_prices();
	
	return true;
}

add_hook('PreCronJob', 1, 'hook_daily_cron_job');

?>
