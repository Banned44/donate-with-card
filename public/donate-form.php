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
    wp_register_script('blockui-js', plugins_url('donate-with-card/public/assets/js/jquery.blockUI.min.js'), array('jquery'), null, true);
}

function donation_form_display($params, $content = null)
{
    global $wpdb;
    $query = "SELECT * FROM " . DONATION_TYPES_TABLE_NAME;
    $donationTypes = $wpdb->get_results($query, ARRAY_A);

    extract(shortcode_atts(array(
        'type' => 'style1'
    ), $params));

    wp_enqueue_style('cardjs-css');
    wp_enqueue_style('animate-css');
    wp_enqueue_style('donate-form-css');
    wp_enqueue_script('cardjs1-js');
    wp_enqueue_script('cardjs2-js');
    wp_enqueue_script('blockui-js');
    wp_enqueue_script('basket-ops-js');
    ob_start();
    require_once 'includes/donate_form_view.php';
    return ob_get_clean();
}

add_shortcode('bagis_formu', 'donation_form_display');

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
    $_SESSION['donationTotal'] = $total;
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
                $error = new WP_Error('empty_error', __('Kart sahibi adını giriniz.', 'dwc-plugin'));
                wp_die($error->get_error_message(), __('Bağış Formu Hatası', 'dwc-plugin'));
            } else if (empty($_POST['card_number'])) {
                $error = new WP_Error('empty_error', __('Lütfen kart numaranızı giriniz.', 'dwc-plugin'));
                wp_die($error->get_error_message(), __('Bağış Formu Hatası', 'dwc-plugin'));
            } else if (empty($_POST['card_expiry'])) {
                $error = new WP_Error('empty_error', __('Lütfen son kullanma tarihini giriniz.', 'dwc-plugin'));
                wp_die($error->get_error_message(), __('Bağış Formu Hatası', 'dwc-plugin'));
            } else if (empty($_POST['card_cvc'])) {
                $error = new WP_Error('empty_error', __('Lütfen güvenlik kodunu (cvv2) giriniz.', 'dwc-plugin'));
                wp_die($error->get_error_message(), __('Bağış Formu Hatası', 'dwc-plugin'));
            } else {

                // wake session to get the donation items and donator infos.
                if (session_id() == '') {
                    session_start();
                }

                require_once plugin_dir_path(__FILE__) . "../admin/VPosPayment.php";
                require_once plugin_dir_path(__FILE__) . "../admin/KuveytTurkVPosPayment.php";

                $customerId = get_option(DWC_OPTION_NAME_VPOS_CUSTOMER_ID);//"400235";
                $merchantId = get_option(DWC_OPTION_NAME_VPOS_MERCHANT_ID);//"496";
                $username = get_option(DWC_OPTION_NAME_VPOS_USERNAME);//"apiuser10";
                $password = get_option(DWC_OPTION_NAME_VPOS_PASSWORD);//"123456";
                $validationUrl = get_option(DWC_OPTION_NAME_VPOS_CARDVALIDATIONURL);
                $provisionUrl = get_option(DWC_OPTION_NAME_VPOS_CARPROVISIONURL);

                try {
                    $vpos = new KuveytTurkVPosPayment($customerId, $merchantId, $username, $password, $validationUrl, $provisionUrl);
                    $cardNo = str_replace(" ", "", $_POST['card_number']);
                    $expiry = $_POST['card_expiry'];
                    $splittedExpiry = explode('/', $expiry);
                    if (is_array($splittedExpiry) && count($splittedExpiry) == 2) {
                        $expireMonth = trim($splittedExpiry[0]);
                        $expireYear = trim($splittedExpiry[1]);
                    } else {
                        throw new Exception("Kart tarih ayrıştırması sırasında hata oluştu!");
                    }
                    $merchantOrderId = "DWC-" . (new DateTime())->format('dmyHis');
                    if (!isset($_SESSION['merchantOrderId'])) {
                        $_SESSION['merchantOrderId'] = $merchantOrderId;
                    } else {
                        $merchantOrderId = $_SESSION['merchantOrderId'];
                    }
                    $redirectionHtmlText = $vpos->cardValidation($_POST['cardholder_name'], $cardNo,
                        $expireMonth, $expireYear, $_POST['card_cvc'], DWC_BANK_RETURN_URL,
                        DWC_BANK_RETURN_URL, $_SESSION['donationTotal'], $merchantOrderId);
                    echo $redirectionHtmlText;
                } catch (Exception $e) {
                    $err = $e->getMessage();
                    error_log("dwc_donation_post_actions exception: " . $err);
                    require_once plugin_dir_path(__FILE__) . "includes/donation_failed_result.php";
                }
            }
        }
    }
}

function dwc_donation_vpos_return_actions()
{
    if (!isset($_POST["AuthenticationResponse"])) {
        return;
    }
    try {
        $customerId = get_option(DWC_OPTION_NAME_VPOS_CUSTOMER_ID);//"400235";
        $merchantId = get_option(DWC_OPTION_NAME_VPOS_MERCHANT_ID);//"496";
        $username = get_option(DWC_OPTION_NAME_VPOS_USERNAME);//"apiuser10";
        $password = get_option(DWC_OPTION_NAME_VPOS_PASSWORD);//"123456";
        $validationUrl = get_option(DWC_OPTION_NAME_VPOS_CARDVALIDATIONURL);
        $provisionUrl = get_option(DWC_OPTION_NAME_VPOS_CARPROVISIONURL);

        // wake session to get the donation items and donator infos.
        if (session_id() == '') {
            session_start();
        }
        $vpos = new KuveytTurkVPosPayment($customerId, $merchantId, $username, $password, $validationUrl, $provisionUrl);
        $xml = $vpos->cardValidationBankReturnOperations();
        $md = $xml->MD;
        $merchantOrderId = "DWC-" . (new DateTime())->format('dmyHis');
        if (!isset($_SESSION['merchantOrderId'])) {
            $_SESSION['merchantOrderId'] = $merchantOrderId;
        } else {
            $merchantOrderId = $_SESSION['merchantOrderId'];
        }

        $cardProvisionResult = $vpos->cardProvision($md, (float)$_SESSION['donationTotal'], $merchantOrderId, true);
        $d = new Donations();
        $donationResult = $d->addSuccessfulDonation($_SESSION, $cardProvisionResult);
        if ($donationResult) {
            require_once plugin_dir_path(__FILE__) . "includes/donation_result.php";
            $_SESSION = null;
        } else {
            throw new Exception("Bağış başarı ile yapıldı ancak sisteme kayıt sırasında hata oluştu!");
        }
        $_POST = null;
        $_SESSION = null;
        session_destroy();
    } catch (Exception $e) {
        $err = $e->getMessage();
        error_log("dwc_donation_vpos_return_actions exception: " . $err);
        require_once plugin_dir_path(__FILE__) . "includes/donation_failed_result.php";
    }
}

//add_action('init', "dwc_donation_post_actions");
add_action('admin_post_nopriv_make_donation', 'dwc_donation_post_actions');
add_action('admin_post_make_donation', 'dwc_donation_post_actions');

add_action('admin_post_nopriv_vpos_return', 'dwc_donation_vpos_return_actions');
add_action('admin_post_vpos_return', 'dwc_donation_vpos_return_actions');