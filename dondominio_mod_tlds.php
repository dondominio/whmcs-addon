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
function dondominio_mod_tlds_index($vars)
{
	if( array_key_exists( 'form_action', $_POST )){
		switch($_POST['form_action']){
		case 'update':
			dondominio_mod_tlds_update($vars);
			break;
		case 'registrar':
			dondominio_mod_tlds_switchRegistrar($vars);
			break;
		case 'create':
			dondominio_mod_tlds_create($vars);
			dd_create_currency_placeholders();
			dd_update_currencies();
			break;
		}
	}
	
	$LANG = $vars['_lang'];
	
	/*
	* Pagination.
	*/
	$page = 1;
	
	$items = dd_get_setting("NumRecordstoDisplay");
	
	if(array_key_exists('page', $_GET) && $_GET['page'] > 1){
		$page = $_GET['page'];
	}
	
	list($total) = mysql_fetch_row(full_query("SELECT count(id) FROM tbldomainpricing"));
	
	$total_pages = ceil($total / $items);
	
	if($page > $total_pages){
		$page = $total_pages;
	}
	
	$start = (($page - 1) * $items);
	/* *** */
	
	$local_tlds = full_query("
		SELECT
			id,
			extension,
			autoreg
		FROM tbldomainpricing
		ORDER BY
			extension ASC
		LIMIT $start, $items
	");
	
	echo "
	<h2>" . $LANG['tld_title'] . "</h2>
	
	<p>" . $LANG['tld_info'] . "</p>
	
	<form action='addonmodules.php' method='get'>
		<input type='hidden' name='module' value='dondominio' />
		<input type='hidden' name='action' value='tlds' />
		
		<table width='100%' border='0' cellpadding='3' cellspacing='0'>
			<tbody>
				<tr>
					<td width='50%' align='left'>
						$total " . $LANG['pagination_results_found'] . ", " . $LANG['pagination_page'] . " $page " . $LANG['pagination_of'] . " $total_pages
					</td>
					
					<td width='50%' align='right'>
						" . $LANG['pagination_go_to'] . "
						<select name='page' onchange='submit()'>
						";
						
						for($i=1;$i<=$total_pages;$i++){
							echo "
							<option value='$i' ";
							
							if($i == $page){
								echo "selected=''";
							}
							
							echo ">$i</option>
							";
						}
						
						echo "
						</select>
						
						<input type='submit' value='" . $LANG['pagination_go'] . "' class='btn-small'>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	
	<form action='#' method='post'>
		<table class='datatable' width='100%' border='0' cellspacing='1' cellpadding='3'>
			<thead>
				<tr>
					<th width='1'>
						<input class='tld_check_all' type='checkbox' />
					</th>
					
					<th>
						" . $LANG['tld_tld'] . "
					</th>
					
					<th width='150'>
						" . $LANG['tld_registrar'] . "
					</th>
					
					<th width='20'>
					
					</th>
				</tr>
			</thead>
			<tbody>
		";
		
		if(mysql_num_rows($local_tlds) > 0){
			while(list($tld_id, $tld, $registrar) = mysql_fetch_row($local_tlds)){
				echo "
					<tr>
						<td>
							<input class='tld_checkbox' name='tld[$tld]' type='checkbox' />
						</td>
						
						<td>
				";
				
				if(empty($registrar)){
					echo "
							<strong>$tld</strong>
					";
				}else{			
					echo "
							$tld
					";
				}
							
				echo "
						</td>
						
						<td>
							$registrar
						</td>
						
						<td>
				";
				
				if(!empty($registrar)){
					echo "
							<a href='addonmodules.php?module=dondominio&action=tld_settings&tld=$tld'>
								<img src='images/edit.gif' width='16' height='16' border='0' alt='" . $LANG['btn_edit'] . "' />
							</a>
					";
				}
				
				echo "
						</td>
					</tr>
				";
			}
		}else{
			echo "
					<tr>
						<td colspan='4'>
							" . $LANG['info_no_results'] . "
						</td>
					</tr>
			";
		}
		
		echo "
			</tbody>
			<tfoot>
				<tr>
					<th width='1'>
						<input class='tld_check_all' type='checkbox' />
					</th>
					
					<th>
						" . $LANG['tld_tld'] . "
					</th>
					
					<th width='150'>
						" . $LANG['tld_registrar'] . "
					</th>
					
					<th width='20'>
					
					</th>
				</tr>
			</tfoot>
		</table>
		
		<br />
		
		" . $LANG['info_with_selected'] . " <button type='submit' name='form_action' value='update' class='btn'>" . $LANG['btn_prices_selected'] . "</button>
		<button type='submit' name='form_action' value='registrar' class='btn'>" . $LANG['btn_registrar_selected'] . "</button>
	</form>
	
	<p align='center'>
	";
	
	if(array_key_exists('filter_tld', $_GET)){
		$filter_var = '&filter_tld=' . $_GET['filter_tld'];
	}else{
		$filter_var = '';
	}
	
	if($page > 1){
		echo "
		<a href='addonmodules.php?module=dondominio&action=tlds$filter_var&page=" . ($page - 1) . "'>« " . $LANG['pagination_previous'] . "</a>
		";
	}else{	
		echo "
			« " . $LANG['pagination_previous'] . "
		";
	}
	
	echo "&nbsp;";
	
	if($page < $total_pages){
		echo "
		<a href='addonmodules.php?module=dondominio&action=tlds$filter_var&page=" . ($page + 1) . "'>" . $LANG['pagination_next'] . " »</a>
		";
	}else{	
		echo "
			" . $LANG['pagination_next'] . " »
		";
	}
	
	echo "
	</p>
	
	<script type='text/javascript'>
	<!--
		$('.tld_check_all').bind('change', function(e){
			$('.tld_checkbox').prop('checked', $(this).prop('checked'));
			$('.tld_check_all').prop('checked', $(this).prop('checked'));
		});
	-->
	</script>
	";
}

/**
 * Mass registrar switch for TLDs.
 * @param array $vars Parameters from WHMCS
 * @return boolean
 */
function dondominio_mod_tlds_switchRegistrar($vars)
{
	$LANG = $vars['_lang'];
	
	if(!array_key_exists('tld', $_POST)){
		echo "
			<div class='infobox'>
				" . $LANG['tld_no_selected'] . "
			</div>
		";
		
		return false;
	}
	
	$tlds_ids = array_keys($_POST['tld']);
	
	$added = array();
	
	foreach($tlds_ids as $tld_id){
		if(full_query("UPDATE tbldomainpricing SET autoreg='dondominio' WHERE extension='$tld_id'")){
			$added[] = '- ' . $tld_id;
		}
	}
	
	echo "
	<div class='successbox'>
		" . $LANG['tld_update_success'] . "<br>
		" . implode('<br />', $added) . "
	</div>
	";
	
	return true;
}

/**
 * Update TLDs pricing according to prices from API.
 * @param array $vars Parameters from WHMCS
 * @return boolean
 */
function dondominio_mod_tlds_update($vars)
{
	dd_create_currency_placeholders();
	
	$LANG = $vars['_lang'];
	
	//Check if EUR is in currencies; if not, fail
	$q_currency = full_query("SELECT id FROM tblcurrencies WHERE code='EUR'");
	
	list($currency) = mysql_fetch_row($q_currency);
	
	if(!array_key_exists('tld', $_POST)){
		echo "
			<div class='infobox'>
				" . $LANG['tld_no_selected'] . "
			</div>
		";
		
		return false;
	}
	
	$tlds_ids = array_keys($_POST['tld']);
	
	foreach($tlds_ids as $extension){
		$tld_info = full_query("SELECT register_price, transfer_price, renew_price, register_range, transfer_range, renew_range FROM mod_dondominio_pricing WHERE tld = '$extension'");
		
		if(mysql_num_rows($tld_info) == 0){
			continue;
		}
		
		list($register_price, $transfer_price, $renew_price, $register_range, $transfer_range, $renew_range) = mysql_fetch_row($tld_info);
		
		$register = array( 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 );
		
		if( dd_get_whmcs_version() >= 6 ){
			$register = array( -1, -1, -1, -1, -1, -1, -1, -1, -1, -1 );
		}
		
		$transfer = array( -1, -1, -1, -1, -1, -1, -1, -1, -1, -1 );
		$renew = array( -1, -1, -1, -1, -1, -1, -1, -1, -1, -1 );
		
		$register_increment = dd_get("register_increase");
		$transfer_increment = dd_get("transfer_increase");
		$renew_increment = dd_get("renew_increase");
		
		$register_increment_type = dd_get("register_increase_type");
		$transfer_increment_type = dd_get("transfer_increase_type");
		$renew_increment_type = dd_get("renew_increase_type");
		
		/*
		 * Domain custom settings
		 */
		$custom_settings = full_query( "SELECT id, tld, `ignore`, enabled, register_increase, register_increase_type, renew_increase, renew_increase_type, transfer_increase, transfer_increase_type FROM mod_dondominio_tld_settings WHERE tld = '$extension'" );
		
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
		$register_terms = explode(',', $register_range);
		
		foreach($register_terms as $term){
			if(strpos($term, '-')){
				$range = explode('-', $term);
				
				for($i=$range[0]; $i<=$range[1]; $i++){
					$register[$i - 1] = $register_price * $i;
				}
			}else{
				$register[$term - 1] = $register_price * $term;
			}
		}
				
		//Transfer
		$transfer_terms = explode(',', $transfer_range);
		
		foreach($transfer_terms as $term){
			if(strpos($term, '-')){
				$range = explode('-', $term);
				
				for($i=$range[0]; $i<=$range[1]; $i++){
					$transfer[$i - 1] = $transfer_price * $i;
				}
			}else{
				$transfer[$term - 1] = $transfer_price * $term;
			}
		}
				
		//Renew
		$renew_terms = explode(',', $renew_range);
		
		foreach($renew_terms as $term){
			if(strpos($term, '-')){
				$range = explode('-', $term);
				
				for($i=$range[0]; $i<=$range[1]; $i++){
					$renew[$i - 1] = $renew_price * $i;
				}
			}else{
				$renew[$term - 1] = $renew_price * $term;
			}
		}
		
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
		";
				
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
		";
		
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
		";
		
		full_query($s_register);
		full_query($s_transfer);
		full_query($s_renew);
		
		$extensions_updated[] = ' - ' . $extension;
	}
	
	//Update currency exchange between different currencies
	dd_update_currencies();
	
	//Actualizar precios de dominios
	dd_update_domain_prices();
	
	echo "
		<div class='successbox'>
			" . $LANG['tld_prices_success'] . "<br />
			" . implode("<br />", $extensions_updated) . "
		</div>
	";
	
	return true;
}
