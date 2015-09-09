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
if(!class_exists('DonDominioAPI')){
	require_once("lib/sdk/DonDominioAPI.php");
}

require_once("dd_utils.php");
require_once("dd_domain_pricing.php");

if(!defined("WHMCS")){
	die("This file cannot be accessed directly");
}

/**
 * Return configuration array for WHMCS.
 * @return array
 */
function dondominio_config()
{
	$configarray = array(
		"name" => "DonDominio Manager Addon",
		"description" => "TLD & Bulk Domain management from DonDominio.",
		"version" => "1.2",
		"author" => "DonDominio",
		"language" => "english",
		"fields" => array()
	);
	
	return $configarray;
}

/**
 * Activation hook for addon.
 * Creates tables in database needed to work.
 * @return array
 */
function dondominio_activate()
{
	/**
	 * Perform a query and check the result.
	 * @param string $sql query to send to the database
	 * @return array|boolean
	 */
	function do_query($sql)
	{
		$result = full_query($sql);
		
		if(!$result){
			return array('status' => 'error','description' => 'There was a problem activating the DonDominio Manager Addon. Please contact support.');
		}
		
		return true;
	}
	
	//Check if EUR is in currencies; if not, fail
	$currency = full_query("SELECT id FROM tblcurrencies WHERE code='EUR'");
	
	if(mysql_num_rows($currency) == 0){
		return array('status' => 'error', 'description' => 'The DonDominio API works with Euros (EUR). Please, add this currency to your WHMCS configuration before enabling the Addon.');
	}
	
	//Creating mod_dondominio_pricing
	if(is_array($result = do_query("
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
	if( is_array( $result = do_query("
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
	"))){
		return $result;
	}
	
	//Adding unique index to mod_dondominio_tld_settings
	if( is_array( $result = do_query("
		CREATE UNIQUE INDEX `unique_tld` ON `mod_dondominio_tld_settings`(`tld`)
	"))){
		return $result;
	}
	
	//Creating mod_dondominio_settings
	if(is_array($result = do_query("
		CREATE TABLE IF NOT EXISTS `mod_dondominio_settings`
		(
			`key` VARCHAR(32) NOT NULL PRIMARY KEY,
			`value` VARCHAR(256) NULL
		)
	"))){
		return $result;
	}
	
	//Creating mod_dondominio_watchlist
	if(is_array($result = do_query("
		CREATE TABLE IF NOT EXISTS `mod_dondominio_watchlist`
		(
			`id` INT(1) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`tld` VARCHAR(64) NOT NULL
		)
	"))){
		return $result;
	}
	
	//Default values
	if(is_array($result = do_query("
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
	
	return array('status' => 'success', 'description' => 'The DonDominio Manager Addon is now ready. Enjoy!');
}

/**
 * Disable hook for addon.
 * Deletes tables from the database.
 * @return array
 */
function dondominio_deactivate()
{
	/**
	 * Perform a query and check the result.
	 * @param string $sql query to send to the database
	 * @return array|boolean
	 */
	function do_query($sql)
	{
		$result = full_query($sql);
		
		if(!$result){
			return array('status' => 'error','description' => 'There was a problem disabling the DonDominio Manager Addon. Please contact support.');
		}
		
		return true;
	}
	
	//Removing mod_dondominio_pricing
	if(is_array($result = do_query("DROP TABLE IF EXISTS `mod_dondominio_pricing`"))){
		return $result;
	}
	
	//Removing mod_dd_settings
	if(is_array($result = do_query("DROP TABLE IF EXISTS `mod_dondominio_settings`"))){
		return $result;
	}
	
	//Removing mod_dd_settings
	if(is_array($result = do_query("DROP TABLE IF EXISTS `mod_dondominio_watchlist`"))){
		return $result;
	}
	
	return array('status' => 'success','description' => 'The DonDominio Manager Addon has been successfully disabled.');
}

/**
 * Upgrade function.
 * Performs updates in the database.
 */
function dondominio_upgrade($vars)
{
	$version = $vars['version'];
		
	/*
	 * La versión 1.0 no tiene "authcode_required" en "mod_dondominio_pricing"
	 */
	if ($version < 1.1) {
		$query = "ALTER TABLE `mod_dondominio_pricing` ADD `authcode_required` TINYINT(1) NOT NULL";
		$result = full_query( $query );
	}
	
	# Run SQL Updates for V1.1 to V1.2
	if ($version < 1.2) {
		$query = "
			CREATE TABLE IF NOT EXISTS `mod_dondominio_tld_settings`
			(
				`id` INT(1) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`tld` VARCHAR(64) NOT NULL,
				`ignore` TINYINT(1) NOT NULL,
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
}

/**
 * Module loader for the DD Addon for WHMCS.
 * @param array $vars Parameters from WHMCS
 */
function dondominio_output($vars)
{
	$modulelink = $vars['modulelink'];
	
	$version = $vars['version'];
	$option1 = $vars['option1'];
	$option2 = $vars['option2'];
	$option3 = $vars['option3'];
	$option4 = $vars['option4'];
	$option5 = $vars['option5'];
	$LANG = $vars['_lang'];
	
	if(!array_key_exists('action', $_GET)){
		$_GET['action'] = 'tlds';
	}
	
	if(!array_key_exists('option', $_GET)){
		$_GET['option'] = 'index';
	}
	
	/*
	 * We do not allow to go anywhere before entering API Username & Password.
	 */
	$username = dd_get('api_username');
	$password = dd_get('api_password');
	
	if(empty($username) || empty($password)){
		$_GET['action'] = 'settings';
		$_GET['option'] = 'index';
	}
	/* * * */
	
	$action = 'dondominio_mod_' . $_GET['action'] . '_' . $_GET['option'];
	
	$path = dirname(__FILE__) . "/dondominio_mod_" . $_GET['action'] . ".php";
	
	if(!file_exists($path)){
		echo "<h3>Module not found: " . $_GET['action'] . "</h3>";
		return false;
	}
	
	require_once $path;
	
	if(!is_callable($action)){
		echo "<h3>Action not found: " . $_GET['action'] . '/' . $_GET['option'] . "</h3>";
		return false;
	}
	
	$action($vars);
}

/**
 * Build the sidebar for the addon.
 * @param array $vars Parameters from WHMCS
 * @return string
 */
function dondominio_sidebar($vars)
{
	$modulelink = $vars['modulelink'];
	
	$version = $vars['version'];
	$option1 = $vars['option1'];
	$option2 = $vars['option2'];
	$option3 = $vars['option3'];
	$option4 = $vars['option4'];
	$option5 = $vars['option5'];
	$LANG = $vars['_lang'];
	
	$sidebar = '
		<span class="header">
			<img src="http://www2.dondominio.com/images/favicon_appletouch.png" class="absmiddle" width="16" height="16" /> DonDominio Manager
		</span>
		
		<ul class="menu">
			<li><a href="' . $modulelink . '&action=tlds">' . $LANG['menu_tlds_update'] . '</a></li>
			<li><a href="' . $modulelink . '&action=tlds_new">' . $LANG['menu_tlds_new'] . '</a></li>
			<li><a href="' . $modulelink . '&action=domains">' . $LANG['menu_domains'] . '</a></li>
			<li><a href="' . $modulelink . '&action=import">' . $LANG['menu_import'] . '</a></li>
			<li><a href="' . $modulelink . '&action=settings">' . $LANG['menu_settings'] . '</a></li>
			<li class="divider">&nbsp;</li>
			<li><a href="https://docs.dondominio.com/" target="_api">' . $LANG['menu_help'] . '</a></li>
		</ul>
	';
	
	return $sidebar;
}

?>