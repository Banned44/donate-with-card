<script>
    var adminUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
    var dt =<?php echo json_encode($donationTypes);?>;
</script>
<h3><?php _e("Select Donation", "dwc-plugin"); ?></h3>
<form>
    <select id="donation_types" style="width:75%;float:left;">
        <option value="-1"><?php _e("Select donation type", "dwc-plugin"); ?></option>
    </select>
    <input id="donation_amount" type="number" minlength="0" min="0" style="margin-left:2%;width:23%;float:left;"/>
    <input type="button" id="addToCart" value="<?php _e("Add to box", "dwc-plugin"); ?>"
           style="clear:both;margin-top:12px;"/>
</form>
<h3><?php _e("Donation Box", "dwc-plugin"); ?></h3>
<div id="cartContainer" style="padding-left: 20px;">
    <ul id="donationCart">
        <hr>
        <li><?php _e("Total", "dwc-plugin"); ?> <span style="float:right;">0 TL</span></li>
    </ul>
</div>
<h3><?php _e("Your Info", "dwc-plugin"); ?></h3>
<form id="donation_infos" method="post" action="">

    <label for=""><?php _e("Your name", "dwc-plugin"); ?>
        <input type="text" name="name"/>
    </label>

    <label for=""><?php _e("Your Phone", "dwc-plugin"); ?>
        <input type="tel" name="tel"/>
    </label>

    <label for=""><?php _e("Your E-mail", "dwc-plugin"); ?>
        <input type="email" name="email"/>
    </label>

    <label for=""><?php _e("Your SSN", "dwc-plugin"); ?>
        <input type="text" name="tckn"/>
    </label>

    <label for=""><?php _e("Notes", "dwc-plugin"); ?>
        <textarea name="donation_notes" rows="5" cols="30"></textarea>
    </label>

    <div class='card-wrapper'></div>

    <input type="text" id="cardholder_name" name="name" placeholder="<?php _e("Cardholder Name", "dwc-plugin"); ?>"
           style="width:50%;float:left;"/>
    <input type="text" id="card_number" name="number" placeholder="<?php _e("Card No", "dwc-plugin"); ?>"
           style="width:50%;float:left;">
    <div style="clear:both;"></div>
    <div style="width:50%;float:left;">
        <input type="text" name="expiry" placeholder="<?php _e("Expires","dwc-plugin");?>"
    </div>
    <div style="width:50%;float:left;">
        <input type="text" id="card_cvc" name="cvc" placeholder="<?php _e("CVC Code", "dwc-plugin"); ?>"/>
    </div>
    <div style="clear:both;"></div>
    <input type="submit" name="donation" value="<?php _e("Donate", "dwc-plugin"); ?>"/>

</form>