<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: Import
 * WHMCS version 5.2.x / 5.3.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */

if( !defined( "WHMCS" )){
	die( "This file cannot be accessed directly" );
}

/**
 * Action: Index
 * List of domains in DD account
 * @param array $vars Parameters from WHMCS
 */
function dondominio_mod_transfer_index( $vars )
{
	$LANG = $vars['_lang'];
	
	if( array_key_exists( 'form_action', $_POST )){
		if( count( $_POST['domain_checkbox'] )){
			switch( $_POST['form_action'] ){
			case 'transfer':
				$result = dondominio_mod_transfer_doTransfer( $vars, array_keys( $_POST['domain_checkbox'] ));
				
				if( !is_array( $result )){
					echo "<div class='errorbox'><span class='title'>" . $result['domain'] . ': ' . $LANG['transfer_generic_error'] . "</span></div>";
					break;
				}
				
				if( $result['status'] == 'OK' ){
					echo "<div class='successbox'><span class='title'>" . $result['domain'] . ': ' . $result['message'] . "<span></div>";
					break;
				}
				
				echo "<div class='errorbox'><span class='title'>" . $result['domain'] . ': ' . $result['message'] . "</span></div>";
				
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
	
	//
	// Pagination
	//
	$page = 1;
	
	$items = dd_get_setting( "NumRecordstoDisplay" );
	
	if( array_key_exists( 'page', $_GET ) && $_GET['page'] > 1 ){
		$page = $_GET['page'];
	}
	
	$total_tlds = full_query( "
		SELECT
			count(id)
		FROM tbldomains
		WHERE
			NOT registrar = 'dondominio'
	" );
	
	list( $total ) = mysql_fetch_row( $total_tlds );
	
	$total_pages = ceil( $total / $items );
	
	if( $page > $total_pages ){
		$page = $total_pages;
	}
	
	$start = (( $page - 1 ) * $items );
	
	//
	// Get domains from the database
	//
	$s_domains = "
		SELECT
			id,
			domain
		FROM tbldomains
		WHERE
			NOT registrar = 'dondominio'
		ORDER BY domain ASC
		LIMIT $start,$items
	";
	
	$domains = full_query( $s_domains );
	
	//
	// Results table
	//
	echo "
	<h2>" . $LANG['transfer_title'] . "</h2>
	
	<p>" . $LANG['transfer_info'] . "</p>
	
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
						
						for( $i = 1; $i <= $total_pages; $i++ ){
							echo "
							<option value='$i' ";
							
							if( $i == $page ){
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
						<input class='domains_check_all' type='checkbox' />
					</th>
					
					<th>
						" . $LANG['transfer_domain'] . "
					</th>
					
					<th width='100'>
						" . $LANG['transfer_authcode'] . "
					</th>
				</tr>
			</thead>
			<tbody>
	";
	
	while( list( $id, $domain ) = mysql_fetch_row( $domains )){
		echo "
			<tr>
				<td>
					<input class='domain_checkbox' name='domain_checkbox[$id]' type='checkbox' />
				</td>
				
				<td>
					$domain
				</td>
				
				<td>
					<input type='text' name='authcode[$id]' value='' width='100%' />
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
						" . $LANG['transfer_domain'] . "
					</th>
					
					<th>
						" . $LANG['transfer_authcode'] . "
					</th>
				</tr>
			</tfoot>
		</table>
		
		<br />
		
		" . $LANG['info_with_selected'] . " <button type='submit' name='form_action' value='transfer' class='btn'>" . $LANG['btn_transfer'] . "</button>
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

function dondominio_mod_transfer_doTransfer( array $vars = array(), array $domain_ids = array())
{
	$LANG = $vars['_lang'];
	
	$dondominio = dd_init( $vars );
	
	foreach( $domain_ids as $domain_id ){
		$domain = full_query( "
			SELECT
				*,
				(
					SELECT
						value
					FROM tblcustomfieldsvalues
					WHERE
						relid = C.id
						AND fieldid = ( 
							SELECT
								id
							FROM tblcustomfields
							WHERE
								fieldname = 'VAT Number'
						)
				) AS vatnumber
			FROM tbldomains D
			LEFT JOIN tblclients C ON C.id = D.userid
			WHERE
				D.id = '$domain_id'
		" );
		
		$domain_info = mysql_fetch_array( $domain, MYSQL_ASSOC );
		
		if( !is_array( $domain_info )){
			return array( 'status' => 'ERROR', 'domain' => 'ID #' . $domain_id, 'message' => $LANG['transfer_domain_not_found'] );
		}
		
		/*
		 * Requesting TLD information
		 */
		$domain_components = explode( '.', $domain_info['domain'] );
		
		if( !is_array( $domain_components )){
			return array( 'status' => 'ERROR', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_invalid_domain_name'] );
		}
		
		$tld = $domain_components[ count( $domain_components ) - 1 ];
		
		$tld_info_query = full_query( "SELECT * FROM `mod_dondominio_pricing` WHERE `tld` = '.$tld'" );
		
		$tld_info = mysql_fetch_array( $tld_info_query, MYSQL_ASSOC );
		
		if( !is_array( $tld_info )){
			return array( 'status' => 'ERROR', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_tld_not_found'] );
		}
		
		if( $tld_info['authcode_required'] == '1' && empty( $_POST['authcode'][$domain_id] )){
			return array( 'status' => 'ERROR', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_authcode_required'] );
		}
		
		/*
		 * Get customer information from WHMCS internal API
		 */
		$command = "getclientsdetails";
		$values["clientid"] = $domain_info['userid'];
		$values["stats"] = true;
		$values["responsetype"] = "json";
		
		$client_details = localAPI( $command, $values, '' );
		
		if( !is_array( $client_details )){
			return array( 'status' => 'ERROR', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_client_not_found'] );
		}
		
		if( empty( $domain_info['vatnumber'] )){
			return array( 'status' => 'ERROR', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_vatnumber_empty'] );
		}
		
		/*
		 * Parsing Organization Type
		 */
		$orgType = dondominio_mod_transfer_orgType( $domain_info['vatnumber'] );
		
		/*
		 * Building parameter array for DonDominio's API
		 */
		$params = array(
			'nameservers' => 'keepns',
			'authcode' => '',
			'ownerContactType' => ( $orgType == "1" || $domain_info['country'] != 'ES' ) ? 'individual' : 'organization',
			'ownerContactFirstName' =>$domain_info['firstname'],
			'ownerContactLastName' => $domain_info['lastname'],
			'ownerContactOrgName' => $domain_info['companyname'],
			'ownerContactOrgType' => $orgType,
			'ownerContactIdentNumber' => $domain_info['vatnumber'],
			'ownerContactEmail' => $domain_info['email'],
			'ownerContactPhone' => '+' . $client_details['client']['phonecc'] . '.' . $client_details['client']['phonenumber'],
			'ownerContactAddress' => $domain_info['address1'],
			'ownerContactPostalCode' => $domain_info['postcode'],
			'ownerContactCity' => $domain_info['city'],
			'ownerContactState' => $domain_info['state'],
			'ownerContactCountry' => $client_details['client']['countrycode']
		);
		
		/*
		 * DonDominio API request
		 */
		try{
			$transfer = $dondominio->domain_transfer( $domain_info['domain'], $params );
		}catch( \DonDominioAPI_Domain_TransferNotAllowed $e ){
			/*
			 * Already in the system
			 */
			if( $e->getMessage() == 'Domain in the system' ){
				full_query( "UPDATE tbldomains SET registrar = 'dondominio', status = 'Active' WHERE id = '$domain_id'" );
				return array( 'status' => 'OK', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_already_transferred'] );
			}
			
			return array( 'status' => 'ERROR', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_error'] );
		}catch( \DonDominioAPI_Domain_TransferError $e ){
			return array( 'status' => 'ERROR', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_error'] );
		}
		
		/*
		 * Updating domain registrar on WHMCS database
		 */
		full_query( "UPDATE tbldomains SET registrar = 'dondominio', status = 'Pending Transfer' WHERE id = '$domain_id'" );
		
		return array( 'status' => 'ERROR', 'domain' => $domain_info['domain'], 'message' => $LANG['transfer_success'] );
	}
}

/**
 * Convert organization type to the corresponding code for the API using a VAT Number.
 * @param string $vat VAT Number used to get the code
 * @return string
 */
function dondominio_mod_transfer_orgType( $vatNumber )
{
	$letter = substr( $vatNumber, 0, 1 );
	
	if( is_numeric( $letter )){
		return "1";
	}
	
	switch($letter){
	case 'A':
		return "524";
		break;
	case 'B':
		return "612";
		break;
	case 'C':
		return "560";
		break;
	case 'D':
		return "562";
		break;
	case 'E':
		return "150";
		break;
	case 'F':
		return "566";
		break;
	case 'G':
		return "47";
		break;
	case 'J':
		return "554";
		break;
	case 'P':
		return "747";
		break;
	case 'Q':
		return "746";
		break;
	case 'R':
		return "164";
		break;
	case 'S':
		return "436";
		break;
	case 'U':
		return "717";
		break;
	case 'V':
		return "877";
		break;
	case 'N':
	case 'W':
		return "713";
		break;
	case 'X':
	case 'Y':
	case 'Z':
		return "1";
	}
	
	return "877";
}