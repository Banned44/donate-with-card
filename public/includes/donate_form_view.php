<script>
    var adminUrl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
    var dt =<?php echo json_encode( $donationTypes );?>;

    var ajaxLoadImgUrl = "<?php echo plugin_dir_url( __DIR__ ) . 'assets/images/ajax-loader.gif';?>";


</script>
<div id="donationFormContainer">
    <div id="step1" class="animated">
        <h3><?php _e( "Select Donation", "dwc-plugin" ); ?></h3>
        <form>
            <div style="width:75%;float:left;">
                <select id="donation_types" style="width: 100%;">
                    <option value="-1"><?php _e( "Select donation type", "dwc-plugin" ); ?></option>
                </select>
            </div>
            <div style="margin-left:2%;width:20%;float:left;">
                <input id="donation_amount" type="number" minlength="0" min="0"
                       style=""/>
            </div>
            <div style="clear:both;height:40px;">
                <input type="button" id="addToCart" value="<?php _e( "Add to box", "dwc-plugin" ); ?>"
                       style="float:right;display: block;height: 40px;margin-top:12px;"/>
            </div>
        </form>
        <h3><?php _e( "Donation Box", "dwc-plugin" ); ?></h3>
        <div id="cartContainer" style="padding-left: 20px;">
            <ul id="donationCart">
                <hr>
                <li><?php _e( "Total", "dwc-plugin" ); ?> <span style="float:right;">0 TL</span></li>
            </ul>
        </div>
        <button id="firstStepContinueButton"><?php _e( "Your Info", "dwc-plugin" ); ?> &raquo;</button>
        <div class="clear"></div>
    </div>
    <div id="step2" class="hideStep animated">
        <h3><?php _e( "Your Info", "dwc-plugin" ); ?></h3>
        <label for=""><?php _e( "Your name", "dwc-plugin" ); ?>
            <input type="text" id="name" name="name"/>
        </label>

        <label for=""><?php _e( "Your Phone", "dwc-plugin" ); ?>
            <input type="tel" id="tel" name="tel"/>
        </label>

        <label for=""><?php _e( "Your E-mail", "dwc-plugin" ); ?>
            <input type="email" id="email" name="email"/>
        </label>

        <label for=""><?php _e( "Your SSN", "dwc-plugin" ); ?>
            <input type="text" id="tckn" name="tckn" maxlength="11"/>
        </label>

        <label for=""><?php _e( "Notes", "dwc-plugin" ); ?>
            <textarea name="donation_notes" id="donation_notes" rows="5" cols="30"></textarea>
        </label>
        <button id="secondStepContinueButton"><?php _e( "Card Infos", "dwc-plugin" ); ?>&raquo;</button>
        <button id="secondStepBackButton"><?php _e( "&laquo; Back", "dwc-plugin" ); ?> </button>
        <div class="clear"></div>
    </div>
    <div id="step3" class="hideStep animated">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="donation_infos">
            <div class='card-wrapper'></div>
            <input type="text" id="cardholder_name" name="cardholder_name"
                   placeholder="<?php _e( "Cardholder Name", "dwc-plugin" ); ?>"
                   style="width:50%;float:left;"/>
            <input type="text" id="card_number" name="card_number" placeholder="<?php _e( "Card No", "dwc-plugin" ); ?>"
                   style="width:50%;float:left;">
            <div style="clear:both;"></div>
            <div style="width:50%;float:left;">
                <input type="text" id="card_expiry" name="card_expiry"
                       placeholder="<?php _e( "Expires", "dwc-plugin" ); ?>"
            </div>
            <div style="width:50%;float:left;">
                <input type="text" id="card_cvc" name="card_cvc"
                       placeholder="<?php _e( "CVC Code", "dwc-plugin" ); ?>"/>
            </div>
            <div style="clear:both;"></div>
            <!--        <input type="submit" name="donation" value=""/>-->
			<?php wp_nonce_field( 'dwc_nonce_action', 'dwc_donation_nonce' ); ?>
            <input type="hidden" name="action" value="make_donation">
        </form>
        <button id="thirdStepBackButton" type="button"><?php _e( "&laquo; Back", "dwc-plugin" ); ?> </button>
        <button id="thirdButtonContinueButton" type="button"><?php _e( "Donate", "dwc-plugin" ); ?></button>
        <div class="clear"></div>
    </div>

</div>