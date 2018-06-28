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
new Dt();

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


function my_registration_form($params, $content = null)
{
    global $wpdb;
    $query = "SELECT * FROM `{$wpdb->prefix}donation-types` ";
    $donationTypes = $wpdb->get_results($query, ARRAY_A);

    extract(shortcode_atts(array(
        'type' => 'style1'
    ), $params));

    ob_start();
    ?>
    <link rel="stylesheet" type="text/css" href="//local/cardjs/card.css">
    <script src="//local/cardjs/card.js"></script>
    <script src="//local/cardjs/jquery.card.js"></script>
    <h3>Bağış Seçiniz</h3>
    <form>
        <select id="donation_types" style="width:75%;float:left;">
            <option value="-1">Bağış Türü Seçiniz</option>
        </select>
        <input id="donation_amount" type="number" minlength="0" min="0" style="margin-left:2%;width:23%;float:left;"/>
        <input type="button" id="addToCart" value="Kutuya Ekle" style="clear:both;margin-top:12px;"/>
    </form>
    <h3>Bağış Kutusu</h3>
    <div id="cartContainer" style="padding-left: 20px;">
        <ul id="donationCart">
            <hr>
            <li>Toplam <span style="float:right;">0 TL</span></li>
        </ul>
    </div>
    <h3>Bilgileriniz</h3>
    <form id="donation_infos" method="post" action="">

        <label for="">Adınız Soyadınız
            <input type="text" name="name"/>
        </label>

        <label for="">Telefon Numaranız
            <input type="tel" name="tel"/>
        </label>

        <label for="">E-Posta Adresiniz
            <input type="email" name="email"/>
        </label>

        <label for="">T.C. Kimlik Numaranız
            <input type="text" name="tckn"/>
        </label>

        <label for="">Bağış Notunuz
            <textarea name="donation_notes" rows="5" cols="30"></textarea>
        </label>

        <div class='card-wrapper'></div>

        <input type="text" id="cardholder_name" name="name" placeholder="Kart sahibi adı"
               style="width:50%;float:left;"/>
        <input type="text" id="card_number" name="number" placeholder="Kart no" style="width:50%;float:left;">
        <div style="clear:both;"></div>
        <div style="width:50%;float:left;">
            <select name="month" style="width:50%;float:left;">
                <option value="">Ay Seçiniz</option>
                <option value="01">Ocak</option>
                <option value="02">Şubat</option>
                <option value="03">Mart</option>
                <option value="04">Nisan</option>
                <option value="05">Mayıs</option>
                <option value="06">Haziran</option>
                <option value="07">Temmuz</option>
                <option value="08">Ağustos</option>
                <option value="09">Eylül</option>
                <option value="10">Ekim</option>
                <option value="11">Kasım</option>
                <option value="12">Aralık</option>
            </select>
            <select name="year" style="width:50%;float:left;">
                <option value="">Yıl Seçiniz</option>
                <option>2018</option>
                <option>2019</option>
                <option>2020</option>
                <option>2021</option>
                <option>2022</option>
                <option>2023</option>
                <option>2024</option>
                <option>2025</option>
                <option>2026</option>
                <option>2027</option>
                <option>2028</option>
                <option>2029</option>
                <option>2030</option>
            </select>
        </div>
        <div style="width:50%;float:left;">
            <input type="text" id="card_cvc" name="cvc" placeholder="CVC Kodu"/>
        </div>
        <div style="clear:both;"></div>
        <input type="submit" name="donation" value="Bağış Yapın"/>
    </form>
    <script>
        $ = jQuery;
        var adminUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
        $(function () {

            var dt =<?php echo json_encode($donationTypes);?>;

            var donationCart = [];

            function getDonationType(id) {
                for (var i in dt) {
                    if (dt[i]['id'] == id) {
                        return dt[i];
                    }
                }
                return false;
            }

            function resetDonationTypeFields() {
                $('#donation_types').prop('selectedIndex', 0);
                $('#donation_amount').val("").attr("disabled", false);
            }

            function calculateCartTotal() {
                var total = 0;
                for (var i in donationCart) {
                    var price = null == donationCart[i]['price'] ? parseFloat(donationCart[i]['default_price']) : parseFloat(donationCart[i]['price']);
                    total += price;
                }
                return total;
            }

            var the_ajax_script = {"ajaxurl": "http://yg.com/wp/wp-admin/admin-ajax.php"};

            function addToSession(itemData) {
                var url = the_ajax_script.ajaxurl;
                var data = {
                    action: "dwc_basket_operations",
                    basket_operation: "add",
                    item: itemData
                };
                var callback = function (obj) {
                    console.log(obj);
                };
                $.post(url, data, callback, 'json');
            }

            function deleteFromSession(itemIndex) {
                var url = the_ajax_script.ajaxurl;
                var data = {
                    action: "dwc_basket_operations",
                    basket_operation: "delete",
                    item: itemIndex
                };
                var callback = function (obj) {
                    console.log(obj);
                };
                $.post(url, data, callback, 'json');
            }

            function addToCart(id) {
                if (id != '') {
                    var data = getDonationType(id);
                    if (null == data['default_price']) {
                        var customAmount = $('#donation_amount').val();
                        if (parseFloat(customAmount) > 0) {
                            data['price'] = parseFloat(customAmount).toFixed(2);
                        } else {
                            alert("Lütfen bir bağış miktarı giriniz.");
                            return;
                        }
                    } else {
                        data['price'] = data['default_price'];
                    }
                    donationCart.push(data);
                    addToSession(data);
                    resetDonationTypeFields();
                    displayCart();

                    // var data = {
                    //     action: 'my_first_ajax_action',
                    //     post_var: 'this will be echoed back',
                    //     wasd: "wasdsad"
                    // };
                    // // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
                    // $.post(the_ajax_script.ajaxurl, data, function (response) {
                    //     alert(response);
                    // });

                    // $.ajax({
                    //         "url": the_ajax_script.ajaxurl,
                    //         "type": "post",
                    //         "data": data,
                    //         success: function (obj) {
                    //             console.log("success!");
                    //             console.log(obj);
                    //         },
                    //         error: function (obj) {
                    //             console.log("error! status:" + obj.status + "\n" + "text:" + obj.responseText);
                    //         }
                    //     }
                    // );
                }
            }

            function displayCart() {
                $('#donationCart').html('');
                for (var i in donationCart) {
                    $('#donationCart').append('<li>' + donationCart[i]['label'] + '<a style="font-size: 12px;cursor: pointer;box-shadow: none;color: red;padding-left: 8px;" class="removeFromCart" data-id="' + i + '">( Sil )</a><span style="float:right;">' + parseFloat(donationCart[i]['price']).toFixed(2) + ' TL</span></li>');
                }
                $('#donationCart').append('<hr/>');
                $('#donationCart').append('<li>Toplam<span style="float:right;">' + calculateCartTotal().toFixed(2) + '</span></li>');
                registerDeleteLinks();
            }

            function registerDeleteLinks() {
                $('a.removeFromCart').off('click').click(function (e) {
                    var id = parseInt($(this).data('id'));
                    if (id > -1) {
                        deleteDonationFromCart(id);
                    }
                });
            }

            function deleteDonationFromCart(id) {
                deleteFromSession(id);
                for (var i in donationCart) {
                    if (parseInt(id) === parseInt(i)) {
                        donationCart.splice(i, 1);
                        console.log(donationCart);
                        break;
                    }
                }
                displayCart();
            }

            // add donation types to option
            for (var i in dt) {
                $('#donation_types').append('<option value="' + dt[i]['id'] + '">' + dt[i]['label'] + '</option>');
            }

            $('#donation_types').change(function (e) {
                var id = $(this).val();
                if ('' != id) {

                    var selectedDt = getDonationType(id);
                    if (null != selectedDt['default_price']) {
                        $('#donation_amount').val(selectedDt['default_price']);
                        $('#donation_amount').attr('disabled', true);
                    } else {
                        $('#donation_amount').val(null);
                        $('#donation_amount').attr('disabled', false);
                    }
                    var temp = $('#dt' + id).data('default');
                }
            });

            $('#addToCart').click(function (e) {
                var id = parseInt($('#donation_types').val());

                if (null == id || id < 1) {
                    alert("Lütfen bir bağış tipi seçiniz.");
                    return;
                }
                addToCart(id);
            });


            $('form#donation_infos').card({
                form: 'form#donation_infos',
                placeholders: {
                    number: '1234 5678 9012 3456',
                    name: 'ADINIZ SOYADINIZ',
                    expiry: '01/2018',
                    cvc: '123'
                },
                formSelectors: {
                    numberInput: 'input#card_number',
                    expiryInput: 'input#card_expiry',
                    cvcInput: 'input#card_cvc',
                    nameInput: 'input#cardholder_name'
                },
                container: '.card-wrapper'
            });
        });

    </script>

    <?php return ob_get_clean();
}

