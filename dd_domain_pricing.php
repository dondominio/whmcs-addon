<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: Domain Pricing
 * WHMCS version 5.2.x / 5.3.x / 6.x / 7.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */

/**
 * Creates pricing for a new TLD.
 * @param string $tld TLD the prices will be created for
 * @return bool
 */
function dd_domain_pricing( $tld )
{
	//Getting currency
	$euro_currency = full_query("
		SELECT
			id
		FROM tblcurrencies
		WHERE
			code='EUR'
	");
	
	if( mysql_num_rows( $euro_currency ) == 0 ){
		echo "
		<div class='errorbox'>
			Currency EUR not found
		</div>
		";
		
		return false;
	}
	
	list( $euro ) = mysql_fetch_row( $euro_currency );
	
	//Getting prices for TLD
	$prices = full_query("
		SELECT
			register_price,
			transfer_price,
			renew_price,
			register_range,
			transfer_range,
			renew_range
		FROM mod_dondominio_pricing
		WHERE
			tld='$tld'
	");
	
	if( mysql_num_rows( $prices ) == 0 ){
		echo "
		<div class='errorbox'>
			TLD not found in local cache. Regenerate cache and try again.
		</div>
		";
	}
	
	list($register_price, $transfer_price, $renew_price, $register_range, $transfer_range, $renew_range) = mysql_fetch_row($prices);
	
	$register = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	
	if( dd_get_whmcs_version() >= 6 ){
		$register = array( -1, -1, -1, -1, -1, -1, -1, -1, -1, -1 );
	}
	
	$transfer = array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
	$renew = array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
	
	//Adding increment to register price
	$register_increment = dd_get("register_increase");
	$register_increment_type = dd_get("register_increase_type");
	
	if($register_increment > 0){
		if($register_increment_type == 'fixed' && $register_increment > 0){
			$register_price += $register_increment;
		}else{
			$register_price = $register_price * (($register_increment / 100) + 1);
		}
	}
	
	//Adding increment to transfer price
	$transfer_increment = dd_get("transfer_increase");
	$transfer_increment_type = dd_get("transfer_increase_type");
	
	if($transfer_increment > 0){
		if($transfer_increment_type == 'fixed'){
			$transfer_price += $transfer_increment;
		}else{
			$transfer_price = $transfer_price * (($transfer_increment / 100) + 1);
		}
	}
	
	//Adding increment to renew price
	$renew_increment = dd_get("renew_increase");
	$renew_increment_type = dd_get("renew_increase_type");
	
	if($renew_increment > 0){
		if($renew_increment_type == 'fixed' && $renew_increment > 0){
			$renew_price += $renew_increment;
		}else{
			$renew_price = $renew_price * (($renew_increment / 100) + 1);
		}
	}
	
	//Register
	$register_terms = explode(",", $register_range);
	
	foreach($register_terms as $term){
		if(strpos($term, '-')){
			$range = explode("-", $term);
			
			for($i=$range[0]; $i<=$range[1]; $i++){
				$register[$i - 1] = $register_price * $i;
			}
		}else{
			$register[$term - 1] = $register_price * $term;
		}
	}
	
	//Transfer
	$transfer_terms = explode( ",", $transfer_range );
	
	foreach( $transfer_terms as $term ){
		if(strpos($term, '-')){
			$range = explode("-", $term);
			
			for($i=$range[0]; $i<=$range[1]; $i++){
				$transfer[$i - 1] = $transfer_price * $i;
			}
		}else{
			$transfer[$term - 1] = $transfer_price * $term;
		}
	}
	
	//Renew
	$renew_terms = explode(",", $renew_range);
	
	foreach($renew_terms as $term){
		if(strpos($term, '-')){
			$range = explode("-", $term);
			
			for($i=$range[0]; $i<=$range[1]; $i++){
				$renew[$i - 1] = $renew_price * $i;
			}
		}else{
			$renew[$term - 1] = $renew_price * $term;
		}
	}
	
	$s_register = "
		INSERT INTO tblpricing(
			type,
			currency,
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
		) VALUES (
			'domainregister',
			$euro,
			(SELECT id FROM tbldomainpricing WHERE extension = '$tld' AND autoreg = 'dondominio'),
			'" . $register[0] . "',
			'" . $register[1] . "',
			'" . $register[2] . "',
			'" . $register[3] . "',
			'" . $register[4] . "',
			'" . $register[5] . "',
			'" . $register[6] . "',
			'" . $register[7] . "',
			'" . $register[8] . "',
			'" . $register[9] . "'
		)
	";
	
	$s_transfer = "
		INSERT INTO tblpricing(
			type,
			currency,
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
		) VALUES (
			'domaintransfer',
			$euro,
			(SELECT id FROM tbldomainpricing WHERE extension = '$tld' AND autoreg = 'dondominio'),
			'" . $transfer[0] . "',
			'" . $transfer[1] . "',
			'" . $transfer[2] . "',
			'" . $transfer[3] . "',
			'" . $transfer[4] . "',
			'" . $transfer[5] . "',
			'" . $transfer[6] . "',
			'" . $transfer[7] . "',
			'" . $transfer[8] . "',
			'" . $transfer[9] . "'
		)
	";
	
	$s_renew = "
		INSERT INTO tblpricing(
			type,
			currency,
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
		) VALUES (
			'domainrenew',
			$euro,
			(SELECT id FROM tbldomainpricing WHERE extension = '$tld' AND autoreg = 'dondominio'),
			'" . $renew[0] . "',
			'" . $renew[1] . "',
			'" . $renew[2] . "',
			'" . $renew[3] . "',
			'" . $renew[4] . "',
			'" . $renew[5] . "',
			'" . $renew[6] . "',
			'" . $renew[7] . "',
			'" . $renew[8] . "',
			'" . $renew[9] . "'
		)
	";
	
	full_query($s_register);
	full_query($s_transfer);
	full_query($s_renew);
	
	return true;
}

?>
