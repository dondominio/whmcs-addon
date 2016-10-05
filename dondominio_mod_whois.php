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
function dondominio_mod_whois_index( $vars )
{	
	
	if( (int) dd_get_whmcs_version() >= 7 ){
		include_once( 'dondominio_mod_whois_7.php' );
		dondominio_mod_whois_7_index( $vars );
	}else{
		include_once( 'dondominio_mod_whois_legacy.php' );
		dondominio_mod_whois_7_legacy( $vars );
	}
	
}