add_shortcode('my_form', 'my_registration_form');

//
//function test_ajax_load_scripts()
//{
//// load our jquery file that sends the $.post request
//    wp_enqueue_script("ajax-test", plugin_dir_url(__FILE__) . '/ajax-test.js', array('jquery'));
//
//// make the ajaxurl var available to the above script
//    wp_localize_script('ajax-test', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
//}
//
//add_action('wp_print_scripts', 'test_ajax_load_scripts');

function dwc_basket_operations()
{
    // add/delete basket items.
    // calculate total price on each change. and return basket items with total.
    if (session_id() == '') {
        session_start();
    }
    if (!isset($_SESSION['donationBasket'])) {
        $_SESSION['donationBasket'] = [];
    }
    if (isset($_POST['basket_operation'])) {
        switch ($_POST['basket_operation']) {
            case 'add':
                array_push($_SESSION['donationBasket'], $_POST['item']);
                break;
            case 'delete':
                array_splice($_SESSION['donationBasket'], $_POST['itemIndex'], 1);
                break;
            default:
                break;
        }
    }
    $total = (float)0;
    foreach ($_SESSION['donationBasket'] as $item) {
        $total += (float)$item['price'];
    }
    echo json_encode(['total' => $total, 'items' => $_SESSION['donationBasket']], JSON_UNESCAPED_UNICODE);
    wp_die();
}

add_action('wp_ajax_dwc_basket_operations', 'dwc_basket_operations');
add_action('wp_ajax_nopriv_dwc_basket_operations', 'dwc_basket_operations');

?>
