<script>
    var adminUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
    var dt =<?php echo json_encode($donationTypes);?>;
</script>
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