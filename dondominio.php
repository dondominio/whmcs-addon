<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 *
 * WHMCS version 5.2.x / 5.3.x
 * @link https://github.com/dondominio/whmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */
 
/**
 * The DonDominio API Client for PHP
 */
if(!class_exists( 'DonDominioAPI' )){
	require_once( "lib/sdk/DonDominioAPI.php" );
}

require_once( "dd_utils.php" );
require_once( "dd_domain_pricing.php" );

if(!defined( "WHMCS" )){
	die( "This file cannot be accessed directly" );
}

/**
 * Return configuration array for WHMCS.
 * @return array
 */
function dondominio_config()
{
	$configarray = array(
		"name"			=> "DonDominio Manager",
		"description"	=> "Advanced features from DonDominio.",
		"version"		=> dd_getVersion(),
		"author"		=> "DonDominio",
		"language"		=> "english",
		"fields"		=> array()
	);
	
	return $configarray;
}

/**
 * Perform a query and check the result.
 * @param string $sql query to send to the database
 * @return array|boolean
 */
function dd_do_query( $sql )
{
	$result = full_query( $sql );
	
	if( !$result ){
		return array(
			'status' => 'error',
			'description' => 'There was a problem activating the DonDominio Manager Addon. Please contact support.'
		);
	}
	
	return true;
}

/**
 * Activation hook for addon.
 * Creates tables in database needed to work.
 * @return array
 */
