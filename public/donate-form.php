<?php

add_action('wp_enqueue_scripts', 'dwc_donate_form_reg_scripts');
function dwc_donate_form_reg_scripts()
{
    wp_register_style('cardjs-css', plugins_url('donate-with-card/public/assets/css/card.css'));
    wp_register_style('animate-css', plugins_url('donate-with-card/public/assets/css/animate.min.css'));
    wp_register_style('donate-form-css', plugins_url('donate-with-card/public/assets/css/donate-form-view.css'));
    wp_register_script('cardjs1-js', plugins_url('donate-with-card/public/assets/js/card.js'));
    wp_register_script('cardjs2-js', plugins_url('donate-with-card/public/assets/js/jquery.card.js'), array('jquery'));
    wp_register_script('basket-ops-js', plugins_url('donate-with-card/public/assets/js/basket-operations.js'), array('jquery'), null, true);
}

function my_registration_form($params, $content = null)
{
    global $wpdb;
    $query = "SELECT * FROM `{$wpdb->prefix}donation-types` ";
    $donationTypes = $wpdb->get_results($query, ARRAY_A);

    extract(shortcode_atts(array(
        'type' => 'style1'
    ), $params));

    wp_enqueue_style('cardjs-css');
    wp_enqueue_style('animate-css');
    wp_enqueue_style('donate-form-css');
    wp_enqueue_script('cardjs1-js');
    wp_enqueue_script('cardjs2-js');
    wp_enqueue_script('basket-ops-js');
    ob_start();
    require_once 'includes/donate_form_view.php';
    return ob_get_clean();
}

add_shortcode('my_form', 'my_registration_form');
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
            case 'addDonatorInfos':
                $_SESSION['donatorInfos'] = $_POST['data'];
                break;
            default:
                break;
        }
    }
    $total = (float)0;
    foreach ($_SESSION['donationBasket'] as $item) {
        $total += (float)$item['price'];
    }
    echo json_encode(['total' => $total, 'items' => $_SESSION['donationBasket'], 'donatorInfos' => $_SESSION['donatorInfos']], JSON_UNESCAPED_UNICODE);
    wp_die();
}

add_action('wp_ajax_dwc_basket_operations', 'dwc_basket_operations');
add_action('wp_ajax_nopriv_dwc_basket_operations', 'dwc_basket_operations');


function dwc_donation_post_actions()
{
    if (!empty($_POST['dwc_donation_nonce'])) {
        if (!wp_verify_nonce($_POST['dwc_donation_nonce'], 'dwc_nonce_action')) {
            die('You are not authorized to perform this action.');
        } else {
            $error = null;
            if (empty($_POST['cardholder_name'])) {
                $error = new WP_Error('empty_error', __('Please enter cardholder name.', 'dwc-plugin'));
                wp_die($error->get_error_message(), __('Donation Form Error', 'dwc-plugin'));
            } else if (empty($_POST['card_number'])) {
                $error = new WP_Error('empty_error', __('Please enter card number.', 'dwc-plugin'));
                wp_die($error->get_error_message(), __('Donation Form Error', 'dwc-plugin'));
            } else if (empty($_POST['card_expiry'])) {
                $error = new WP_Error('empty_error', __('Please enter card expiry.', 'dwc-plugin'));
                wp_die($error->get_error_message(), __('Donation Form Error', 'dwc-plugin'));
            } else if (empty($_POST['card_cvc'])) {
                $error = new WP_Error('empty_error', __('Please enter CVC code.', 'dwc-plugin'));
                wp_die($error->get_error_message(), __('Donation Form Error', 'dwc-plugin'));
            } else {


                die('Its safe to do further processing on submitted data.');

                // do vpos actions
                // if successfull, add it to db and do not forget to destroy the session.
            }
        }
    }
}

add_action('init', "dwc_donation_post_actions");
