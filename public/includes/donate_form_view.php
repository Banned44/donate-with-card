<script>
    var adminUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
    var dt =<?php echo json_encode($donationTypes);?>;
    var ajaxLoadImgUrl = "<?php echo plugin_dir_url(__DIR__) . 'assets/images/ajax-loader.gif';?>";
</script>
<style>

    #donationFormContainer {
        margin: 0 auto;
    }

    #donationFormContainer #step1 h3, #step2 h3, #step3 h3 {
        display: block;
        font-size: 24px;
        margin: 0;
        padding: 13px 0;
    }

    #donationFormContainer #donation_type_select_container {
        width: 75%;
        float: left;
    }

    #donationFormContainer #donation_types {
        width: 100%;
        height: 48px;
        text-indent: 14px;
        border: 1px solid #bbb;
        border-radius: 3px;
    }

    #donationFormContainer #donation_amount_container {
        margin-left: 2%;
        width: 23%;
        float: left;
    }

    #donationFormContainer #addToCartButtonContainer {
    }

    #donationFormContainer #addToCart {
        float: right;
        width: 23%;
    }

    .button {
        display: block;
        height: 40px;
        margin: 0 !important;
        padding: 0 7px !important;
        background-color: #222;
        border: 0;
        -webkit-border-radius: 2px;
        border-radius: 2px;
        -webkit-box-shadow: none;
        box-shadow: none;
        color: #fff;
        cursor: pointer;
        font-size: 14px;
        font-weight: 800;
        -webkit-transition: background 0.2s;
        transition: background 0.2s;
        text-align: center;
    }

    .clear {
        clear: both;
    }

    #donationFormContainer #cartContainer {
        padding-left: 20px;
    }

    .h10 {
        height: 10px;
    }

    #firstStepContinueButton {
        width: 23%;
    }

    #donationFormContainer #step2 label {
        display block !important;
        line-height: 15px;
        font-size: 15px;
        margin: 0 0 7px 0 !important;
    }

    #donationFormContainer .input {
        display block !important;
        color: #666;
        background: #fff;
        background-image: none;
        background-image: -webkit-linear-gradient(rgba(255, 255, 255, 0), rgba(255, 255, 255, 0));
        border: 1px solid #bbb;
        -webkit-border-radius: 3px;
        border-radius: 3px;
        display: block;
        width: 98%;
        line-height: 46px;
        text-indent: 14px;

    }

    #donationFormContainer .textarea {
        display block !important;
        color: #666;
        background: #fff;
        background-image: none;
        background-image: -webkit-linear-gradient(rgba(255, 255, 255, 0), rgba(255, 255, 255, 0));
        border: 1px solid #bbb;
        -webkit-border-radius: 3px;
        border-radius: 3px;
        display: block;
        width: 94%;
        padding: 10px 2%;
        line-height: 23px;
    }

    #donationCart hr {
        background: #0a0a0a !important;
    }

    #donationFormContainer #step3 #cardholder_name {
        display: block;
    }

    #donationFormContainer #step3 .card_expiry {
        width: 49%;
    }

    .fleft {
        float: left !important;
    }

    .fright {
        float: right !important;
    }


</style>
<div id="donationFormContainer">
    <div id="step1" class="animated">
        <h3><?php _e("Bağış Seçiniz", "dwc-plugin"); ?></h3>
        <form>
            <div id="donation_type_select_container">
                <select id="donation_types">
                    <option value="-1"><?php _e("Lütfen bağış türü seçiniz", "dwc-plugin"); ?></option>
                </select>
            </div>
            <div id="donation_amount_container">
                <input id="donation_amount" type="number" class="input" minlength="0" min="0"/>
            </div>
            <div class="clear h10"></div>
            <div id="addToCartButtonContainer">
                <input type="button" class="button" id="addToCart" value="<?php _e("Kutuya Ekle", "dwc-plugin"); ?>"/>
                <div class="clear"></div>
            </div>
        </form>
        <h3><?php _e("Bağış Kutusu", "dwc-plugin"); ?></h3>
        <div id="cartContainer">
            <ul id="donationCart">
                <hr>
                <li><?php _e("Toplam", "dwc-plugin"); ?> <span
                            style="float:right;">0 <?php _e("TL", "dwc-plugin"); ?></span></li>
            </ul>
            <div class="h10"></div>
            <button class="button" id="firstStepContinueButton"><?php _e("İleri", "dwc-plugin"); ?> &raquo;
            </button>
            <div class="clear"></div>
        </div>
    </div>

    <div id="step2" class="hideStep animated">
        <h3><?php _e("Bilgileriniz", "dwc-plugin"); ?></h3>
        <label for="name"><?php _e("Adınız Soyadınız(*)", "dwc-plugin"); ?></label>
        <input type="text" id="name" name="name" class="input"/>
        <div class="h10"></div>
        <label for="tel"><?php _e("Telefonunuz(*)", "dwc-plugin"); ?></label>
        <input type="tel" id="tel" name="tel" class="input"/>
        <div class="h10"></div>
        <label for="email"><?php _e("E-Posta Adresiniz", "dwc-plugin"); ?></label>
        <input type="email" id="email" name="email" class="input"/>
        <div class="h10"></div>
        <label for="donation_notes"><?php _e("Notunuz", "dwc-plugin"); ?></label>
        <textarea name="donation_notes" id="donation_notes" rows="5" cols="30" class="textarea"></textarea>
        <div class="h10"></div>
        <div class="h10"></div>
        <button id="secondStepContinueButton" class="button" style="margin: 0 27px 0 0 !important;"><?php _e("İleri", "dwc-plugin"); ?> &raquo;
        </button>
        <button id="secondStepBackButton" class="button" ><?php _e("&laquo; Geri", "dwc-plugin"); ?> </button>
        <div class="clear"></div>
    </div>

    <div id="step3" class="hideStep animated">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="donation_infos">
            <div class='card-wrapper'></div>
            <div class="h10"></div>
            <input type="text" id="cardholder_name" name="cardholder_name"
                   placeholder="<?php _e("Kart Sahibi Adı", "dwc-plugin"); ?>" class="input" value="Test Kart"/>
            <div class="h10"></div>
            <input type="text" id="card_number" name="card_number"
                   placeholder="<?php _e("Kart Numarası", "dwc-plugin"); ?>" value="5188 9600 0004 3719" class="input"/>
            <div class="h10"></div>

            <div class="card_expiry fleft">
                <input type="text" id="card_expiry" name="card_expiry" value="11 / 20" 
                       placeholder="MM/YY" class="input" style="width: 95%;"/>
            </div>
            <div class="card_expiry fright">
                <input type="text" id="card_cvc" name="card_cvc" value="383" 
                       placeholder="CVC2" class="input" style="width: 95%;"/>
            </div>

            <div class="clear"></div>
            <div class="h10"></div>

            <?php wp_nonce_field('dwc_nonce_action', 'dwc_donation_nonce'); ?>
            <input type="hidden" name="action" value="make_donation">
            <div class="h10"></div>

        </form>
        <button id="thirdStepBackButton" class="button"
                type="button"><?php _e("&laquo; Geri", "dwc-plugin"); ?> </button>
        <button id="thirdButtonContinueButton" class="button" style="margin:0 27px 0 0 !important"
                type="button"><?php _e("Bağış Yapın", "dwc-plugin"); ?></button>
        <div class="clear"></div>
    </div>

</div>