function dondominio_activate()
{
	//Check if EUR is in currencies; if not, fail
	$currency = full_query( "SELECT id FROM tblcurrencies WHERE code='EUR'" );
	
	if( mysql_num_rows( $currency ) == 0 ){
		return array(
			'status' => 'error',
			'description' => 'The DonDominio API works with Euros (EUR). Please, add this currency to your WHMCS configuration before enabling the Addon.'
		);
	}
	
	//Creating mod_dondominio_pricing
	if( is_array( $result = dd_do_query("
		CREATE TABLE IF NOT EXISTS `mod_dondominio_pricing`
		(
			`id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`tld` VARCHAR(64) NOT NULL,
			`register_price` DECIMAL(10,2) NULL,
			`transfer_price` DECIMAL(10,2) NULL,
			`renew_price` DECIMAL(10,2) NULL,
			`register_range` VARCHAR(128) NULL,
			`transfer_range` VARCHAR(128) NULL,
			`renew_range` VARCHAR(128) NULL,
			`old_register_price` DECIMAL(10,2) NULL,
			`old_transfer_price` DECIMAL(10,2) NULL,
			`old_renew_price` DECIMAL(10,2) NULL,
			`authcode_required` TINYINT(1) NULL,
			`last_update` DATETIME NOT NULL
		)
	"))){
		return $result;
	}
	
	//Creating mod_dondominio_tld_settings
	if( is_array( $result = dd_do_query("
		CREATE TABLE IF NOT EXISTS `mod_dondominio_tld_settings`
		(
			`id` INT(1) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`tld` VARCHAR(64) NOT NULL,
			`ignore` TINYINT(1) NOT NULL,
			`enabled` TINYINT(1) NOT NULL,
			`register_increase` DECIMAL(10,2) NOT NULL DEFAULT 0,
			`register_increase_type` VARCHAR(16) NOT NULL DEFAULT 'fixed',
			`renew_increase` DECIMAL(10,2) NOT NULL DEFAULT 0,
			`renew_increase_type` VARCHAR(16) NOT NULL DEFAULT 'fixed',
			`transfer_increase` DECIMAL(10,2) NOT NULL DEFAULT 0,
			`transfer_increase_type` VARCHAR(16) NOT NULL DEFAULT 'fixed',
			UNIQUE INDEX `unique_tld` (`tld`)
		)
	"))){
		return $result;
	}
	
	//Creating mod_dondominio_settings
	if( is_array( $result = dd_do_query("
		CREATE TABLE IF NOT EXISTS `mod_dondominio_settings`
		(
			`key` VARCHAR(32) NOT NULL PRIMARY KEY,
			`value` VARCHAR(256) NULL
		)
	"))){
		return $result;
	}
	
	//Creating mod_dondominio_watchlist
	if( is_array( $result = dd_do_query("
		CREATE TABLE IF NOT EXISTS `mod_dondominio_watchlist`
		(
			`id` INT(1) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`tld` VARCHAR(64) NOT NULL
		)
	"))){
		return $result;
	}
	
	//Default values
	if( is_array( $result = dd_do_query("
		INSERT INTO `mod_dondominio_settings` (`key`, `value`) VALUES
			('register_increase', '0.00'),
			('transfer_increase', '0.00'),
			('renew_increase', '0.00'),
			('register_increase_type', 'fixed'),
			('transfer_increase_type', 'fixed'),
			('renew_increase_type', 'fixed'),
			('notifications_enabled', '0'),
			('notifications_email', ''),
			('notifications_new_tlds', '0'),
			('notifications_prices', '0'),
			('api_username', ''),
			('api_password', ''),
			('watchlist_mode', 'disabled'),
			('prices_autoupdate', '0')
	"))){
		return $result;
	}
	
	return array(
		'status' => 'success',
		'description' => 'The DonDominio Manager Addon is now ready. Enjoy!'
	);
}

/**
 * Disable hook for addon.
 * Deletes tables from the database.
 * @return array
 */
function dondominio_deactivate()
{
	
	//Removing mod_dondominio_pricing
	if( is_array( $result = dd_do_query("DROP TABLE IF EXISTS `mod_dondominio_pricing`" ))){
		return $result;
	}
	
	//Removing mod_dd_settings
	if( is_array( $result = dd_do_query("DROP TABLE IF EXISTS `mod_dondominio_settings`" ))){
		return $result;
	}
	
	//Removing mod_dd_settings
	if( is_array( $result = dd_do_query("DROP TABLE IF EXISTS `mod_dondominio_watchlist`" ))){
		return $result;
	}
	
	return array('status' => 'success','description' => 'The DonDominio Manager Addon has been successfully disabled.');
}

/**
 * Upgrade function.
 * Performs updates in the database.
 * @param Array $vars Parameters passed by WHMCS
 * @return bool
 */
function dondominio_upgrade($vars)
{
	$version = $vars['version'];
		
	/*
	 * La versión 1.0 no tiene "authcode_required" en "mod_dondominio_pricing"
	 */
	if( $version < 1.1 ){
		$query = "ALTER TABLE `mod_dondominio_pricing` ADD `authcode_required` TINYINT(1) NOT NULL";
		$result = full_query( $query );
	}
	
	# Run SQL Updates for V1.1 to V1.2
	if( $version < 1.2 ){
		$query = "
			CREATE TABLE IF NOT EXISTS `mod_dondominio_tld_settings`
			(
				`id` INT(1) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`tld` VARCHAR(64) NOT NULL,
				`ignore` TINYINT(1) NOT NULL,
				`enabled` TINYINT(1) NOT NULL,
				`register_increase` DECIMAL(10,2) NOT NULL DEFAULT 0,
				`register_increase_type` VARCHAR(16) NOT NULL DEFAULT 'fixed',
				`renew_increase` DECIMAL(10,2) NOT NULL DEFAULT 0,
				`renew_increase_type` VARCHAR(16) NOT NULL DEFAULT 'fixed',
				`transfer_increase` DECIMAL(10,2) NOT NULL DEFAULT 0,
				`transfer_increase_type` VARCHAR(16) NOT NULL DEFAULT 'fixed'
			)
		";
		
		$result = full_query( $query );
		
		$query = "CREATE UNIQUE INDEX `unique_tld` ON `mod_dondominio_tld_settings`(`tld`)";
		
		$result = full_query( $query );
	}
	
	if( $version < 1.6 ){
		$query = "ALTER TABLE `mod_dondominio_tld_settings` ADD `ignore` TINYINT(1) NOT NULL";
		
		$result = full_query( $query );
	}			
	
	return true;
}

/**
 * Check the DonDominio Domain Management Addon version.
 * @return Array|bool
 */
function dondominio_addon_version_check()
{
	$localVersionInfo = file_get_contents( '../modules/addons/dondominio/version.json' );
	$githubVersionInfo = file_get_contents( 'https://raw.githubusercontent.com/dondominio/whmcs-addon/master/version.json' );
	
	// Have we retrieved anything?
	if( empty( $localVersionInfo ) || empty( $githubVersionInfo )){
		return false;
	}
	
	$localJson = json_decode( $localVersionInfo, true );
	$githubJson = json_decode( $githubVersionInfo, true );
	
	// Have we decoded the JSONs correctly?
	if( !is_array( $localJson ) || !is_array( $githubJson )){
		return false;
	}
	
	// Comparing the versions found on the JSONs
	if( version_compare( $localJson['version'], $githubJson['version'] ) < 0 ){
		return $githubJson;
	}
	
	return false;
}

/**
 * Check the DonDominio Registrar Plugin version.
 * @return Array|bool
 */
function dondominio_plugin_version_check()
{
	if( !is_dir( '../modules/registrars/dondominio' )){
		return false;
	}
	
	$localVersionInfo = @file_get_contents( '../modules/registrars/dondominio/version.json' );
	$githubVersionInfo = @file_get_contents( 'https://raw.githubusercontent.com/dondominio/whmcs-plugin/master/version.json' );
	
	// Have we retrieved anything?
	if( empty( $localVersionInfo ) || empty( $githubVersionInfo )){
		return false;
	}
	
	$localJson = json_decode( $localVersionInfo, true );
	$githubJson = json_decode( $githubVersionInfo, true );
	
	// Have we decoded the JSONs correctly?
	if( !is_array( $localJson) || !is_array( $githubJson )){
		return false;
	}
	
	// Comparing versions found on the JSONs
	if( version_compare( $localJson['version'], $githubJson['version'] ) < 0 ){
		return $githubJson;
	}
	
	return false;
}

function dondominio_version_check()
{
	// Getting last time we checked versions
	$last_check = dd_get( 'last_version_check' );
	
	if( !empty( $last_check ) && time() - $last_check < 86400 ){
		return false;
	}
	
	/*
	 * Checking DonDominio Domain Management Addon version.
	 */
	if( $version = dondominio_addon_version_check()){
		echo "
		<a href='https://github.com/dondominio/whmcs-addon'>
			<div style='background-color: #F3F3C8; padding: 10px; border: 2px black solid; font-weight: 600;'>
				<h1>New Addon version available</h1>
				
				<p>A new version of the DonDominio Addon for WHMCS has been released. Regularly updating the plugin is recommended to get all the features
				and avoid future incompatibilities with the DonDominio API.</p>
				
				Click here to download <strong>version " . $version['version'] . " released on " . date( 'd/m/Y', strtotime( $version['releaseDate'] )) . "</strong></a>
			</div>
		</a>
		";
	}
	
	/*
	 * Checking DonDominio Registrar Plugin version.
	 */
	if( $version = dondominio_plugin_version_check()){
		echo "
		<a href='https://github.com/dondominio/whmcs-plugin'>
			<div style='background-color: #F3F3C8; padding: 10px; border: 2px black solid; font-weight: 600;'>
				<h1>DonDominio Registrar Plugin for WHMCS updated</h1>
				
				<p>A new version of the DonDominio Registrar Plugin for WHMCS has been released. Regularly updating the plugin is recommended to get all the features
				and avoid future incompatibilities with the DonDominio API.</p>
				
				<p>Click here to download <strong>version " . $version['version'] . " released on " . date( 'd/m/Y', strtotime( $version['releaseDate'] )) . "</strong></a></p>
			</div>
		</a>
		";
	}
	
	dd_set( 'last_version_check', time());
}

/**
 * Module loader for the DD Addon for WHMCS.
 * @param array $vars Parameters from WHMCS
 */
function dondominio_output( $vars )
{
	$modulelink = $vars['modulelink'];
	
	$version = $vars['version'];
	$LANG = $vars['_lang'];
	
	if(!array_key_exists( 'action', $_REQUEST )){
		$_REQUEST['action'] = 'tlds';
	}
	
	if(!array_key_exists( 'option', $_REQUEST )){
		$_REQUEST['option'] = 'index';
	}
	
	/*
	 * We do not allow to go anywhere before entering API Username & Password.
	 */
	$username = trim( dd_get( 'api_username' ));
	$password = trim( base64_decode( dd_get( 'api_password' )));
	
	if( strlen( $username ) == 0 || strlen( $password ) == 0 ){
		$_REQUEST['action'] = 'settings';
		$_REQUEST['option'] = 'index';
	}
	/* * * */
	
	$action = 'dondominio_mod_' . $_REQUEST['action'] . '_' . $_REQUEST['option'];
	
	$path = dirname( __FILE__ ) . "/dondominio_mod_" . $_REQUEST['action'] . ".php";
	
	if( !file_exists( $path )){
		echo "<h3>Module not found: " . $_REQUEST['action'] . "</h3>";
		return false;
	}
	
	require_once $path;
	
	if( !is_callable( $action )){
		echo "<h3>Action not found: " . $_REQUEST['action'] . '/' . $_REQUEST['option'] . "</h3>";
		return false;
	}
	
	dondominio_version_check();
	
	$action( $vars );
}

/**
 * Build the sidebar for the addon.
 * @param array $vars Parameters from WHMCS
 * @return string
 */
function dondominio_sidebar( $vars )
{
	$modulelink = $vars['modulelink'];
	
	$version = $vars['version'];
	$LANG = $vars['_lang'];
	
	$sidebar = '
		<span class="header">
			<img src="https://www.dondominio.com/images/favicon_appletouch.png" class="absmiddle" width="16" height="16" /> DonDominio Manager
		</span>
		
		<ul class="menu">
			<li><a href="' . $modulelink . '&action=tlds">' . $LANG['menu_tlds_update'] . '</a></li>
			<li><a href="' . $modulelink . '&action=tlds_new">' . $LANG['menu_tlds_new'] . '</a></li>
			<li><a href="' . $modulelink . '&action=domains">' . $LANG['menu_domains'] . '</a></li>
			<li><a href="' . $modulelink . '&action=transfer">' . $LANG['menu_transfer'] . '</a></li>
			<li><a href="' . $modulelink . '&action=import">' . $LANG['menu_import'] . '</a></li>
			<li><a href="' . $modulelink . '&action=suggests">' . $LANG['menu_suggests'] . '</a></li>
			<li><a href="' . $modulelink . '&action=whois">' . $LANG['menu_whois'] . '</a></li>
			<li><a href="' . $modulelink . '&action=settings">' . $LANG['menu_settings'] . '</a></li>
			<li class="divider">&nbsp;</li>
			<li><a href="https://docs.dondominio.com/" target="_api">' . $LANG['menu_help'] . '</a></li>
		</ul>
	';
	
	return $sidebar;
}

?>
