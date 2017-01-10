<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: Domain Manager
 * WHMCS version 5.2.x / 5.3.x / 6.x / 7.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */

if(!defined("WHMCS")){
	die("This file cannot be accessed directly");
}

/**
 * Action: Index
 * Domain list with bulk actions.
 * @param array $vars Parameters from WHMCS
 */
function dondominio_mod_domains_index( $vars )
{
	$LANG = $vars['_lang'];
	
	if( array_key_exists( 'form_action', $_POST )){
		if( count( $_POST['domain_checkbox'] )){
			switch( $_POST['form_action'] ){
			case 'registrar':
				dondominio_mod_domains_switchRegistrar( $vars, array_keys( $_POST['domain_checkbox'] ));
				
				break;
			case 'sync':
				dondominio_mod_domains_sync( $vars, array_keys( $_POST['domain_checkbox'] ));
				
				break;
			case 'price':
				dondominio_mod_domains_updatePrice( $vars, array_keys( $_POST['domain_checkbox'] ));
				
				break;
			case 'owner':
			case 'tech':
			case 'admin':
			case 'billing':
				if( empty( $_POST['ddid'] )){
					echo "
					<div class='errorbox'>
						" . $LANG['domains_error_dondominio_id'] . ". <a href='https://docs.dondominio.com/api/#section-5-3' target='_api'>" . $LANG['link_more_info'] . "</a>
					</div>
					";
					
					break;
				}
				
				dondominio_mod_domains_updateContact( $vars, array_keys( $_POST['domain_checkbox'] ), $_POST['form_action'], $_POST['ddid'] );
				break;
			}
		}else{
			echo "
			<div class='errorbox'>
				<span class='title'>" . $LANG['domains_no_domains_selected'] . "</span>
			</div>
			";
		}
	}
	
	/*
	 * Sync single domain information.
	 */
	if( array_key_exists( 'sync', $_GET ) && is_numeric( $_GET['sync'] )){
		dondominio_mod_domains_sync( $vars, array( $_GET['sync'] ));
	}
	
	$registrar = ( empty($_POST['registrar'] )) ? '%' : $_POST['registrar'];
	$status = ( empty($_POST['status'] )) ? '%' : $_POST['status'];
	
	/*
	 * Pagination.
	 */
	$page = 1;
	$total_pages = 1;
	
	$items = dd_get_setting( "NumRecordstoDisplay" );
	
	if( array_key_exists( 'page', $_GET ) && $_GET['page'] > 1 ){
		$page = $_GET['page'];
	}
	
	if( !array_key_exists( 'domain', $_POST )){
		$_POST['domain'] = '';
	}
	
	if( !array_key_exists( 'tld', $_POST )){
		$_POST['tld'] = '';
	}
	
	if( !array_key_exists( 'ddid', $_POST )){
		$_POST['ddid'] = '';
	}
	
	$total_tlds = full_query("
		SELECT
			count(id)
		FROM tbldomains D
		WHERE
			D.domain LIKE '%" . $_POST['domain'] . "%'
			AND D.registrar LIKE '" . $registrar . "'
			AND D.status LIKE '" . $status . "'
			AND SUBSTRING(D.domain FROM -" . strlen( $_POST['tld'] ) . " FOR " . strlen( $_POST['tld'] ) . ") = '" . $_POST['tld'] . "'
	");
	
	list( $total ) = mysql_fetch_row( $total_tlds );
	
	$total_pages = ceil( $total / $items );
	
	if( $page > $total_pages ){
		$page = $total_pages;
	}
	
	$start = (( $page - 1 ) * $items );
	/* *** */
	
	$domains = full_query("
		SELECT
			D.id,
			D.domain,
			D.status,
			D.registrar
		FROM tbldomains D
		WHERE
			D.domain LIKE '%" . $_POST['domain'] . "%'
			AND D.registrar LIKE '" . $registrar . "'
			AND D.status LIKE '" . $status . "'
			AND SUBSTRING(D.domain FROM -" . strlen($_POST['tld']) . " FOR " . strlen($_POST['tld']) . ") = '" . $_POST['tld'] . "'
		ORDER BY
			D.domain ASC
		LIMIT $start, $items
	");
	
	$tlds = full_query("SELECT id, extension FROM tbldomainpricing ORDER BY extension ASC");
	$registrars = full_query("SELECT DISTINCT registrar FROM tbldomains ORDER BY registrar ASC");
	
	echo "
	<h2>" . $LANG['domains_title'] . "</h2>
	
	<p>" . $LANG['domains_info'] . "</p>
	
	<p>" . $LANG['info_too_much_requests'] . "</p>
	
	<div id='tabs'>
		<ul class='nav nav-tabs admin-tabs' role='tablist'>
			<li id='tab0' class='tab'>
				<a href='javascript:;'>" . $LANG['filter_title'] . "</a>
			</li>
		</ul>
	</div>
	
	<div id='tab0box' class='tabbox'>
		<div id='tab_content'>
			<form action='#' method='post'>
				<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
					<tbody>
						<tr>
							<td width='15%' class='fieldlabel'>
								" . $LANG['filter_domain'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='domain' size='30' value='" . $_POST['domain'] . "'>
							</td>
							
							<td width='15%' class='fieldlabel'>
								" . $LANG['filter_tld'] . "
							</td>
							
							<td class='fieldarea'>
								<select name='tld' id='tldDropDown'>
									<option value=''>" . $LANG['filter_any'] . "</option>
								";
									
								while(list($tld_id, $extension) = mysql_fetch_row($tlds)){
									$selected = "";
									
									if($extension == $_POST['tld']){
										$selected = "selected='selected'";
									}
									
									echo "
									<option $selected value='$extension'>$extension</option>
									";
								}
									
								echo "
								</select>
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['filter_registrar'] . "
							</td>
							
							<td class='fieldarea'>
								<select name='registrar' id='registrarsDropDown'>
									<option value=''>" . $LANG['filter_any'] . "</option>
								";
									
								while(list($registrar) = mysql_fetch_row($registrars)){
									$selected = "";
									
									if($registrar == $_POST['registrar']){
										$selected = "selected='selected'";
									}
									
									echo "
									<option $selected value='$registrar'>$registrar</option>
									";
								}
									
								echo "
								</select>
							</td>
							
							<td class='fieldlabel'>
								" . $LANG['filter_status'] . "
							</td>
							
							<td class='fieldarea'>
								<select name='status'>
							";
							
							$statuses = array(
								$LANG['filter_any'] => '',
								$LANG['filter_pending'] => 'Pending',
								$LANG['filter_pending_transfer'] => 'Pending Transfer',
								$LANG['filter_active'] => 'Active',
								$LANG['filter_expired'] => 'Expired',
								$LANG['filter_cancelled'] => 'Cancelled',
								$LANG['filter_fraud'] => 'Fraud'
							);
							
							foreach($statuses as $key=>$status){
								$selected = "";
								
								if($status == $_POST['status']){
									$selected = "selected='selected'";
								}
								
								$text = (!is_numeric($key)) ? $key : $status;
								
								echo "<option $selected value='" . $status . "'>" . $text . "</option>";
							}
								
							echo "
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				
				<p align='center'>
					<input type='submit' id='search-clients' value='" . $LANG['filter_search'] . "' class='button'>
				</p>
			</form>
		</div>
	</div>
	
	<br />
	
	<form action='addonmodules.php' method='get'>
		<input type='hidden' name='module' value='dondominio' />
		<input type='hidden' name='action' value='domains' />
		
	";
	
	if(array_key_exists('domain', $_GET)){
		echo "
		<input type='hidden' name='domain' value='" . $_GET['domain'] . "' />
		";
	}
	
	if( array_key_exists( 'registrar', $_GET )){
		echo "
		<input type='hidden' name='registrar' value='" . $_GET['registrar'] . "' />
		";
	}
	
	if( array_key_exists( 'status', $_GET )){
		echo "
		<input type='hidden' name='status' value='" . $_GET['status'] . "' />
		";
	}
	
	echo "
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
	
	<form action='' method='post'>
		<table class='datatable' width='100%' border='0' cellspacing='1' cellpadding='3'>
			<thead>
				<tr>
					<th width='1'>
						<input class='domains_check_all' type='checkbox' />
					</th>
					
					<th>
						" . $LANG['domains_domain'] . "
					</th>
					
					<th width='100'>
						" . $LANG['domains_registrar'] . "
					</th>
					
					<th width='100'>
						" . $LANG['domains_status'] . "
					</th>
					
					<th width='20'>
						&nbsp;
					</th>
				</tr>
			</thead>
			<tbody>
		";
		
		$i = 1;
		
		if(mysql_num_rows($domains) > 0){
			while(list($id, $domain, $status, $registrar) = mysql_fetch_row($domains)){
				$status_class = '';
				
				switch($status){
				case 'Active':
					$status_class = 'active';
					break;
				case 'Pending':
				case 'Pending Transfer':
					$status_class = 'pending';
					break;
				case 'Expired':
					$status_class = 'expired';
					break;
				case 'Cancelled':
					$status_class = 'cancelled';
					break;
				case 'Fraud':
					$status_class = 'fraud';
					break;
				}
				
				echo "
				<tr>
					<td>
						<input class='domain_checkbox' name='domain_checkbox[$id]' type='checkbox' />
					</td>
					
					<td>
						$domain
					</td>
					
					<td>
						$registrar
					</td>
					
					<td>
						<div style='text-align: center;' class='label $status_class'>$status</div>
					</td>
					
					<td>
						<a href='addonmodules.php?module=dondominio&action=domains&sync=$id'><img src='images/icons/navrotate.png'></a>
					</td>
				</tr>
				";
				
				$i++;
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
						<input class='domains_check_all' type='checkbox' />
					</th>
					
					<th>
						" . $LANG['domains_domain'] . "
					</th>
					
					<th width='100'>
						" . $LANG['domains_registrar'] . "
					</th>
					
					<th width='100'>
						" . $LANG['domains_status'] . "
					</th>
					
					<th width='20'>
						&nbsp;
					</th>
				</tr>
			</tfoot>
		</table>
		
		<br />
		
		" . $LANG['info_with_selected'] . " <button id='domain_dondominio' name='form_action' value='registrar' class='btn'>" . $LANG['domains_set_dondominio'] . "</button>
		<button id='domain_sync' name='form_action' value='sync' class='btn'>" . $LANG['domains_sync'] . "</button>
		<button id='domain_price' name='form_action' value='price' class='btn'>" . $LANG['domains_price'] . "</button>
		
		<br /><br />
		
		<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
			<tbody>
				<tr>
					<td width='20%' class='fieldlabel'>
						" . $LANG['domains_contact_id'] . "
					</td>
					
					<td class='fieldarea'>
						<input type='text' name='ddid' size='30' value='" . $_POST['ddid'] . "' placeholder='XXX-00000' /> <a href='https://docs.dondominio.com/api/#section-5-3' target='_api'>" . $LANG['link_more_info'] . "</a>
					</td>
				</tr>
				
				<tr>
					<td class='fieldlabel'>
						&nbsp;
					</td>
					
					<td class='fieldarea'>
						<button id='domain_owner' name='form_action' value='owner' class='btn updateContact'>" . $LANG['domains_set_owner'] . "</button>
						<button id='domain_owner' name='form_action' value='admin' class='btn updateContact'>" . $LANG['domains_set_admin'] . "</button>
						<button id='domain_owner' name='form_action' value='tech' class='btn updateContact'>" . $LANG['domains_set_tech'] . "</button>
						<button id='domain_owner' name='form_action' value='billing' class='btn updateContact'>" . $LANG['domains_set_billing'] . "</button>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	
	<p align='center'>
	";
	
	$filter_var = '';
	
	if( array_key_exists( 'domain', $_GET )){
		$filter_var .= '&domain=' . $_GET['domain'];
	}
	
	if( array_key_exists( 'registrar', $_GET )){
		$filter_var .= '&registrar=' . $_GET['registrar'];
	}
	
	if( array_key_exists( 'status', $_GET )){
		$filter_var .= '&status=' . $_GET['status'];
	}
	
	if( $page > 1 ){
		echo "
		<a href='addonmodules.php?module=dondominio&action=domains$filter_var&page=" . ($page - 1) . "'>« " . $LANG['pagination_previous'] . "</a>
		";
	}else{	
		echo "« " . $LANG['pagination_previous'];
	}
	
	echo "&nbsp;";
	
	if( $page < $total_pages ){
		echo "
		<a href='addonmodules.php?module=dondominio&action=domains$filter_var&page=" . ($page + 1) . "'>" . $LANG['pagination_next'] . " »</a>
		";
	}else{	
		echo $LANG['pagination_next'] . " »";
	}
	
	echo "
	</p>
	
	<script type='text/javascript'>
	<!--
		$('.domains_check_all').bind('change', function(e)
		{
			$('.domain_checkbox').prop('checked', $(this).prop('checked'));
			$('.domains_check_all').prop('checked', $(this).prop('checked'));
		});
		
		function toggleadvsearch()
		{
			if (document.getElementById('searchbox').style.visibility==\"hidden\") {
				document.getElementById('searchbox').style.visibility=\"\";
			} else {
				document.getElementById('searchbox').style.visibility=\"hidden\";
			}
		}
		
		$(document).ready(function()
		{
			$(\".tabbox\").css(\"display\",\"none\");
			
			var selectedTab;
			
			$(\".tab\").click(function()
			{
				var elid = $(this).attr(\"id\");
				$(\".tab\").removeClass(\"tabselected\");
				$(\"#\"+elid).addClass(\"tabselected\");
				$(\".tabbox\").slideUp();
				if (elid != selectedTab) {
					selectedTab = elid;
					$(\"#\"+elid+\"box\").slideDown();
				} else {
					selectedTab = null;
					$(\".tab\").removeClass(\"tabselected\");
				}
				$(\"#tab\").val(elid.substr(3));
			});
		});
	-->
	</script>
	";
}

/**
 * Update renewal price for selected domains.
 * @param array $domain_ids Internal IDs of the domains to update
 */
function dondominio_mod_domains_updatePrice( $vars, $domain_ids )
{
	$LANG = $vars['_lang'];
	
	foreach( $domain_ids as $domain_id ){
		//Get domain details
		$domain = full_query("SELECT domain FROM tbldomains WHERE id = '" . $domain_id . "' AND registrar = 'dondominio'");
		
		if( !mysql_num_rows( $domain )){
			continue;
		}
		
		list( $domain_name ) = mysql_fetch_row( $domain );
		
		$dot = strpos( $domain_name, '.' );
		
		if( !$dot ){
			continue;
		}
		
		$extension = substr( $domain_name, $dot );
		
		//Get TLD ID
		$tld = full_query("SELECT id FROM tbldomainpricing WHERE extension = '" . $extension . "' AND autoreg = 'dondominio'");

		if( !mysql_num_rows( $tld )){
			$errors[] = '- ' . $domain_name . ': ' . $LANG['domains_tld_not_valid'];
			
			continue;
		}
		
		list( $tld_id ) = mysql_fetch_row( $tld );
		
		//Get EUR code
		$eur = full_query("SELECT id FROM tblcurrencies WHERE code = 'EUR'");
		
		if( !mysql_num_rows( $eur )){
			$errors[] = '- ' . $domain_name . ': ' . $LANG['domains_eur_not_found'];
				
			continue;
		}
		
		list( $eur_id ) = mysql_fetch_row( $eur );
		
		//Get TLD renewal price
		$pricing = full_query("SELECT msetupfee FROM tblpricing WHERE type = 'domainrenew' AND relid = '" . $tld_id . "' AND currency = '" . $eur_id . "'");
		
		if( !mysql_num_rows( $pricing )){
			$errors[] = '- ' . $domain_name . ': ' . $LANG['domains_tld_price_not_found'];
				
			continue;
		}
		
		list( $renew_price ) = mysql_fetch_row( $pricing );
		
		//Update domain renewal price
		full_query("UPDATE tbldomains SET recurringamount = '" . $renew_price . "' WHERE id = '" . $domain_id . "'");
		
		$success_domains[] = '- ' . $domain_name;
	}
	
	if( count( $success_domains )){
		echo "
		<div class='successbox'>
			<strong>" . $LANG['domains_price_update_success'] . ":</strong><br />
			" . implode("<br />", $success_domains) . "
		</div>
		";
	}
	
	if( count( $errors )){
		echo "
		<div class='infobox'>
			<strong>" . $LANG['domains_price_errors'] . ":</strong><br />
			" . implode( "<br />", $errors ) . "
		</div>
		";
	}
}

/**
 * Mass registrar switch to DonDominio
 * @param array $domain_ids Internal IDs of the domains to update
 */
function dondominio_mod_domains_switchRegistrar($vars, $domain_ids)
{
	$LANG = $vars['_lang'];
	
	foreach($domain_ids as $domain_id){
		full_query("UPDATE tbldomains SET registrar = 'dondominio' WHERE id = '" . $domain_id . "'");
		
		$q_domain = full_query("SELECT domain FROM tbldomains WHERE id = '$domain_id'");
		
		list($domain) = mysql_fetch_row($q_domain);
		
		$success_domains[] = '- ' . $domain;
	}
	
	echo "
	<div class='successbox'>
		<strong>" . $LANG['domains_registrar_success'] . ":</strong><br />
		" . implode("<br />", $success_domains) . "
	</div>
	";
	
	return true;
}

/**
 * Sync domain information with DonDominio API
 * @param array $vars Parameters from WHMCS
 * @param array $domain_ids Array of Domain IDs to be synced
 * @return boolean
 */
function dondominio_mod_domains_sync($vars, $domain_ids)
{
	$LANG = $vars['_lang'];
	
	$dondominio = dd_init($vars);
	
	$errors = array();
	$success_domains = array();
	
	foreach($domain_ids as $domain_id){
		$q_domain = full_query("SELECT domain FROM tbldomains WHERE id='$domain_id'");
		
		if(mysql_num_rows($q_domain) != 1){
			$errors[] = '- ' . $LANG['domains_not_found'] . ": " . $domain_id;
			continue;
		}
		
		list($domain) = mysql_fetch_row($q_domain);
		
		try{
			$info = $dondominio->domain_getInfo($domain, array('infoType' => 'status'));
			
			//Checking if the domain is active
			$active = (
				in_array(
					$info->get('status'),
					array(
						'active',
						'renewed',
						'expired-renewgrace',
						'expired-redemption',
						'expired-pendingdelete'
					)
				)
			) ? true : false;
			
			//Checking if the domain has expired
			$expired = (
				in_array(
					$info->get('status'),
					array(
						'expired-renewgrace',
						'expired-redemption',
						'expired-pendingdelete'
					)
				)
			) ? true : false;
			
			$status = 'Pending';
			if($active === true) $status = 'Active';
			if($expired === true) $status = 'Expired';
			
			full_query("
				UPDATE tbldomains
				SET
					expirydate = '" . $info->get("tsExpir") . "',
					status = '$status'
				WHERE
					domain = '$domain'
			");
			
			$success_domains[] = '- ' . $domain;
		}catch(DonDominioAPI_Error $e){
			$errors[] = '- ' . $domain . ': ' . $e->getMessage();
		}
	}
	
	if(count($errors) > 0){
		echo "
			<div class='errorbox'>
				<strong>" . $LANG['info_errors'] . "</strong>
				<br />
				" . implode("<br />", $errors) . "
			</div>";
	}else{
		echo "
			<div class='successbox'>
				<span class='title'>" . $LANG['domains_sync_success'] . ":</span><br />
				" . implode("<br>", $success_domains) . "
			</div>
		";
	}
	
	return true;
}

/**
 * Mass contact update
 * @param array $vars Parameters from WHMCS
 * @param array $domain_ids Internal IDs of the domains to update
 * @param string $type Contact type
 * @param string $ddid DonDominio Contact ID to use
 */
function dondominio_mod_domains_updateContact($vars, $domain_ids, $type, $ddid)
{
	$LANG = $vars['_lang'];
	
	$dondominio = dd_init($vars);
	
	foreach($domain_ids as $domain_id){
		$domain = full_query("SELECT domain FROM tbldomains WHERE id='$domain_id'");
		
		if(mysql_num_rows($domain) == 1){
			$domain_data = mysql_fetch_array($domain, MYSQL_ASSOC);
			
			try{
				$update = $dondominio->domain_updateContacts(
					$domain_data['domain'],
					array(
						$type . 'ContactID' => $ddid
					)
				);
				
				$success[] = '- ' . $domain_data['domain'];
			}catch(DonDominioAPI_Error $e){
				$errors[] = '- ' . $domain_data['domain'] . ': ' . $e->getMessage();
			}
		}
	}
	
	if(count($errors) > 0){
		echo "
			<div class='errorbox'>
				<strong>" . $LANG['domains_contacts_error'] . ":</strong><br />
				" . implode('<br />', $errors) . "
			</div>
		";
	}
	
	if(count($success) > 0){
		echo "
		<div class='successbox'>
			<strong>" . $LANG['domains_contacts_success'] . ":</strong><br />
				" . implode("<br />", $success) . "
		</div>
		";
	}
	
	echo "
	<div class='infobox'>
		<strong>" . $LANG['domains_operation_complete'] . ":</strong><br>
		" . $LANG['domains_requests'] . ": " . count($domain_ids) . "; " . $LANG['domains_success'] . ": " . count($success) . "; " . $LANG['domains_errors'] . ": " . count($errors) . "
	</div>
	";
	
	return true;
}