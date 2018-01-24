<?php
   /*
   Plugin Name: Donate With Card
   Plugin URI: https://github.com/Kambaa/donate-with-card
   description: A plugin to create a page for donating money via credit card
   Version: 0.0.1
   Author: Kambaa
   Author URI: http://kambaa.tk
   License: GPL3
   */





/**
Inserts necessary db tables upon activation
*/
function dbTableInsertion(){
   global $wpdb;
   $donationTypesTableSql=null;
   $donationsTableSql=null;
   $donationItemsTableSql=null;
   $donationTypesTableName=$wpdb->prefix . 'donation-types';
   $donationsTableName=$wpdb->prefix . 'donations';
   $donationItemsTableName=$wpdb->prefix . 'donation-items';

   require_once(ABSPATH .'wp-admin/includes/upgrade.php');

   if($donationTypesTableName!=$wpdb->get_var('SHOW TABLES LIKE '.$donationTypesTableName)){
      $donationTypesTableSql="CREATE TABLE `$donationTypesTableName` (
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
if($donationsTableName!=$wpdb->get_var('SHOW TABLES LIKE '.$donationsTableName)){
   $donationsTableSql="CREATE TABLE `$donationsTableName` (
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

if($donationItemsTableName!=$wpdb->get_var('SHOW TABLES LIKE '.$donationItemsTableName)){
   $donationItemsTableSql="CREATE TABLE `$donationItemsTableName` (
   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id index',
   `donation_id` int(11) NOT NULL COMMENT 'donation id that current item belongs to',
   `donation_type_id` int(11) NOT NULL COMMENT 'donation type that current item belongs to',
   `amount` decimal(15,2) UNSIGNED NOT NULL COMMENT 'Donation amount of the current item',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='donation details mapping table';";
dbDelta($donationItemsTableSql);
}

add_option("donate_with_card_db_version","0.0.1");

}



function initialDonationTypeAdding(){
   register_deactivation_hook(__FILE__,'deactivateTest');
}

function deactivateTest(){
   require_once(ABSPATH .'wp-admin/includes/upgrade.php');
   $donationTypesTableName=$wpdb->prefix . 'donation-types';
   $donationsTableName=$wpdb->prefix . 'donations';
   $donationItemsTableName=$wpdb->prefix . 'donation-items';  
   $wpdb->query( "DROP TABLE IF EXISTS $donationTypesTableName" );
   $wpdb->query( "DROP TABLE IF EXISTS $donationsTableName" );
   $wpdb->query( "DROP TABLE IF EXISTS $donationItemsTableName" );
   delete_option( 'donate_with_card_db_version' );
}

register_activation_hook(__FILE__,"dbTableInsertion");
register_activation_hook(__FILE__,"initialDonationTypeAdding");
?>