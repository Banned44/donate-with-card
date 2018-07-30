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
    <img id="drc_img" src="<?php echo plugins_url("donate-with-card/public/assets/images/fail.png"); ?>"
         align="center"/>
    <h1><?php _e("Bağış İşlemi Sırasında Hata!", "dwc-plugin"); ?></h1>
    <p>
        <br/><?php printf(__("Detaylar: %s", "dwc-plugin"), $err); ?>
    </p>
    <div style="text-align: center;">
        <a id="redirectToHomepage" href="<?php echo home_url(); ?>"><?php _e("Anasayfaya Dön", "dwc-plugin"); ?></a>
    </div>
</div>

<script>
    setTimeout(function () {
        var link = document.getElementById("redirectToHomepage");
        link.click();
    }, 5000);
</script>