<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: TLD Cache
 * WHMCS version 5.2.x / 5.3.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */
 
if( !defined( "WHMCS" )){
	die("This file cannot be accessed directly");
}

/**
 * Action: Index.
 * List of TLDs available to create on WHMCS.
 * @param array $vars Parameters from WHMCS
 */
function dondominio_mod_tlds_new_index( $vars )
{
	if(array_key_exists( 'tld_create', $_POST )){
		dondominio_mod_tlds_create( $vars );
		dd_create_currency_placeholders();
		dd_update_currencies();
	}
	
	if( array_key_exists( 'form_action', $_POST )){
		switch( $_POST['form_action'] ){
		case 'create':
			dondominio_mod_tlds_create( $vars );
			dd_create_currency_placeholders();
			dd_update_currencies();
			
			break;
		}
	}
	
	$LANG = $vars['_lang'];
	
	if( !array_key_exists( 'filter_tld', $_GET )){
		$_GET['filter_tld'] = '';
	}
	
	/*
	 * Pagination.
	 */
	$page = 1;
	
	$items = dd_get_setting( "NumRecordstoDisplay" );
	
	if( array_key_exists( 'page', $_GET ) && $_GET['page'] > 1 ){
		$page = $_GET['page'];
	}
	
	$total_tlds = full_query( "
		SELECT
			count(id)
		FROM mod_dondominio_pricing
		WHERE
			tld NOT IN (SELECT extension FROM tbldomainpricing)
			AND tld LIKE '%" . $_GET['filter_tld'] . "%'
	" );
	
	list( $total ) = mysql_fetch_row( $total_tlds );
	
	$total_pages = ceil( $total / $items );
	
	if( $page > $total_pages ){
		$page = $total_pages;
	}
	
	$start = (( $page - 1 ) * $items );
	/* *** */
	
	$s_tlds = "
		SELECT
			id,
			tld as extension,
			register_price,
			transfer_price,
			renew_price
		FROM mod_dondominio_pricing
		WHERE
			tld NOT IN (SELECT extension FROM tbldomainpricing)
			AND tld LIKE '%" . $_GET['filter_tld'] . "%'
		ORDER BY
			tld ASC
		LIMIT
			$start, $items
	";
	
	$tlds = full_query( $s_tlds );
	
	echo "
	<h2>" . $LANG['tld_new_title'] . "</h2>
	
	<p>" . $LANG['tld_new_info'] . "</p>
	
	<div id='tabs'>
		<ul class='nav nav-tabs admin-tabs'>
			<li id='tab0' class='tab'>
				<a href='javascript:;'>
					" . $LANG['filter_title'] . "
				</a>
			</li>
		</ul>
	</div>
	
	<div id='tab0box' class='tabbox'>
		<div id='tab_content'>
			<form action='addonmodules.php' method='get'>
				<input type='hidden' name='module' value='dondominio' />
				<input type='hidden' name='action' value='tlds_new' />
				<input type='hidden' name='page' value='$page' />
				
				<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
					<tbody>
						<tr>
							<td width='15%' class='fieldlabel'>
								" . $LANG['filter_tld'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='filter_tld' value='" . $_GET['filter_tld'] . "' />
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
		<input type='hidden' name='action' value='tlds_new' />
	";
	
	if( array_key_exists( 'filter_tld', $_GET )){
		echo "
		<input type='hidden' name='filter_tld' value='" . $_GET['filter_tld'] . "' />
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
					
					<th width='120'>
						" . $LANG['tld_register'] . "
					</th>
					
					<th width='120'>
						" . $LANG['tld_transfer'] . "
					</th>
					
					<th width='120'>
						" . $LANG['tld_renew'] . "
					</th>
					
					<th width='20'>
					
					</th>
				</tr>
			</thead>
			<tbody>
	";
	
	while( list(
		$tld_id,
		$tld,
		$register_price,
		$transfer_price,
		$renew_price
	) = mysql_fetch_row( $tlds )){
		if( !is_numeric( $register_price )){
			$register_price = $LANG['tld_not_available'];
		}
		
		if( !is_numeric( $transfer_price )){
			$transfer_price = $LANG['tld_not_available'];
		}
		
		if( !is_numeric( $renew_price )){
			$renew_price = $LANG['tld_not_available'];
		}
		
		echo "
			<tr>
				<td>
					<input class='tld_checkbox' name='tld[$tld]' type='checkbox' />
				</td>
				
				<td>
					$tld
				</td>
				
				<td>
					$register_price &nbsp;
				</td>
				
				<td>
					$transfer_price &nbsp;
				</td>
				
				<td>
					$renew_price &nbsp;
				<td>
					<a href='#'>
						<input type='image' name='tld_create[$tld]' value='on' src='images/icons/add.png' width='16' height='16' border='0' alt='" . $LANG['btn_add'] . "' />
					</a>
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
					
					<th width='120'>
						" . $LANG['tld_register'] . "
					</th>
					
					<th width='120'>
						" . $LANG['tld_transfer'] . "
					</th>
					
					<th width='120'>
						" . $LANG['tld_renew'] . "
					</th>
					
					<th width='20'>
					
					</th>
				</tr>
			</tfoot>
		</table>
		
		<br />
		
		" . $LANG['info_with_selected'] . " <button type='submit' name='form_action' value='create' class='btn'>" . $LANG['btn_create_selected'] . "</button>
	</form>
	
	<p align='center'>
	";
	
	if( array_key_exists( 'filter_tld', $_GET )){
		$filter_var = '&filter_tld=' . $_GET['filter_tld'];
	}else{
		$filter_var = '';
	}
	
	if( $page > 1 ){
		echo "
		<a href='addonmodules.php?module=dondominio&action=tlds_new$filter_var&page=" . ($page - 1) . "'>« " . $LANG['pagination_previous'] . "</a>
		";
	}else{	
		echo "
			« " . $LANG['pagination_previous'] . "
		";
	}
	
	echo "&nbsp;";
	
	if( $page < $total_pages ){
		echo "
		<a href='addonmodules.php?module=dondominio&action=tlds_new$filter_var&page=" . ($page + 1) . "'>" . $LANG['pagination_next'] . "»</a>
		";
	}else{	
		echo "
			" . $LANG['pagination_next'] . "»
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
 * Create the selected TLDs in WHMCS.
 * @param array $vars Parameters from WHMCS
 * @return boolean
 */
function dondominio_mod_tlds_create( $vars )
{
	$LANG = $vars[ '_lang' ];
	
	$dondominio = dd_init();
	
	if( array_key_exists( 'tld_create', $_POST)) {
		$_POST[ 'tld' ] = $_POST[ 'tld_create' ];
	}
	
	if( !array_key_exists( 'tld', $_POST )){
		echo "
			<div class='infobox'>
				" . $LANG[ 'tld_no_selected' ] . "
			</div>
		";
		
		return false;
	}
	
	$tlds_ids = array_keys( $_POST[ 'tld' ] );
	
	$added = array();
	
	foreach( $tlds_ids as $tld_id ){
		$check = full_query( "SELECT id FROM tbldomainpricing WHERE extension = '" . $tld_id . "'" );
		
		if( mysql_num_rows( $check ) == 0 ){
			$authcode = '';
			
			$q_authcode = full_query( "SELECT authcode_required FROM mod_dondominio_pricing WHERE tld = '" . $tld_id . "'" );
			
			list( $authcode_required ) = mysql_fetch_row( $q_authcode );
			
			$authcode = ( $authcode_required == 1 ) ? 'on' : '';
			
			if ( (int) dd_get_whmcs_version() >= 7 ) {
                if ( (int) dd_get_whmcs_sub_version() >= 6 ) {
                    $s_insert = "
						INSERT INTO tbldomainpricing
						VALUES (
							NULL,
							'$tld_id',
							0,
							0,
							0,
							'$authcode',
							'dondominio',
							0,
							'none',
							-1,
							0.00,
							-1,
							0.00,
							NOW(),
							NOW()
						)
					";
                } else {
                    $s_insert = "
						INSERT INTO tbldomainpricing
						VALUES (
							NULL,
							'$tld_id',
							'',
							'',
							'',
							'$authcode',
							'dondominio',
							0,
							'none'
						)
					";
                }
            } else {
                $s_insert = "
					INSERT INTO tbldomainpricing
					VALUES (
						NULL,
						'$tld_id',
						'',
						'',
						'',
						'$authcode',
						'dondominio',
						0
					)
				";
            }
			
			full_query( $s_insert );
			
			dd_domain_pricing( $tld_id );
			
			$added[] = '- ' . $tld_id;
		}
	}
	
	if( count( $added ) > 0 ){
		echo "
			<div class='successbox'>
				" . $LANG['tld_created_success_info'] . "<br>
				" . implode('<br />', $added) . "
			</div>
		";
	}else{
		echo "
			<div class='infobox'>
				" . $LANG['tld_created_no_tlds'] . "
			</div>
		";
	}
	
	return true;
}

?>
