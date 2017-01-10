<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: English langfile
 * WHMCS version 5.2.x / 5.3.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */

$_ADDONLANG = array(
	//Buttons
	'btn_edit' => 'Edit',
	'btn_add' => 'Add',
	'btn_delete' => 'Delete',
	'btn_update_selected' => 'Update Selected',
	'btn_delete_selected' => 'Delete Selected',
	'btn_add_selected' => 'Add Selected',
	'btn_save' => 'Save changes',
	'btn_back' => 'Go back',
	'btn_transfer' => 'Transfer to DonDominio/MrDomain',
	
	//Info
	'info_with_selected' => 'With selected:',
	'info_no_results' => 'No Records Found',
	'info_errors' => 'Error found:',
	'info_too_much_requests' => 'Take into account that this feature may make a big amount of requests to the API, and thus take some time to complete. Depending on your server configuration you may run into timeout limits. To avoid these problems, limit the amount of domains being sent each time using the filters and selecting only the domains that need updates.',
	
	//Links
	'link_more_info' => 'More information',
	
	//Menu
	'menu_tlds_update' => 'Price update',
	'menu_tlds_new' => 'Available TLDs',
	'menu_domains' => 'Manage domains',
	'menu_import' => 'Import domains',
	'menu_transfer' => 'Transfer domains',
	'menu_suggests' => 'Domain suggestions',
	'menu_whois' => 'Whois Proxy',
	'menu_settings' => 'Settings',
	'menu_help' => 'Help & Documentation',
	
	//Filter
	'filter_title' => 'Filter/Search',
	'filter_domain' => 'Domain Name',
	'filter_tld' => 'TLD',
	'filter_status' => 'Status',
	'filter_registrar' => 'Registrar',
	'filter_any' => 'Any',
	'filter_search' => 'Search',
	'filter_pending' => 'Pending',
	'filter_pending_transfer' => 'Pending Transfer',
	'filter_active' => 'Active',
	'filter_expired' => 'Expired',
	'filter_cancelled' => 'Cancelled',
	'filter_fraud' => 'Fraud',
	
	//Pagination
	'pagination_results_found' => 'Results found',
	'pagination_page' => 'Page',
	'pagination_of' => 'of',
	'pagination_go' => 'Go',
	'pagination_go_to' => 'Go to Page:',
	'pagination_previous' => 'Previous page',
	'pagination_next' => 'Next page',
	
	//TLDs
	'tld_title' => 'Price update',
	'tld_info' => 'These are the TLDs currently installed on your WHMCS. You can update their price information and switch them to autoregister using the DonDominio API, if you have the DonDominio Registrar Addon installed.',
	'tld_new_title' => 'Available TLDs',
	'tld_new_info' => 'These are the TLDs available to configure in your WHMCS installation. When you add them to WHMCS, they will be created with the current price information. This list is updated each time the Cron runs.',
	'tld_create_title' => 'Add new TLDs',
	'tld_tld' => 'TLD',
	'tld_registrar' => 'Registrar',
	'tld_no_selected' => 'No TLD selected',
	'tld_created_success_info' => 'The following TLDs have been added:',
	'tld_created_no_tlds' => 'No new TLDs have been added; all TLDs in sync',
	'tld_prices_success' => 'Domain prices updated correctly for the following extensions:',
	'tld_update_success' => 'The following TLDs have been updated:',
	'btn_prices_selected' => 'Update prices',
	'btn_registrar_selected' => 'Switch Registrar to DonDominio',
	'btn_create_selected' => 'Add to WHMCS',
	'tld_register' => 'Register',
	'tld_transfer' => 'Transfer',
	'tld_renew' => 'Renew',
	'tld_not_available' => 'Not Available',
	'tld_regenerate' => 'Rebuild TLD cache',
	
	//Domains
	'domains_title' => 'Manage domains',
	'domains_info' => 'These are the domains currently registered on your WHMCS installation. You can update their information using the DonDominio API and configure them to use the DonDominio Registrar addon, if installed. You can also update their contact information using a DonDominio Contact ID.',
	'domains_domain' => 'Domain',
	'domains_status' => 'Status',
	'domains_registrar' => 'Registrar',
	'domains_set_owner' => 'Update Owner Contact',
	'domains_set_admin' => 'Update Admin Contact',
	'domains_set_tech' => 'Update Tech Contact',
	'domains_set_billing' => 'Update Billing Contact',
	'domains_set_dondominio' => 'Switch registrar to DonDominio',
	'domains_contact_id' => 'DonDominio Contact ID',
	'domains_operation_complete' => 'Operation completed',
	'domains_no_domains_selected' => 'No domains selected',
	'domains_registrar_success' => 'Registrar switched successfully for the following domains',
	'domains_price_update_success' => 'The price for the following domains has been updated successfully',
	'domains_price_no_changes' => 'No changes were made to domains',
	'domains_contacts_error' => 'There was an error when updating the contacts',
	'domains_contacts_success' => 'The following domains have been updated correctly',
	'domains_error_dondominio_id' => 'You need to specify a DonDominio Contact ID to continue',
	'domains_requests' => 'Requests',
	'domains_success' => 'Success',
	'domains_errors' => 'Errors',
	'domains_sync' => 'Update information from DonDominio',
	'domains_price' => 'Update renewal price',
	'domains_sync_success' => 'Domains synced successfully',
	'domains_price_errors' => 'The following domains couldn\'t be updated',
	'domains_tld_price_not_found' => 'The price for the TLD could not be found',
	'domains_eur_not_found' => 'The currency Euro could not be found',
	'domains_tld_not_valid' => 'The TLD could not be found',	
	
	//Import
	'import_title' => 'Import domains',
	'import_info' => 'These are the domains that exist in your DonDominio account. If a domain is not on your WHMCS installation, you may import its information and assign it to an existing customer.',
	'import_btn_import' => 'Import to WHMCS and assign to selected customer',
	'import_imported' => 'Imported',
	'import_not_imported' => 'Not Imported',
	'import_success' => 'The following domains have been imported:',
	'import_completed_not_imported' => 'The following domains were already in the database:',
	'import_error' => 'The following couldn\'t be imported because of an error:',
	
	//Transfer
	'transfer_title' => 'Transfer domains',
	'transfer_info' => 'Use this option to transfer domains to DonDominio/MrDomain from other registrars',
	'transfer_domain' => 'Domain name',
	'transfer_authcode' => 'Authcode/EPP',
	'transfer_authcode_required' => 'This domain extension requires an Authcode/EPP to transfer domains',
	'transfer_generic_error' => 'There was an error while starting the transfer',
	'transfer_domain_not_found' => 'The request domain could not be found on WHMCS',
	'transfer_invalid_domain_name' => 'This domain does not have a valid domain name',
	'transfer_tld_not_found' => 'This domain extension is not supported by DonDominio/MrDomain',
	'transfer_client_not_found' => 'The customer could not be found on WHMCS',
	'transfer_vatnumber_empty' => 'This customer\'s VAT Number is empty; could not continue',
	'transfer_already_transferred' => 'This domain has been already transferred to DonDominio/MrDomain',
	'transfer_error' => 'There was an error while starting the transfer',
	'transfer_success' => 'Transfer has been initiated correctly',
	
	//Settings
	'settings_title' => 'Settings',
	'settings_prices_title' => 'Price adjustment',
	'settings_prices_register_add' => 'Registration increase',
	'settings_prices_transfer_add' => 'Transfer increase',
	'settings_prices_renew_add' => 'Renew increase',
	'settings_prices_type_fixed' => 'Fixed',
	'settings_prices_type_percent' => '%',
	'settings_prices_type_disabled' => 'Disabled (fixed price)',
	'settings_prices_update_cron' => 'Update prices on WHMCS when they change',
	'settings_prices_update_cron_info' => '<strong>Warning:</strong> Enabling this option will cause prices on your WHMCS to update automatically. Use with caution.',
	'settings_notifications_title' => 'Automatic notifications',
	'settings_notifications_enable' => 'Enable notifications',
	'settings_notifications_email' => 'Email for notifications',
	'settings_notifications_email_info' => 'Email address where notifications will be sent',
	'settings_notifications_select' => 'Enabled notifications',
	'settings_notifications_new_tld' => 'New TLD available',
	'settings_notifications_prices_updated' => 'Prices have been updated',
	'settings_save_success' => 'Settings saved successfully',
	'settings_api_title' => 'DonDominio API',
	'settings_api_username' => 'API Username',
	'settings_api_username_info' => 'Fill in your API Username for DonDominio',
	'settings_api_password' => 'API Password',
	'settings_api_password_info' => 'Fill in your API Password for DonDominio',
	'settings_api_required' => 'Before using the DonDominio WHMCS Addon you need to enter your API account details.',
	'settings_watch_ignore' => 'Watch/Ignorelist',
	'settings_watch_ignore_disable' => 'Do not use the Watch/Ignorelist',
	'settings_watch_ignore_watch' => 'Watch only these TLDs',
	'settings_watch_ignore_ignore' => 'Ignore these TLDs',
	'settings_watch_ignore_available' => 'Available TLDs to select',
	'settings_watch_ignore_active' => 'Chosen TLDs',
	'settings_cache_title' => 'Cache status',
	'settings_cache_last_update' => 'Last update',
	'settings_cache_total' => 'TLDs in cache',
	'settings_cache_rebuild' => 'Rebuild cache',
	'settings_cache_rebuild_info' => 'Check this box and click on "Save changes" to rebuild the TLD cache',
	'settings_suggests_title' => 'Domain Suggestions',
	'settings_suggests_enable' => 'Enable Domain Suggestions',
	'settings_suggests_language' => 'Language for suggestions',
	'settings_suggests_tlds' => 'Generate suggestions for these TLDs:',
	'settings_whois_title' => 'Whois Proxy',
	'settings_whois_domain' => 'WHMCS Domain',
	'settings_whois_ip' => 'Allowed IP address',
	'settings_whois_ip_info' => 'Enter more than one IP address by separating them with ;',
	
	'tld_settings_title' => 'Individual TLD settings',
	'tld_settings_description' => 'Adjust the price increase for each TLD individually',
	'tld_settings_no_update' => 'Do not update automatically the prices for this TLD',
	'tld_settings_enabled' => 'Enable these settings',
	
	// Suggests
	'suggests_title' => 'Domain suggestions',
	'suggests_lang' => 'Language',
	'suggests_tlds' => 'TLDs',
	'suggests_info_saved' => 'Settings have been saved',
	'lang_en' => 'English',
	'lang_es' => 'Spanish',
	'lang_zh' => 'Chinese',
	'lang_fr' => 'French',
	'lang_de' => 'German',
	'lang_kr' => 'Korean',
	'lang_pt' => 'Portuguese',
	'lang_tr' => 'Turkish',
	'suggests_is_enabled' => 'Domain suggestions module is',
	'suggests_enabled' => 'Enabled',
	'suggests_disabled' => 'Disabled',
	'suggests_change_settings_a' => 'You may enable or disable and configure the Domain Suggestions on the',
	'suggests_change_settings_b' => 'Settings',
	'suggests_change_settings_c' >= 'module',
	'suggests_integration_a' => 'Follow the',
	'suggests_integration_b' => 'Integration Instructions',
	'suggests_integration_c' => 'to enable Domain Suggestions on your WHMCS frontend.',
	
	// Suggests TPL
	'tpl_you_may_also_like' => 'You may also like...',
	'tpl_go_to_checkout' => 'Go to checkout',
	'tpl_search_results' => 'Search results',
	'tpl_add_to_cart' => 'Add to Cart',
	'tpl_additional_pricing_options_for' => 'Additional pricing options for',
	'tpl_years' => 'Years',
	'tpl_available' => 'Available',
	
	/*
	 * CONFIG
	 */
	'config_settings' => 'Change settings',
	'config_username' => 'API Username',
	'config_password' => 'API Password',
	'config_domain' => 'Access domain',
	'config_domain_info' => 'This is the domain name where your WHMCS frontend is hosted, con http:// o https://.',
	'config_ip' => 'Allowed IPs',
	'config_ip_info' => 'Only requests coming from these IPs will be allowed to access the Whois proxy. Separate IPs with ;.',
	'config_save' => 'Save settings',
	'config_cancel' => 'Cancel',
	'config_save_success' => 'Settings successfully saved',
	'config_save_error' => 'Settings couldn\'t be saved. Maybe you have a permissions problem?',
	'config_switch' => 'Switch to MrDomain/DonDominio',
	
	/*
	 * NEW TLD
	 */
	'new_tld' => 'Add a new TLD',
	'new_tld_tld' => 'TLD',
	'new_tld_add' => 'Add TLD',
	
	/*
	 * INFO
	 */
	'info_path_whois' => 'Yor Whois servers file is located here',
	'info_path_moreinfo' => 'Documentation',
	'info_whois_domain' => 'Before using the Whois Proxy, configure it on the settings screen.',
	'info_whois_settings' => 'Click here to access the settings screen',
	
	/*
	 * IMPORT/EXPORT
	 */	
	'servers_export' => 'Export server list',
	'servers_import' => 'Import server list',
	'import_btn' => 'Import file',
	
	/*
	 * MESSAGES
	 */
	'error_servers_no_writable' => 'Whois servers file is not writable by the server. Make it writable or edit it directly.',
	'error_whois_domain_empty' => 'Whois domain name is empty',
	'new-tld-error-permissions' => 'Couldn\'t access file, check permissions or edit the file directly',
	'new-tld-ok' => 'TLD updated successfully',
	'new-tld-error' => 'Empty TLD provided',
	'import-ok' => 'Whois Servers file imported correctly',
	'import-error' => 'The provided Whois Servers file is invalid or you don\'t have enough permissions to updated the file',
	'settings-ok' => 'Settings modified successfully',
	'settings-error' => 'Could not save settings'
);

?>