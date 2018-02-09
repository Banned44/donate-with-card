<?php

class Donations extends PluginBase
{
    private $tableName;
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = &$wpdb;
        $this->tableName = $this->db->prefix . "donations";
        add_action('admin_menu', array($this, self::PREFIX . "xxxadmin_menu_operations"));
//        add_action('admin_init', array($this, self::PREFIX . "style_script_reg_operations"));
    }

    public function xxxadmin_menu_operations()
    {
        $pageTitle = __("Donations", 'dwc-plugin');
        $menuText = __("Donations", 'dwc-plugin');
        $capability = "manage_options";
        $callback = array($this, self::PREFIX . "donations_page_controller");
        $menuSlug = "donations";
        add_menu_page($pageTitle, $menuText, $capability, $menuSlug, $callback);
    }

    public function donations_page_controller()
    {

    }
}
///**
// * Register a custom menu page.
// */
//function wpdocs_register_my_custom_menu_page() {
//    add_menu_page(
//        __( 'Custom Menu Title', 'textdomain' ),
//        'custom menu',
//        'manage_options',
//        'myplugin/myplugin-admin.php',
//        '',
//        plugins_url( 'myplugin/images/icon.png' ),
//        6
//    );
//}
//add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );
