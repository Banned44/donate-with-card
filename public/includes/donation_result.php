<style>
    #donation_result_container {
        width: 100%;
    }

    #drc_img {
        display: block;
        margin: 0 auto;
    }

    #donation_result_container > h1 {
        text-align: center;
    }

    #donation_result_container > p {
        text-align: center;
        line-height: 26px;
    }

    #successful_donation_details {
        width: 400px;
        padding: 15px;
        margin: 0 auto;
        border: 1px solid #ccc;
    }

    #donationList {
        padding-bottom: 15px;
    }

    .donationItem {
        margin: 4px 0;
    }

    .donationItem .name {
        float: left;
    }

    .donationItem .amount_container {
        float: right;
    }

    .clear {
        clear: both;
    }

    #total {
        padding: 15px 0;
        border-top: 1px dashed #ccc;
    }

    #total #label {
        float: right;
    }

    #total #total_amount {
        float: right;
    }
</style>

<div id="donation_result_container">
    <img id="drc_img" src="<?php echo plugins_url("donate-with-card/public/assets/images/success.png"); ?>"
         align="center"/>
    <h1><?php _e("Bağış İşleminiz Başarılı!", "dwc-plugin"); ?></h1>
    <p>
        <?php printf(__("Sayın %s, bağış yaptığınız için teşekkür ederiz.", "dwc-plugin"), $_SESSION['donatorInfos']['name']); ?>
        <br/><?php _e("Bağış detayları aşağıda yer almaktadır.", "dwc-plugin"); ?>
    </p>
    <div id="successful_donation_details">
        <div id="donationList">
            <?php
            if (isset($_SESSION['donationBasket']) && is_array($_SESSION['donationBasket'])) {
                foreach ($_SESSION['donationBasket'] as $v) {
                    echo '<div class="donationItem"><div class="name">' . $v['label'] .
                        '</div><div class="amount_container"><span class="amount">' . $v['price'] .
                        '</span>&nbsp;' . __('TL', 'dwc-plugin') . '</div><div class="clear"></div></div>';
                }
            }
            ?>
            <div class="clear"></div>
        </div>
        <div id="total">
            <div id="total_amount">
                <span id="totalAmount">
                    <?php echo number_format((float)$_SESSION['donationTotal'], 2); ?>
                </span>
                <?php _e("TL", "dwc-plugin"); ?></div>
            <div id="label"><?php _e("Toplam:", "dwc-plugin"); ?> &nbsp;</div>
        </div>
    </div>

    <div style="text-align: center;">
        <br/>
        <a id="redirectToHomepage" href="<?php echo home_url(); ?>"><?php _e("Anasayfaya Dön", "dwc-plugin"); ?></a>
    </div>
</div>

