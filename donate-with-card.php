<?php
/*
Plugin Name: Donate With Card
Plugin URI: https://github.com/Kambaa/donate-with-card
description: A plugin to create a page for donating money via credit card
Version: 0.0.1
Author: Kambaa
Author URI: http://kambaa.tk
License: GPL3
Text Domain: dwc-plugin
Domain Path: /languages/
*/

// Exit if accessed direcly.
if (!defined('ABSPATH')) {
    exit;
}

define("DWC_PLUGIN_DIR", plugin_dir_path(__FILE__));


/**
 * Inserts necessary db tables upon activation
 */
function dwc_ddl()
{
    global $wpdb;
    $donationTypesTableSql = null;
    $donationsTableSql = null;
    $donationItemsTableSql = null;
    $donationTypesTableName = $wpdb->prefix . 'donation-types';
    $donationsTableName = $wpdb->prefix . 'donations';
    $donationItemsTableName = $wpdb->prefix . 'donation-items';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    if ($donationTypesTableName != $wpdb->get_var('SHOW TABLES LIKE ' . $donationTypesTableName)) {
        $donationTypesTableSql = "CREATE TABLE `$donationTypesTableName` (
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
    if ($donationsTableName != $wpdb->get_var('SHOW TABLES LIKE ' . $donationsTableName)) {
        $donationsTableSql = "CREATE TABLE `$donationsTableName` (
   `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id index',
   `name` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s name',
   `surname` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s surname',
   `email` varchar(255) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s email address',
   `phone` varchar(50) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s phone number',
   `trid` varchar(11) COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s Turkish Identification Number (TCKN)',
   `donation-notes` text COLLATE utf8mb4_turkish_ci NOT NULL COMMENT 'donator''s custom notes',
   `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'donation time',
   `total` decimal(15,2) NOT NULL COMMENT 'total donation amount',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='donation that has been made';";
        dbDelta($donationsTableSql);
    }

    if ($donationItemsTableName != $wpdb->get_var('SHOW TABLES LIKE ' . $donationItemsTableName)) {
        $donationItemsTableSql = "CREATE TABLE `$donationItemsTableName` (
   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id index',
   `donation_id` int(11) NOT NULL COMMENT 'donation id that current item belongs to',
   `donation_type_id` int(11) NOT NULL COMMENT 'donation type that current item belongs to',
   `amount` decimal(15,2) UNSIGNED NOT NULL COMMENT 'Donation amount of the current item',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='donation details mapping table';";
        dbDelta($donationItemsTableSql);
    }

    add_option("donate_with_card_db_version", "0.0.1");

}


function dwc_dml()
{
    /*
    INSERT INTO `wp_donation-types` (`id`, `name`, `label`, `default_price`, `ord`) VALUES
(1, 'Hayat Kurtarınca Güzel', 'Hayat Kurtarınca Güzel', '115.00', NULL),
(2, 'Genel Bağış', 'Genel Bağış', NULL, NULL),
(3, 'Zekat', 'Zekat', NULL, NULL),
(4, 'Suriye Krizi', 'Suriye Krizi', NULL, NULL),
(5, 'Yemen Krizi', 'Yemen Krizi', NULL, NULL),
(6, 'Arakan Krizi', 'Arakan Krizi', NULL, NULL),
(7, 'Tedavi Programları', 'Tedavi Programları', NULL, NULL),
(8, 'Koruyucu Sağlık Programları', 'Koruyucu Sağlık Programları', NULL, NULL),
(9, 'Sağlık Eğitimleri', 'Sağlık Eğitimleri', NULL, NULL),
(10, 'Gözlerini Aç!', 'Gözlerini Aç!', '300.00', NULL),
(11, 'Açlıktan Ölüyorum! Gerçekten...', 'Açlıktan Ölüyorum! Gerçekten...', '145.00', NULL),
(12, 'Bizim İçin Su Onlar İçin Hayat', 'Bizim İçin Su Onlar İçin Hayat', NULL, NULL),
(13, 'Kurban Olsun Sağlık Olsun', 'Kurban Olsun Sağlık Olsun', '430.00', NULL);*/


}

function dwc_deactivate()
{

}

function dwc_uninstall()
{
//    global $wpdb;
//    $wpdb->query("DROP TABLE IF EXISTS $donationTypesTableName;");
//    $wpdb->query("DROP TABLE IF EXISTS $donationsTableName;");
//    $wpdb->query("DROP TABLE IF EXISTS $donationItemsTableName;");
//    delete_option('donate_with_card_db_version');
}

register_activation_hook(__FILE__, "dwc_ddl");
register_activation_hook(__FILE__, "dwc_dml");
register_deactivation_hook(__FILE__, "dwc_deactivate");
// register_uninstall_hook(__FILE__,"dwc_uninstall");
register_deactivation_hook(__FILE__, "dwc_uninstall");

require plugin_dir_path(__FILE__) . "admin/PluginBase.php";
require plugin_dir_path(__FILE__) . "admin/dt.php";
require plugin_dir_path(__FILE__) . "admin/Donations.php";
new Dt();
new Donations();

// MENU STRUCTURE
// DonateWithCard
//    - Donations
//    - Options
//         - Donation Types
//         - VPos Settings
//         - Misc
//


function my_plugin_load_plugin_textdomain()
{
    load_plugin_textdomain('dwc-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'my_plugin_load_plugin_textdomain');

require_once 'public/donate-form.php';

?>