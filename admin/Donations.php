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
        add_action('admin_menu', array($this, self::PREFIX . "donation_admin_menu_ops"));
//        add_action('admin_init', array($this, self::PREFIX . "style_script_reg_operations"));
    }

    public function donation_admin_menu_ops()
    {
        $pageTitle = __("Donations", 'dwc-plugin');
        $menuText = __("Donations", 'dwc-plugin');
        $capability = "manage_options";
        $callback = array($this, self::PREFIX . "donations_page_controller");
        $menuSlug = "donations";
        $icon = "dashicons-analytics";
        add_menu_page($pageTitle, $menuText, $capability, $menuSlug, $callback, $icon);
    }

    public function donations_page_controller()
    {

    }
}