<?php

/**
 * The DonDominio Manager Addon for WHMCS.
 * Mod: Spanish langfile
 * WHMCS version 5.2.x / 5.3.x
 * @link https://github.com/dondominio/dondominiowhmcsaddon
 * @package DonDominioWHMCSAddon
 * @license CC BY-ND 3.0 <http://creativecommons.org/licenses/by-nd/3.0/>
 */
 
$_ADDONLANG = array(
	//Buttons
	'btn_edit' => 'Editar',
	'btn_add' => 'Añadir',
	'btn_delete' => 'Borrar',
	'btn_update_selected' => 'Actualizar seleccionados',
	'btn_delete_selected' => 'Borrar seleccionados',
	'btn_add_selected' => 'Añadir seleccionados',
	'btn_save' => 'Guardar cambios',
	'btn_back' => 'Volver',
	'btn_transfer' => 'Transferir a DonDominio',
	
	//Info
	'info_enabled' => 'El Addon DonDominio Manager está ahora listo. ¡Que lo disfrutes!',
	'info_disabled' => 'El Addon DonDominio Manager se ha desactivado correctamente.',
	'info_error_enabling' => 'Se ha producido un error activando el Addon DonDominio Manager. Por favor, contacta con soporte.',
	'info_error_disabling' => 'Se ha producido un error desactivando el Addon DonDominio Manager. Por favor, contacta con soporte.',
	'info_with_selected' => 'Con los seleccionados:',
	'info_no_results' => 'No hay registros',
	'info_errors' => 'Se ha producido un error:',
	'info_too_much_requests' => 'Ten en cuenta que esta función puede realizar una gran cantidad de peticiones a la API, y por consiguiente llevar algún tiempo completarla. Dependiendo de la configuración en tu servidor puede que encuentres límites de tiempo de ejecución. Para evitar estos problemas, usa los filtros y selecciona sólo los dominios que deben actualizarse para limitar la cantidad de dominios que se envían de una vez.',
	
	//Links
	'link_more_info' => 'Más información',
	
	//Menu
	'menu_tlds_update' => 'Actualizar tarifas',
	'menu_tlds_new' => 'TLDs disponibles',
	'menu_domains' => 'Gestión de dominios',
	'menu_import' => 'Importar dominios',
	'menu_transfer' => 'Transferir dominios',
	'menu_settings' => 'Configuración',
	'menu_help' => 'Ayuda y documentación',
	
	//Filter
	'filter_title' => 'Filtro/Búsqueda',
	'filter_domain' => 'Nombre de dominio',
	'filter_tld' => 'TLD',
	'filter_status' => 'Estado',
	'filter_registrar' => 'Registrador',
	'filter_any' => 'Cualquiera',
	'filter_search' => 'Buscar',
	'filter_pending' => 'Pendiente',
	'filter_pending_transfer' => 'Pendiente de transferencia',
	'filter_active' => 'Activo',
	'filter_expired' => 'Caducado',
	'filter_cancelled' => 'Cancelado',
	'filter_fraud' => 'Fraude',
	
	//Pagination
	'pagination_results_found' => 'Resultados encontrados',
	'pagination_page' => 'Página',
	'pagination_of' => 'de',
	'pagination_go' => 'Ir',
	'pagination_go_to' => 'Ir a la Página:',
	'pagination_previous' => 'Página anterior',
	'pagination_next' => 'Página siguiente',
	
	//TLDs
	'tld_title' => 'Actualizar tarifas',
	'tld_info' => 'Estos son los TLDs actualmente instalados en tu WHMCS. Puedes actualizar su información de precios y cambiar el autoregistro para que usen la API de DonDominio, si tienes el módulo de registro de DonDominio instalado.',
	'tld_new_title' => 'TLDs disponibles',
	'tld_new_info' => 'Estos son los TLDs disponibles para ser configurados en tu WHMCS. Cuando los añadas a WHMCS se crearán junto a su información de precios. Esta lista se actualiza cada vez que se ejecuta el trabajo Cron.',
	'tld_create_title' => 'Añadir nuevos TLDs',
	'tld_tld' => 'TLD',
	'tld_registrar' => 'Registrador',
	'tld_no_selected' => 'Ningún TLD seleccionado',
	'tld_created_success_info' => 'Los siguientes TLDs se han añadido:',
	'tld_created_no_tlds' => 'No se han añadido TLDs; todos están sincronizados',
	'tld_prices_success' => 'Los precios de los TLDs se han actualizado correctamente en las siguientes extensiones:',
	'tld_update_success' => 'Los siguientes TLDs se han actualizado correctamente:',
	'btn_prices_selected' => 'Actualizar precios',
	'btn_registrar_selected' => 'Cambiar registrador a DonDominio',
	'btn_create_selected' => 'Añadir a WHMCS',
	'tld_register' => 'Registro',
	'tld_transfer' => 'Transferencia',
	'tld_renew' => 'Renovación',
	'tld_not_available' => 'No disponible',
	'tld_regenerate' => 'Reconstruir la caché de TLDs',
	
	//Domains
	'domains_title' => 'Gestión de dominios',
	'domains_info' => 'Estos son los dominios actualmente registrados en tu instalación de WHMCS. Puedes actualizar su infromación usando la API de DonDominio y configurarlos para que usen el módulo de registro de Dondominio, si está instalado. También puedes actualizar su información de contacto usando un ID de contacto de DonDominio.',
	'domains_domain' => 'Dominio',
	'domains_status' => 'Estado',
	'domains_registrar' => 'Registrador',
	'domains_set_owner' => 'Actualizar contacto propietario',
	'domains_set_admin' => 'Actualizar contacto administrativo',
	'domains_set_tech' => 'Actualizar contacto técnico',
	'domains_set_billing' => 'Actualizar contacto de pago',
	'domains_set_dondominio' => 'Cambiar registrador a DonDominio',
	'domains_contact_id' => 'ID de contacto de DonDominio',
	'domains_operation_complete' => 'Operación completada',
	'domains_no_domains_selected' => 'Ningún dominio seleccionado',
	'domains_registrar_success' => 'Registrador cambiado correctamente para los siguientes dominios',
	'domains_price_update_success' => 'El precio de los siguientes dominios ha sido actualizado correctamente',
	'domains_price_no_changes' => 'No se han hecho cambios en ningún dominio',
	'domains_contacts_error' => 'Se ha producido un error al actualizar los contactos',
	'domains_contacts_success' => 'Los siguientes dominios han sido actualizados correctamente',
	'domains_error_dondominio_id' => 'Debes especificar un ID de contacto en DonDominio para continuar',
	'domains_requests' => 'Peticiones',
	'domains_success' => 'Correctos',
	'domains_errors' => 'Errores',
	'domains_sync' => 'Actualizar datos desde DonDominio',
	'domains_price' => 'Actualizar precio de renovación',
	'domains_sync_success' => 'Dominios sincronizados correctamente',
	'domains_price_errors' => 'Los siguientes dominios no han podido ser actualizados',
	'domains_tld_price_not_found' => 'El precio del TLD no ha sido encontrado en la base de datos',
	'domains_eur_not_found' => 'La divisa Euro no ha sido encontrada en la base de datos',
	'domains_tld_not_valid' => 'El TLD no ha sido encontrado en la base de datos',
	
	//Import
	'import_title' => 'Importar dominios',
	'import_info' => 'Estos son los dominios que hay en tu cuenta de DonDominio. Si un dominio no está en tu instalación de WHMCS, puedes importar su información y asignarlo a un cliente existente.',
	'import_btn_import' => 'Importar a WHMCS y asignar al cliente seleccionado',
	'import_imported' => 'Importado',
	'import_not_imported' => 'No Importado',
	'import_success' => 'Los siguientes dominios han sido importados:',
	'import_completed_not_imported' => 'Los siguientes dominios ya existían en la base de datos:',
	'import_error' => 'Los siguientes dominios no se han importado debido a un error:',
	
	//Transfer
	'transfer_title' => 'Transferir dominios',
	'transfer_info' => 'Utiliza esta opción para transferir dominios a DonDominio desde otros registradores',
	'transfer_domain' => 'Nombre de dominio',
	'transfer_authcode' => 'Authcode/EPP',
	'transfer_authcode_required' => 'Esta extensión de dominio require un Authcode/EPP para transferir dominios',
	'transfer_generic_error' => 'Se ha producido un error al transferir el dominio',
	'transfer_domain_not_found' => 'El dominio solicitado no se ha encontrado en WHMCS',
	'transfer_invalid_domain_name' => 'El dominio no tiene un nombre de dominio válido',
	'transfer_tld_not_found' => 'La extensión de dominio no es una extensión soportada por DonDominio',
	'transfer_client_not_found' => 'El cliente no ha sido encontrado en WHMCS',
	'transfer_vatnumber_empty' => 'El cliente tiene el campo NIF vacío; no se puede continuar',
	'transfer_already_transferred' => 'Este dominio ya ha sido transferido a DonDominio',
	'transfer_error' => 'Se ha producido un error al transferir el dominio',
	'transfer_success' => 'Transferencia iniciada correctamente',
	
	//Settings
	'settings_title' => 'Configuración',
	'settings_prices_title' => 'Ajuste de precios',
	'settings_prices_register_add' => 'Aumento de registro',
	'settings_prices_transfer_add' => 'Aumento de transferencia',
	'settings_prices_renew_add' => 'Aumento de renovación',
	'settings_prices_type_fixed' => 'Fijo',
	'settings_prices_type_percent' => '%',
	'settings_prices_type_disabled' => 'Desactivado (precio fijo)',
	'settings_prices_update_cron' => 'Actualizar precios en WHMCS cuando cambien',
	'settings_prices_update_cron_info' => '<strong>Advertencia:</strong> Activar esta opción causará que los precios en WHMCS cambien automáticamente. Usar con precaución.',
	'settings_notifications_title' => 'Notificaciones automáticas',
	'settings_notifications_enable' => 'Activar notificaciones',
	'settings_notifications_email' => 'Correo para notificaciones',
	'settings_notifications_email_info' => 'Dirección de correo electrónico a la que llegarán las notificaciones',
	'settings_notifications_select' => 'Notificaciones activadas',
	'settings_notifications_new_tld' => 'Nuevo TLD disponible',
	'settings_notifications_prices_updated' => 'Cambios en precios',
	'settings_save_success' => 'La configuración se ha guardado correctamente',
	'settings_api_title' => 'API de DonDominio',
	'settings_api_username' => 'Usuario de API',
	'settings_api_username_info' => 'Introduce tu usuario para la API de DonDominio',
	'settings_api_password' => 'Contraseña de API',
	'settings_api_password_info' => 'Introduce tu contraseña para la API de DonDominio',
	'settings_api_required' => 'Antes de usar el Addon de DonDominio para WHMCS necesitas introducir tus datos de cuenta de API.',
	'settings_watch_ignore' => 'Lista de seguimiento/ignorados',
	'settings_watch_ignore_disable' => 'No usar la lista de seguimiento/ignorados',
	'settings_watch_ignore_watch' => 'Seguir sólo estos TLDs',
	'settings_watch_ignore_ignore' => 'Ignorar estos TLDs',
	'settings_watch_ignore_available' => 'TLDs disponibles',
	'settings_watch_ignore_active' => 'TLDs seleccionados',
	'settings_cache_title' => 'Estado de caché',
	'settings_cache_last_update' => 'Última actualización',
	'settings_cache_total' => 'TLDs en caché',
	'settings_cache_rebuild' => 'Reconstruir la caché',
	'settings_cache_rebuild_info' => 'Marca esta casilla y pulsa "Guardar cambios" para reconstruir la caché de TLDs',
	
	'tld_settings_title' => 'Configuración de TLD individual',
	'tld_settings_description' => 'Ajusta el aumento de precio de un TLD de forma independiente',
	'tld_settings_no_update' => 'No actualizar de forma automática el precio de este TLD',
	'tld_settings_enabled' => 'Activar configuración individual'
);

?>