<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: TLD Manager
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
 * TLDs list with prices & actions.
 * @param array $vars Parameters from WHMCS
 */
function dondominio_mod_whois_index( $vars )
{
	$module_link = $vars['modulelink'];
	$version = $vars['version'];
	$lang = $vars['_lang'];
	
	if( !strlen( dd_get( 'whois_domain' ))){
		echo "<div class='infobox'><span class='title'>" . $lang['error_whois_domain_empty'] . "</span></div>";
		
		echo "
		<p>
			" . $lang['info_whois_domain'] . "
		</p>
		
		<p>
			<strong><a href='addonmodules.php?module=dondominio&action=settings'>" . $lang['info_whois_settings'] . "</a></strong>
		</p>
		";
		
		return false;
	}
	
	if( (int) dd_get_whmcs_version() >= 7 ){
		include_once( 'dondominio_mod_whois_7.php' );
		dondominio_mod_whois_7_index( $vars );
	}else{
		include_once( 'dondominio_mod_whois_legacy.php' );
		dondominio_mod_whois_legacy_index( $vars );
	}
	
}
