$ = jQuery;
$(function () {
    var donationCart = [];

    function initialCartDataFetch() {
        var url = adminUrl;
        var data = {
            action: "dwc_basket_operations",
        };
        var callback = function (obj) {
            displayCart(obj)
        };
        $.post(url, data, callback, 'json');
    }

    initialCartDataFetch();

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

    function addToSession(itemData) {
        var url = adminUrl;
        var data = {
            action: "dwc_basket_operations",
            basket_operation: "add",
            item: itemData
        };
        var callback = function (obj) {
            displayCart(obj)
        };
        $.post(url, data, callback, 'json');
    }

    function deleteFromSession(itemIndex) {
        var url = adminUrl;
        var data = {
            action: "dwc_basket_operations",
            basket_operation: "delete",
            item: itemIndex
        };
        var callback = function (obj) {
            displayCart(obj)
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
        }
    }

    function displayCart(cartObj) {
        $('#donationCart').html('');
        for (var i in cartObj.items) {
            var item = cartObj.items[i];
            $('#donationCart').append('<li>' + item['label'] + '<a style="font-size: 12px;cursor: pointer;box-shadow: none;color: red;padding-left: 8px;" class="removeFromCart" data-id="' + i + '">( Sil )</a><span style="float:right;">' + parseFloat(item['price']).toFixed(2) + ' TL</span></li>');
        }
        $('#donationCart').append('<hr/>');
        $('#donationCart').append('<li>Toplam<span style="float:right;">' + cartObj.total.toFixed(2) + '</span></li>');
        registerDeleteLinks();
    }

    function registerDeleteLinks() {
        $('a.removeFromCart').off('click').click(function (e) {
            var id = parseInt($(this).data('id'));
            if (id > -1) {
                deleteFromSession(id);
            }
        });
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