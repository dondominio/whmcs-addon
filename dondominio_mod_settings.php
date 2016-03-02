<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: Settings
 * WHMCS version 5.2.x / 5.3.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */
 
if(!defined('WHMCS')){
	die('This file cannot be accessed directly');
}

/**
 * Action: Index
 * Settings screen.
 * @param array $vars Parameters from WHMCS
 */
function dondominio_mod_settings_index($vars)
{
	$LANG = $vars['_lang'];
	
	$tab0 = 'tabselected';
	$tab1 = '';
	$tab2 = '';
	
	$username = dd_get('api_username');
	$password = base64_decode( dd_get( 'api_password' ));
	
	if( strlen( $username ) == 0 || strlen( $password ) == 0 ){
		$initial_sync = true;
	}
	
	$tab0 = '';
	$tab1 = '';
	$tab2 = '';
	$tab3 = '';
	
	if(count($_POST)){
		switch($_POST['tab']){
		
		// API settings
		case 0:
			dd_set( 'api_username',				(string) $_POST['api_username'] );
			dd_set( 'api_password',				(string) base64_encode( $_POST['api_password'] ));
			
			if($initial_sync){
				dd_initial_sync();
			}
			
			$tab0 = 'tabselected';
			break;
			
		// Price settings
		case 1:
			dd_set( "prices_autoupdate",		( $_POST['prices_update_cron'] == 'on' ) ? '1' : '0' );
			dd_set( "register_increase",		floatval($_POST['prices_register_add'] ));
			dd_set( "transfer_increase",		floatval($_POST['prices_transfer_add'] ));
			dd_set( "renew_increase",			floatval($_POST['prices_renew_add'] ));
			
			dd_set( "register_increase_type",	$_POST['prices_register_type'] );
			dd_set( "transfer_increase_type",	$_POST['prices_transfer_type'] );
			dd_set( "renew_increase_type",		$_POST['prices_renew_type'] );
			
			$tab1 = 'tabselected';
			break;
			
		// Notifications settings
		case 2:
			dd_set( "notifications_enabled",	( $_POST['notifications_enabled'] == 'on' ) ? '1' : '0' );
			dd_set( "notifications_email",		$_POST['notifications_email'] );
			dd_set( "notifications_new_tlds",	( $_POST['notifications_new_tld'] == 'on' ) ? '1' : '0' );
			dd_set( "notifications_prices",		( $_POST['notifications_prices'] == 'on' ) ? '1' : '0' );
			dd_set( "watchlist_mode",			$_POST['watchlist'] );
			
			full_query("DELETE FROM mod_dondominio_watchlist");
			
			foreach($_POST['selected_tlds'] as $tld){
				$values[] = "('$tld')";
			}
			
			$sql = "INSERT INTO mod_dondominio_watchlist(tld) VALUES " . implode(',', $values);
			
			full_query($sql);
			
			$tab2 = 'tabselected';
			break;
			
		// Cache settings
		case 3:
			if($_POST['cache_rebuild'] == 'on'){
				dd_initial_sync();
			}
			
			$tab3 = 'tabselected';
			break;
			
		// Domain Suggestions
		case 4:
			dd_set( "suggests_enabled",			( $_POST['suggests_enabled'] == 'on' ) ? '1' : '0' );
			dd_set( "suggests_language",		$_POST['language'] );
			dd_set( 'suggests_tlds',			implode( ',', $_POST['tlds'] ));
			
			$tab4 = 'tabselected';
			break;
			
		// Whois Proxy
		case 5:
			dd_set( 'whois_domain',				trim( $settings['domain'] ));
			dd_set( 'whois_ip',					trim( $settings['ip'] ));
			
			$tab5 = 'tabselected';
			break;
		}
		
		echo "
			<div class='successbox'>
				<span class='title'>" . $LANG['settings_save_success'] . "</span>
			</div>
		";
	}else{
		$_POST['tab'] = 0;
	}
	
	$username = dd_get('api_username');
	$password = base64_decode( dd_get( 'api_password' ));
	
	if( strlen( $username ) == 0 || strlen( $password ) == 0 ){
		echo "
			<div class='infobox'>
				" . $LANG['settings_api_required'] . "
			</div>
		";
	}
	
	//Price increase type for domain registering
	$register_increase_type = dd_get("register_increase_type");
	$register_increase_type_fixed = ($register_increase_type == 'fixed') ? "checked='checked'" : "";
	$register_increase_type_percent = ($register_increase_type == 'percent') ? "checked='checked'" : "";
	
	//Price increase type for domain transfer
	$transfer_increase_type = dd_get("transfer_increase_type");
	$transfer_increase_type_fixed = ($transfer_increase_type == 'fixed') ? "checked='checked'" : "";
	$transfer_increase_type_percent = ($transfer_increase_type == 'percent') ? "checked='checked'" : "";
	
	//Price increase type for domain renewal
	$renew_increase_type = dd_get("renew_increase_type");
	$renew_increase_type_fixed = ($renew_increase_type == 'fixed') ? "checked='checked'" : "";
	$renew_increase_type_percent = ($renew_increase_type == 'percent') ? "checked='checked'" : "";
	
	//Notifications enabled
	$notifications_enabled = dd_get("notifications_enabled");
	$notifications_enabled_checkbox = ($notifications_enabled == '1') ? "checked='checked'" : "";
	
	//Types of notifications
	$notifications_new_tlds = dd_get("notifications_new_tlds");
	$notifications_new_tlds_checkbox = ($notifications_new_tlds == '1') ? "checked='checked'" : "";
	
	$notifications_prices = dd_get("notifications_prices");
	$notifications_prices_checkbox = ($notifications_prices == '1') ? "checked='checked'" : "";
	
	//Price autoupdate
	$prices_autoupdate = dd_get("prices_autoupdate");
	$prices_update_cron = ($prices_autoupdate == '1') ? "checked='checked'" : "";
	
	$tlds = full_query("SELECT tld FROM mod_dondominio_pricing WHERE tld NOT IN (SELECT tld FROM mod_dondominio_watchlist) ORDER BY tld ASC");
	$selected_tlds = full_query("SELECT tld FROM mod_dondominio_watchlist ORDER BY tld ASC");
	
	echo "
	<h2>" . $LANG['settings_title'] . "</h2>
	
	<br />
	
	<div id='tabs'>
		<ul class='nav nav-tabs admin-tabs' role='tablist'>
			<li id='tab0' class='tab $tab0'>
				<a href='javascript:;'>" . $LANG['settings_api_title'] . "</a>
			</li>
			
			<li id='tab1' class='tab $tab1'>
				<a href='javascript:;'>" . $LANG['settings_prices_title'] . "</a>
			</li>
			
			<li id='tab2' class='tab $tab2'>
				<a href='javascript:;'>" . $LANG['settings_notifications_title'] . "</a>
			</li>
			
			<li id='tab3' class='tab $tab3'>
				<a href='javascript:;'>" . $LANG['settings_cache_title'] . "</a>
			</li>
			
			<li id='tab4' class='tab $tab4'>
				<a href='javascript:;'>" . $LANG['settings_suggests_title'] . "</a>
			</li>
			
			<li id='tab5' class='tab $tab5'>
				<a href='javascript:;'>" . $LANG['settings_whois_title'] . "</a>
			</li>
		</ul>
	</div>
	
	<form id='settings' method='post' action=''>
		<!-- API Settings -->
		<div id='tab0box' class='tabbox'>
			<div id='tab_content'>
				<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
					<tbody>
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_api_username'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='api_username' value='" . dd_get("api_username") . "' />
								" . $LANG['settings_api_username_info'] . "
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_api_password'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='api_password' value='" . base64_decode( dd_get( "api_password" )) . "' />
								" . $LANG['settings_api_password_info'] . "
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<!-- Price Adjustment -->
		<div id='tab1box' class='tabbox'>
			<div id='tab_content'>
				<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
					<tbody>
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_prices_update_cron'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='checkbox' name='prices_update_cron' $prices_update_cron /> " . $LANG['settings_prices_update_cron_info']. "
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_prices_register_add'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='prices_register_add' size='20' value='" . dd_get("register_increase") . "' />
								
								<label><input type='radio' name='prices_register_type' value='fixed' $register_increase_type_fixed> " . $LANG['settings_prices_type_fixed'] . "</label>
								<label><input type='radio' name='prices_register_type' value='percent' $register_increase_type_percent> " . $LANG['settings_prices_type_percent'] . "</label>
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_prices_transfer_add'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='prices_transfer_add' size='20' value='" . dd_get("transfer_increase") . "' />
								
								<label><input type='radio' name='prices_transfer_type' value='fixed' $transfer_increase_type_fixed> " . $LANG['settings_prices_type_fixed'] . "</label>
								<label><input type='radio' name='prices_transfer_type' value='percent' $transfer_increase_type_percent> " . $LANG['settings_prices_type_percent'] . "</label>
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_prices_renew_add'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='prices_renew_add' size='20' value='" . dd_get("renew_increase") . "' />
								
								<label><input type='radio' name='prices_renew_type' value='fixed' $renew_increase_type_fixed> " . $LANG['settings_prices_type_fixed'] . "</label>
								<label><input type='radio' name='prices_renew_type' value='percent' $renew_increase_type_percent> " . $LANG['settings_prices_type_percent'] . "</label>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<!-- Notifications -->
		<div id='tab2box' class='tabbox'>
			<div id='tab_content'>
				<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
					<tbody>
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_notifications_enable'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='checkbox' name='notifications_enabled' $notifications_enabled_checkbox />
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_notifications_email'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='email' name='notifications_email' value='" . dd_get("notifications_email") . "' size='35' />
								" . $LANG['settings_notifications_email_info'] . "
							</td>
						</tr>
						
						<tr>
							<td class='fieldarea' colspan='2'>
								&nbsp;
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_notifications_select'] . "
							</td>
							
							<td class='fieldarea'>
								<label><input type='checkbox' name='notifications_new_tld' $notifications_new_tlds_checkbox /> " . $LANG['settings_notifications_new_tld'] . "</label><br />
								<label><input type='checkbox' name='notifications_prices' $notifications_prices_checkbox /> " . $LANG['settings_notifications_prices_updated'] . "</label>
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_watch_ignore'] . "
							</td>
							
							<td class='fieldarea'>
								<label><input type='radio' name='watchlist'"; if(dd_get("watchlist_mode") == "disable") echo "checked='checked'"; echo " value='disable' /> " . $LANG['settings_watch_ignore_disable'] . "</label>
								<label><input type='radio' name='watchlist'"; if(dd_get("watchlist_mode") == "watch") echo "checked='checked'"; echo "  value='watch' /> " . $LANG['settings_watch_ignore_watch'] . "</label>
								<label><input type='radio' name='watchlist'"; if(dd_get("watchlist_mode") == "ignore") echo "checked='checked'"; echo "  value='ignore' /> " . $LANG['settings_watch_ignore_ignore'] . "</label>
								
								<br /><br />
								
								<table border='0'>
									<tbody>
										<tr>
											<td width='200'>
												" . $LANG['settings_watch_ignore_available'] . ":<br /><br />
												<select id='tlds_available' name='all_tlds[]' multiple size='20' style='width: 100%;'>
												";
												
												while(list($available_tld) = mysql_fetch_row($tlds)){
													echo "
													<option value='$available_tld'>$available_tld</option>
													";
												}
												
												echo "
												</select>
											</td>
											
											<td style='vertical-align: middle; padding: 10px;'>
												<input id='to_the_right' type='button' value='>>' />
												<br /><br />
												<input id='to_the_left' type='button' value='<<' />
											</td>
											
											<td width='200'>
												" . $LANG['settings_watch_ignore_active'] . ":<br /><br />
												<select id='tlds_selected' name='selected_tlds[]' multiple size='20' style='width: 100%;'>
												";
												
												while(list($available_tld) = mysql_fetch_row($selected_tlds)){
													echo "
													<option value='$available_tld'>$available_tld</option>
													";
												}
												
												echo "
												</select>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		";
		
		$q_cache_info = full_query("
			SELECT
				DATE_FORMAT(MAX(last_update), '%d/%m/%Y %H:%i:%S'),
				COUNT(id)
			FROM mod_dondominio_pricing
		");
		
		list($last_update, $total_tlds) = mysql_fetch_row($q_cache_info);
		
		// Choose selected language
		$lang_selected[ dd_get( 'suggests_language' ) ] = "selected=\"selected\"";
		
		// Choose selected TLDs
		$tlds = explode( ',', dd_get( 'suggests_tlds' ));
		
		foreach( $tlds as $selected_tld ){
			$tlds_selected[ $selected_tld ] = "selected=\"selected\"";
		}
		
		//Suggestions enabled
		if( dd_get( "suggests_enabled" ) == "1" ){
			$suggests_enabled = "checked=\"checked\"";
		}
		
		echo "
		<!-- Notifications -->
		<div id='tab3box' class='tabbox'>
			<div id='tab_content'>
				<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
					<tbody>
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_cache_last_update'] . "
							</td>
							
							<td class='fieldarea'>
								$last_update
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_cache_total'] . "
							</td>
							
							<td class='fieldarea'>
								$total_tlds
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_cache_rebuild'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='checkbox' name='cache_rebuild' />
								" . $LANG['settings_cache_rebuild_info'] . "
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<div id='tab4box' class='tabbox'>
			<div id='tab_content'>
				<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
					<tbody>	
						<tr>
							<td class='fieldlabel'>
								" . $LANG['settings_suggests_enable'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='checkbox' name='suggests_enabled' $suggests_enabled />
							</td>
						</tr>
						
						<tr>
							<td class=\"fieldlabel\">
								" . $LANG['suggests_lang'] . "
							</td>
							
							<td class=\"fieldarea\">
								<select name=\"language\">
									<option value=\"en\" " . $lang_selected['en'] . ">" . $LANG['lang_en'] . "</option>
									<option value=\"es\" " . $lang_selected['es'] . ">" . $LANG['lang_es'] . "</option>
									<option value=\"zh\" " . $lang_selected['zh'] . ">" . $LANG['lang_zh'] . "</option>
									<option value=\"fr\" " . $lang_selected['fr'] . ">" . $LANG['lang_fr'] . "</option>
									<option value=\"de\" " . $lang_selected['de'] . ">" . $LANG['lang_de'] . "</option>
									<option value=\"kr\" " . $lang_selected['kr'] . ">" . $LANG['lang_kr'] . "</option>
									<option value=\"pt\" " . $lang_selected['pt'] . ">" . $LANG['lang_pt'] . "</option>
									<option value=\"tr\" " . $lang_selected['tr'] . ">" . $LANG['lang_tr'] . "</option>
								</select>
							</td>
						</tr>
						
						<tr>
							<td class=\"fieldlabel\">
								" . $LANG['suggests_tlds'] . "
							</td>
							
							<td class=\"fieldarea\">
								<select multiple=\"multiple\" name=\"tlds[]\">
									<option value=\"com\" " . $tlds_selected['com'] . ">.com</option>
									<option value=\"net\" " . $tlds_selected['net'] . ">.net</option>
									<option value=\"tv\" " . $tlds_selected['tv'] . ">.tv</option>
									<option value=\"cc\" " . $tlds_selected['cc'] . ">.cc</option>
									<option value=\"es\" " . $tlds_selected['es'] . ">.es</option>
									<option value=\"org\" " . $tlds_selected['org'] . ">.org</option>
									<option value=\"info\" " . $tlds_selected['info'] . ">.info</option>
									<option value=\"biz\" " . $tlds_selected['biz'] . ">.biz</option>
									<option value=\"eu\" " . $tlds_selected['eu'] . ">.eu</option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<div id='tab5box' class='tabbox'>
			<div id='tab_content'>
				<table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
					<tbody>
						<tr>
							<td class='fieldlabel' width='200'>
								" . $LANG['settings_whois_domain'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='domain' value='" . dd_get( 'whois_domain' ) . "' size='35'><br />" . $lang['config_domain_info'] . "
							</td>
						</tr>
						
						<tr>
							<td class='fieldlabel' width='200'>
								" . $LANG['settings_whois_ip'] . "
							</td>
							
							<td class='fieldarea'>
								<input type='text' name='ip' value='" . dd_get( 'whois_ip' ) . "' size='35'><br />" . $lang['config_ip_info'] . "
								<span class='help'>" . $LANG['settings_whois_ip_info'] . "</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<p align='center'>
			<button action='submit' name='submit_button' id='settings_submit' class='btn'>" . $LANG['btn_save'] . "</button>
		</p>
		
		<input type='hidden' name='tab' id='tab' value='" . $_POST['tab'] . "'>
	</form>
	
	<script>
	$('#to_the_right').click(function(e){
		var selected = $('#tlds_available OPTION:selected');
		
		$.each(selected, function(key, item){
			$('#tlds_selected').append(item);
		});
	});
	
	$('#settings').submit(function(e){
		e.preventDefault();
		
		$('#tlds_selected option').attr('selected', 'selected');
		
		this.submit();
	});
	
	$('#to_the_left').click(function(e){
		var selected = $('#tlds_selected OPTION:selected');
		
		$.each(selected, function(key, item){
			$('#tlds_available').append(item);
		});
	});
	
	$('.tabbox').css('display','none');
	
	var selectedTab;
	
	$('.tab').click(function(){
		var elid = $(this).attr('id');
		$('.tab').removeClass('tabselected');
		$('#'+elid).addClass('tabselected');
		if (elid != selectedTab) {
			$('.tabbox').slideUp();
			$('#'+elid+'box').slideDown();
			selectedTab = elid;
		}
		$('#tab').val(elid.substr(3));
	});
	
	selectedTab = 'tab" . $_POST['tab'] . "';
	
	$('#tab" . $_POST['tab'] . "').addClass('tabselected');
	$('#tab" . $_POST['tab'] . "box').css('display','');
	</script>
	";
}
