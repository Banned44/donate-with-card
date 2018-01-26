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

// Exit if accessed direcly.
if (!defined('ABSPATH')) {
    exit;
}


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
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS $donationTypesTableName;");
    $wpdb->query("DROP TABLE IF EXISTS $donationsTableName;");
    $wpdb->query("DROP TABLE IF EXISTS $donationItemsTableName;");
    delete_option('donate_with_card_db_version');
}

register_activation_hook(__FILE__, "dwc_ddl");
register_activation_hook(__FILE__, "dwc_dml");
register_deactivation_hook(__FILE__, "dwc_deactivate");
// register_uninstall_hook(__FILE__,"dwc_uninstall");
register_deactivation_hook(__FILE__, "dwc_uninstall");


// function wpdocs_register_my_custom_menu_page() {
// // add_menu_page('My Custom Page', 'My Custom Page', 'manage_options', 'my-top-level-slug');
// // add_submenu_page( 'my-top-level-slug', 'My Custom Page', 'My Custom Page','manage_options', 'my-top-level-slug');
// // add_submenu_page( 'my-top-level-slug', 'My Custom Submenu Page', 'My Custom Submenu Page',    'manage_options', 'my-secondary-slug');

//     add_submenu_page(
//         'tools.php',
//         'My Custom Submenu Pagwasdasde',
//         'Donate With Card Options',
//         'manage_options',
//         'my-custom-submenu-page',
//         'wpdocs_my_custom_submenu_page_callback' );
// }
// function wpdocs_my_custom_submenu_page_callback() {
//     echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
//         echo '<h2>My Custom Submenu Page</h2>';
//     echo '</div>';
// }
// add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );

class DonateWithCard
{

    public function addAdminPage()
    {
        // Add the menu item and page
        $page_title = 'Donate With Card - Settings - Donation Types';
        $menu_title = 'Donation Types';
        $capability = 'manage_options';
        $slug = 'donate-with-card-settings-donation-types';
        $callback = "dwcDonationTypesDisplay";//array( $this, 'plugin_settings_page_content' );
        $icon = 'dashicons-admin-plugins';
        $position = 100;
        add_menu_page($page_title, $menu_title, $capability, $slug, $callback, $icon, $position);

//        add_submenu_page('options-general.php', $page_title, $menu_title, $capability, $slug, $callback);
    }


    public function __construct()
    {
        // Hook into the admin menu
        add_action('admin_menu', array($this, 'addAdminPage'));
    }

    public function plugin_settings_page_content()
    {
        // echo 'Hello World!';
        if ($_POST['updated'] === 'true') {
            $this->handle_form();
        }
        ?>
        <div class="wrap">
            <h2>My Awesome Settings Page</h2>
            <form method="POST">
                <input type="hidden" name="updated" value="true"/>
                <?php wp_nonce_field('awesome_update', 'awesome_form'); ?>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="username">Username</label></th>
                        <td><input name="username" id="username" type="text"
                                   value="<?php echo get_option('awesome_username'); ?>" class="regular-text"/></td>
                    </tr>
                    <tr>
                        <th><label for="email">Email Address</label></th>
                        <td><input name="email" id="email" type="text"
                                   value="<?php echo get_option('awesome_email'); ?>" class="regular-text"/></td>
                    </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Check My Info!">
                </p>
            </form>
        </div> <?php
    }

    public function handle_form()
    {
        if (
            !isset($_POST['awesome_form']) ||
            !wp_verify_nonce($_POST['awesome_form'], 'awesome_update')
        ) { ?>
            <div class="error">
                <p>Sorry, your nonce was not correct. Please try again.</p>
            </div> <?php
            exit;
        } else {
            $valid_usernames = array('admin', 'matthew');
            $valid_emails = array('email@domain.com', 'anotheremail@domain.com');

            $username = sanitize_text_field($_POST['username']);
            $email = sanitize_email($_POST['email']);

            if (in_array($username, $valid_usernames) && in_array($email, $valid_emails)) {
                update_option('awesome_username', $username);
                update_option('awesome_email', $email); ?>
                <div class="updated">
                    <p>Your fields were saved!</p>
                </div> <?php
            } else { ?>
                <div class="error">
                    <p>Your username or email were invalid.</p>
                </div> <?php
            }
        }
    }
}

new DonateWithCard();

//include( plugin_dir_path( __FILE__ ) . 'admin/donation-types.php');


function dwcDonationTypesDisplay()
{
    global $wpdb;
    $sql = "SELECT * FROM `" . $wpdb->prefix . "donation-types` " . "ORDER BY ord ASC";
    $result = $wpdb->get_results($sql, ARRAY_A);

    ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" type="text/css"
          media="all"/>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>


    <h2>Donation Types</h2>
    <table id="example" class="display" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Label</th>
            <th>Default Price</th>
            <th>Edit</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($result as $v) {
            $v['default_price'] = empty($v['default_price']) ? "-" : $v['default_price'];
            echo "<tr><td>{$v['id']}</td><td>{$v['name']}</td><td>{$v['label']}</td><td>{$v['default_price']}</td><td><a href='#{$v['id']}'><span class=\"dashicons dashicons-edit\"></span></a></td>";
        }
        ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function () {
            $('#example').DataTable({
                responsive: {
                    details: false
                },
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
            });
        });
    </script>
    <?php

}


// MENU STRUCTURE
// DonateWithCard
//    - Donations
//    - Options
//         - Donation Types
//         - VPos Settings
//         - Misc
//

?>
