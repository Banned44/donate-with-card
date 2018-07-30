<?php
/*
Plugin Name: Online Bağış Wordpress Eklentisi
Plugin URI: https://github.com/Kambaa/donate-with-card
description: Wordpress dernek siteleri için kredi kartı ile bağış yapılabilmeyi sağlayan eklenti.
Version: 0.1.1
Author: Yusuf Gündüz <yusuf.gunduz@gmail.com>
Author URI: http://www.yusufgunduz.com.tr
License: GPL3
Text Domain: dwc-plugin
Domain Path: /languages/
*/

// Exit if accessed direcly.
if (!defined('ABSPATH')) {
    exit;
}

define("DWC_PLUGIN_DIR", plugin_dir_path(__FILE__));
define("DONATION_TYPES_TABLE_NAME", "dwc_donation_types");
define("DONATIONS_TABLE_NAME", "dwc_donations");
define("DONATION_ITEMS_TABLE_NAME", "dwc_donation_items");
define('DWC_BANK_RETURN_URL', admin_url('admin-post.php?action=vpos_return', 'http'));
define("DWC_OPTION_NAME_VERSION", "donate_with_card_version");
define("DWC_OPTION_NAME_VPOS_CUSTOMER_ID", "dwc_option_kuveytTurk_customerId");
define("DWC_OPTION_NAME_VPOS_MERCHANT_ID", "dwc_option_kuveytTurk_merchantId");
define("DWC_OPTION_NAME_VPOS_USERNAME", "dwc_option_kuveytTurk_username");
define("DWC_OPTION_NAME_VPOS_PASSWORD", "dwc_option_kuveytTurk_password");
define("DWC_OPTION_NAME_VPOS_CARDVALIDATIONURL", "dwc_option_kuveytTurk_cardValidationUrl");
define("DWC_OPTION_NAME_VPOS_CARPROVISIONURL", "dwc_option_kuveytTurk_cardProvisionUrl");
define("DWC_OPTION_NAME_KURBAN_VEKIL_TAYIN", "kurbanVekilTayin");

/**
 * Inserts necessary db tables upon activation
 */
function dwc_ddl()
{
    global $wpdb;

    $donationTypesTableSql = null;
    $donationsTableSql = null;
    $donationItemsTableSql = null;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    if (DONATION_TYPES_TABLE_NAME != $wpdb->get_var("SHOW TABLES LIKE '" . DONATION_TYPES_TABLE_NAME . "'")) {
        $donationTypesTableSql = "CREATE TABLE `" . DONATION_TYPES_TABLE_NAME . "` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id index',
      `name` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'Unique donation name',
      `label` varchar(255) COLLATE utf8mb4_turkish_ci DEFAULT NULL COMMENT 'domain label for web display',
      `default_price` decimal(15,2) DEFAULT NULL COMMENT 'default price - if available',
      `ord` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'custom ordering for web page',
      PRIMARY KEY (`id`),
      UNIQUE KEY `name` (`name`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='defines donation types';";
        dbDelta($donationTypesTableSql);
    }
    if (DONATIONS_TABLE_NAME != $wpdb->get_var("SHOW TABLES LIKE '" . DONATIONS_TABLE_NAME . "'")) {
        $donationsTableSql = "CREATE TABLE `" . DONATIONS_TABLE_NAME . "` (
   `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id index',
   `name` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s name',
   `surname` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s surname',
   `email` varchar(255) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s email address',
   `phone` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s phone number',
   `donation_notes` text COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s custom notes',
   `provision_result` text COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'card provision result data',
   `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'donation time',
   `total` decimal(15,2) NOT NULL COMMENT 'total donation amount',
   `vpos_data` mediumtext null,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='donations that has been successfully made';";
        dbDelta($donationsTableSql);
    }

    if (DONATION_ITEMS_TABLE_NAME != $wpdb->get_var("SHOW TABLES LIKE '" . DONATION_ITEMS_TABLE_NAME . "'")) {
        $donationItemsTableSql = "CREATE TABLE `" . DONATION_ITEMS_TABLE_NAME . "` (
   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id index',
   `donation_id` int(11) NOT NULL COMMENT 'donation id that current item belongs to',
   `donation_type_id` int(11) NOT NULL COMMENT 'donation type that current item belongs to',
   `amount` decimal(15,2) UNSIGNED NOT NULL COMMENT 'Donation amount of the current item',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='donation details mapping table';";
        dbDelta($donationItemsTableSql);
    }

    add_option(DWC_OPTION_NAME_VERSION, "0.1.1");
    add_option(DWC_OPTION_NAME_VPOS_CUSTOMER_ID, "400235");
    add_option(DWC_OPTION_NAME_VPOS_MERCHANT_ID, "496");
    add_option(DWC_OPTION_NAME_VPOS_USERNAME, "apiuser10");
    add_option(DWC_OPTION_NAME_VPOS_PASSWORD, "123456");
    add_option(DWC_OPTION_NAME_VPOS_CARDVALIDATIONURL, "https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelPayGate");
    add_option(DWC_OPTION_NAME_VPOS_CARPROVISIONURL, "https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelProvisionGate");
}

function dwc_dml()
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql = "INSERT INTO " . DONATION_TYPES_TABLE_NAME . "(`id`, `name`, `label`, `default_price`, `ord`) VALUES
(1, 'Test_donation', 'Deneme Bağış', '1.00', NULL);";
    dbDelta($sql);
}

function dwc_uninstall()
{
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta("DROP TABLE " . DONATION_TYPES_TABLE_NAME);
    dbDelta("DROP TABLE " . DONATION_ITEMS_TABLE_NAME);
    dbDelta("DROP TABLE " . DONATIONS_TABLE_NAME);
    delete_option(DWC_OPTION_NAME_VERSION);
    delete_option(DWC_OPTION_NAME_VPOS_CUSTOMER_ID);
    delete_option(DWC_OPTION_NAME_VPOS_MERCHANT_ID);
    delete_option(DWC_OPTION_NAME_VPOS_USERNAME);
    delete_option(DWC_OPTION_NAME_VPOS_PASSWORD);
    delete_option(DWC_OPTION_NAME_VPOS_CARDVALIDATIONURL);
    delete_option(DWC_OPTION_NAME_VPOS_CARPROVISIONURL);
}

register_activation_hook(__FILE__, "dwc_ddl");
register_activation_hook(__FILE__, "dwc_dml");
register_uninstall_hook(__FILE__, "dwc_uninstall");

require plugin_dir_path(__FILE__) . "admin/PluginBase.php";
require plugin_dir_path(__FILE__) . "admin/Dt.php";
require plugin_dir_path(__FILE__) . "admin/Donations.php";
require plugin_dir_path(__FILE__) . "admin/VPosPayment.php";
require plugin_dir_path(__FILE__) . "admin/KuveytTurkVPosPayment.php";

// Add donation type settings page to the wordpress admin's settings menu
new Dt();

new Donations();

function dwc_load_plugin_textdomain()
{
    load_plugin_textdomain('dwc-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'dwc_load_plugin_textdomain');

require_once 'public/donate-form.php';
