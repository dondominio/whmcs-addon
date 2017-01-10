<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: Import
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
 * List of domains in DD account
 * @param array $vars Parameters from WHMCS
 */
function dondominio_mod_import_index($vars)
{
	$LANG = $vars['_lang'];
	
	if( array_key_exists( 'form_action', $_POST )){
		if(count($_POST['domain_checkbox'])){
			switch($_POST['form_action']){
			case 'import':
				dondominio_mod_import_doImport($vars, array_keys($_POST['domain_checkbox']));
				
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
	
	$page = (empty($_GET['page'])) ? 1 : intval($_GET['page']);
	
	$items = dd_get_setting("NumRecordstoDisplay");
	
	$dondominio = dd_init($vars);
	
	try{
		$domains = $dondominio->domain_list(array(
			'page' => $page,
			'pageLength' => intval($items)
		));
	}catch(DonDominioAPI_Error $e){
		die($e->getMessage());
	}
	
	if(array_key_exists('page', $_GET) && $_GET['page'] > 1){
		$page = $_GET['page'];
	}
	
	$total = $domains->get("queryInfo")['total'];
	
	$total_pages = ceil($total / $items);
	
	echo "
	<h2>" . $LANG['import_title'] . "</h2>
	
	<p>" . $LANG['import_info'] . "</p>
	
	<form action='addonmodules.php' method='get'>
		<input type='hidden' name='module' value='dondominio' />
		<input type='hidden' name='action' value='import' />
		
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
						" . $LANG['domains_status'] . "
					</th>
				</tr>
			</thead>
			<tbody>
		";
		
		$i = 1;
		
		$domain_items = $domains->get("domains");
		
		if(count($domain_items)){
			foreach($domain_items as $item){
				$status_class = 'cancelled';
				$status_text = $LANG['import_not_imported'];
				
				$check = full_query("SELECT * FROM tbldomains WHERE domain='" . $item['name'] . "'");
				
				if(mysql_num_rows($check) == 1){
					$status_class = 'active';
					$status_text = $LANG['import_imported'];
				}
				
				echo "
				<tr>
					<td>
						<input class='domain_checkbox' name='domain_checkbox[" . $item['name'] . "]' type='checkbox' />
					</td>
					
					<td>
						" . $item['name'] . "
					</td>
					
					<td>
						<div style='text-align: center;' class='label $status_class'>" . $status_text . "</div>
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
						" . $LANG['domains_status'] . "
					</th>
				</tr>
			</tfoot>
		</table>
		
		<br />
		" . $LANG['info_with_selected'] . "
		
		<select name='customer' id='import_customer'>
		";
		
		$q_customers = full_query("SELECT id, firstname, lastname FROM tblclients ORDER BY firstname, lastname");
		
		while(list($customer_id, $customer_firstname, $customer_lastname) = mysql_fetch_row($q_customers)){
			echo "
			<option value='$customer_id'>$customer_firstname $customer_lastname</option>
			";
		}
		
		echo "
		</select>
		
		<button id='import_import' name='form_action' value='import' class='btn'>" . $LANG['import_btn_import'] . "</button>
	</form>
	
	<p align='center'>
	";
	
	if($page > 1){
		echo "
		<a href='addonmodules.php?module=dondominio&action=import&page=" . ($page - 1) . "'>« " . $LANG['pagination_previous'] . "</a>
		";
	}else{	
		echo "
			« " . $LANG['pagination_previous'] . "
		";
	}
	
	echo "&nbsp;";
	
	if($page < $total_pages){
		echo "
		<a href='addonmodules.php?module=dondominio&action=import&page=" . ($page + 1) . "'>" . $LANG['pagination_next'] . " »</a>
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
 * Import domains from DonDominio API.
 * @param array $vars Parameters from WHMCS
 * @param array $domain_ids IDs of selected domains to import
 * @return boolean
 */
function dondominio_mod_import_doImport($vars, array $domain_ids = array())
{
	$LANG = $vars['_lang'];
	
	if(!is_array($domain_ids) || !count($domain_ids)){
		return false;
	}
	
	$order = dondominio_mod_create_order($_POST['customer']);
	
	if(!$order){
		return false;
	}
	
	$errors = array();
	$not_imported = array();
	$imported = array();
	
	$dondominio = dd_init($vars);
	
	foreach($domain_ids as $domain_id){
		$check = full_query("SELECT * FROM tbldomains WHERE domain='$domain_id'");
		
		if(mysql_num_rows($check) > 0){
			$not_imported[] = '- ' . $domain_id;
			
			continue;
		}
		
		try{
			$domain = $dondominio->domain_getInfo($domain_id, array('infoType' => 'status'));
		}catch(DonDominioAPI_Error $e){
			$errors[] = '- ' . $domain_id . ': ' . $e->getMessage();
			
			continue;
		}
		
		$s_insert = "
			INSERT INTO tbldomains(
				id,
				userid,
				orderid,
				type,
				registrationdate,
				domain,
				firstpaymentamount,
				recurringamount,
				registrar,
				registrationperiod,
				expirydate,
				subscriptionid,
				promoid,
				status,
				nextduedate,
				nextinvoicedate,
				additionalnotes,
				synced
			) VALUES (
				NULL,
				'" . $_POST['customer'] . "',
				'$order',
				'Register',
				'" . $domain->get("tsCreate") . "',
				'" . $domain->get("name") . "',
				0.00,
				0.00,
				'dondominio',
				1,
				'" . $domain->get("tsExpir") . "',
				'',
				0,
				'Active',
				'" . $domain->get("tsExpir") . "',
				'" . $domain->get("tsExpir") . "',
				'Created by DonDominio WHMCS Addon',
				0
			)
		";
		
		full_query($s_insert);
		
		$imported[] = '- ' . $domain_id;
	}
	
	if(count($errors)){
		echo "
			<div class='errorbox'>
				<strong>" . $LANG['import_error'] . "</strong><br />
				" . implode("<br />", $errors) . "
			</div>
		";
	}
	
	if(count($imported)){
		echo "
			<div class='successbox'>
				<strong>" . $LANG['import_success'] . "</strong><br />
				" . implode("<br />", $imported) . "
			</div>
		";
	}
	
	if(count($not_imported)){
		echo "
			<div class='infobox'>
				<strong>" . $LANG['import_completed_not_imported'] . "</strong><br />
				" . implode("<br />", $not_imported) . "
			</div>
		";
	}
	
	return true;
}

/**
 * Create a new order to hold domains.
 * @param integer $customer ID of the customer that will own the domains
 * @return integer|boolean
 */
function dondominio_mod_create_order($customer)
{
	$s_order = "
		INSERT INTO tblorders(
			id,
			ordernum,
			userid,
			contactid,
			date,
			amount,
			invoiceid,
			status,
			notes
		) VALUES (
			NULL,
			1,
			$customer,
			0,
			NOW(),
			0.00,
			0,
			'Active',
			'Created by DonDominio WHMCS Addon'
		)
	";
	
	if(!full_query($s_order)){
		return false;
	}
	
	$s_last_order = "SELECT MAX(id) FROM tblorders";
	
	$q_last_order = full_query($s_last_order);
	
	list($last_order) = mysql_fetch_row($q_last_order);
	
	return $last_order;
}