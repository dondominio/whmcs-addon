<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: TLD Manager
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
 * TLDs list with prices & actions.
 * @param array $vars Parameters from WHMCS
 */
function dondominio_mod_suggests_index( $vars )
{	
	$LANG = $vars['_lang'];
	
	$enabled = dd_get( "suggests_enabled" );
	$enabled_status = ( $enabled == "1" ) ? $LANG['suggests_enabled'] : $LANG['suggests_disabled'];
	
	echo "
	<h2>" . $LANG['suggests_title'] . "</h2>
	
	<p>" . $LANG['suggests_is_enabled'] . "<strong>" . $enabled_status . "</strong>.</p>
	
	<p>" . $LANG['suggests_change_settings_a'] . " <strong><a href=\"addonmodules.php?module=dondominio&action=settings\">" . $LANG['suggests_change_settings_b'] . "</a></strong> " . $LANG['suggests_change_settings_c'] . ".</p>
	
	<p>" . $LANG['suggests_integration_a'] . " <strong><a href='https://dev.dondominio.com/whmcs/docs/addon/'>" . $LANG['suggests_integration_b'] . "</a></strong> " . $LANG['suggests_integration_c'] . "</p>
	";
}
