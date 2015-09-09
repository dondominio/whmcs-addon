<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: TLD Manager
 * WHMCS version 5.2.x / 5.3.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */

if(!defined("WHMCS")){
	die("This file cannot be accessed directly");
}

/**
 * Action: Index
 * TLDs list with prices & actions.
 * @param array $vars Parameters from WHMCS
 */
function dondominio_mod_tld_settings_index($vars)
{
	$LANG = $vars['_lang'];
	
	if( array_key_exists( 'registration', $_POST )){
		dd_update_tld_settings($vars);
	}
	
	$q_tld = full_query( "SELECT * from mod_dondominio_tld_settings WHERE tld = '" . $_GET['tld'] . "'" );
	
	$enable_checkbox = "";
	$ignore_checkbox = "";
	
	$register_increase_type_fixed = "checked='checked'";
	$register_increase_type_percent = "";
	$register_increase_type_disabled = "";
	
	$renew_increase_type_fixed = "checked='checked'";
	$renew_increase_type_percent = "";
	$renew_increase_type_disabled = "";
	
	$transfer_increase_type_fixed = "checked='checked'";
	$transfer_increase_type_percent = "";
	$transfer_increase_type_disabled = "";
	
	if( mysql_num_rows( $q_tld )){
		list(
			$id,
			$tld,
			$ignore,
			$enabled,
			$register_increase,
			$register_increase_type,
			$renew_increase,
			$renew_increase_type,
			$transfer_increase,
			$transfer_increase_type
		) = mysql_fetch_row( $q_tld );
		
		$ignore_checkbox = ( $ignore == 1 ) ? "checked='checked'" : "";
		$enabled_checkbox = ( $enabled == 1 ) ? "checked='checked'" : "";
		
		if( $register_increase_type == 'percent' ){
			$register_increase_type_fixed = "";
			$register_increase_type_percent = "checked='checked'";
			$register_increase_type_disabled = "";
		}else if( $register_increase_type == 'no_increase' ){
			$register_increase_type_fixed = "";
			$register_increase_type_percent = "";
			$register_increase_type_disabled = "checked='checked'";
		}
		
		if( $renew_increase_type == 'percent' ){
			$renew_increase_type_fixed = "";
			$renew_increase_type_percent = "checked='checked'";
			$renew_increase_type_disabled = "";
		}else if( $renew_increase_type == 'no_increase' ){
			$renew_increase_type_fixed = "";
			$renew_increase_type_percent = "";
			$renew_increase_type_disabled = "checked='checked'";
		}
		
		if( $transfer_increase_type == 'percent' ){
			$transfer_increase_type_fixed = "";
			$transfer_increase_type_percent = "checked='checked'";
			$transfer_increase_type_disabled = "";
		}else if( $transfer_increase_type == 'no_increase' ){
			$transfer_increase_type_fixed = "";
			$transfer_increase_type_percent = "";
			$transfer_increase_type_disabled = "checked='checked'";
		}
	}else{
		$register_increase = "0.00";
		$renew_increase = "0.00";
		$transfer_increase = "0.00";
	}
	
	echo "
	<h2>" . $LANG['tld_settings_title'] . ": " . $_GET['tld'] . "</h2>
	
	<p>" . $LANG['tld_settings_description'] . "</p>
	
	<form action='#' method='post'>
		<table class='form' width='100%' border='0' cellpadding='3' cellspacing='0'>
			<tbody>
				<tr>
					<td class='fieldlabel'>
						&nbsp;
					</td>
					
					<td class='fieldarea'>
						<label><input type='checkbox' name='no_update' $ignore_checkbox> " . $LANG['tld_settings_no_update'] . "</label>
					</td>
				</tr>
				
				<tr>
					<td class='fieldlabel'>
						&nbsp;
					</td>
					
					<td class='fieldarea'>
						<label><input type='checkbox' name='status' $enabled_checkbox> " . $LANG['tld_settings_enabled'] . "</label>
					</td>
				</tr>
				
				<tr>
					<td class='fieldlabel'>
						" . $LANG['settings_prices_register_add'] . "
					</td>
					
					<td class='fieldarea'>
						<input type='text' name='registration' size='20' value='$register_increase' />
						
						<label><input type='radio' name='registration_type' value='fixed' $register_increase_type_fixed> " . $LANG['settings_prices_type_fixed'] . "</label>
						<label><input type='radio' name='registration_type' value='percent' $register_increase_type_percent> " . $LANG['settings_prices_type_percent'] . "</label>
						<label><input type='radio' name='registration_type' value='no_increase' $register_increase_type_disabled> " . $LANG['settings_prices_type_disabled'] . "</label>
					</td>
				</tr>
				
				<tr>
					<td class='fieldlabel'>
						" . $LANG['settings_prices_transfer_add'] . "
					</td>
					
					<td class='fieldarea'>
						<input type='text' name='transfer' size='20' value='$transfer_increase' />
						
						<label><input type='radio' name='transfer_type' value='fixed' $transfer_increase_type_fixed> " . $LANG['settings_prices_type_fixed'] . "</label>
						<label><input type='radio' name='transfer_type' value='percent' $transfer_increase_type_percent> " . $LANG['settings_prices_type_percent'] . "</label>
						<label><input type='radio' name='transfer_type' value='no_increase' $transfer_increase_type_disabled> " . $LANG['settings_prices_type_disabled'] . "</label>
					</td>
				</tr>
				
				<tr>
					<td class='fieldlabel'>
						" . $LANG['settings_prices_renew_add'] . "
					</td>
					
					<td class='fieldarea'>
						<input type='text' name='renewal' size='20' value='$renew_increase' />
						
						<label><input type='radio' name='renewal_type' value='fixed' $renew_increase_type_fixed> " . $LANG['settings_prices_type_fixed'] . "</label>
						<label><input type='radio' name='renewal_type' value='percent' $renew_increase_type_percent> " . $LANG['settings_prices_type_percent'] . "</label>
						<label><input type='radio' name='renewal_type' value='no_increase' $renew_increase_type_disabled> " . $LANG['settings_prices_type_disabled'] . "</label>
					</td>
				</tr>
			</tbody>
		</table>
		
		<p align='center'>
			<button action='submit' name='submit_button' id='settings_submit' class='btn'>" . $LANG['btn_save'] . "</button>
			<a href='addonmodules.php?module=dondominio&action=tlds' class='btn'>" . $LANG['btn_back'] . "</a>
		</p>
	</form>
	";
}

function dd_update_tld_settings( $vars )
{
	$LANG = $vars['_lang'];
	
	//Checkbox "Enable settings"
	$enabled = ( $_POST['status'] == 'on' ) ? 1 : 0;
	
	//Checkbox "Do not update automatically"
	$ignore = ( $_POST['no_update'] == 'on' ) ? 1 : 0;
	
	//Creating/Updating TLD Settings
	full_query( "
		REPLACE INTO mod_dondominio_tld_settings(
			id,
			tld,
			`ignore`,
			enabled,
			register_increase,
			register_increase_type,
			renew_increase,
			renew_increase_type,
			transfer_increase,
			transfer_increase_type
		) VALUES (
			NULL,
			'" . $_GET['tld'] . "',
			'" . $ignore . "',
			'" . $enabled . "',
			'" . $_POST['registration'] . "',
			'" . $_POST['registration_type'] . "',
			'" . $_POST['renewal'] . "',
			'" . $_POST['renewal_type'] . "',
			'" . $_POST['transfer'] . "',
			'" . $_POST['transfer_type'] . "'
		)	
	" );
}

?>