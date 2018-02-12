$ = jQuery;

var labels = [
    "Lütfen bir bağış miktarı giriniz.",
    "Sil",
    "TL",
    "Toplam",
    "Bağış kutunuz boş!",
    "Ad Soyad",
];

$(function () {
    var donationBasket = [];

    $(document)
        .ajaxStart(function () {
            $('#donationFormContainer').block({message: '<img src="' + ajaxLoadImgUrl + '"/>'});
        })
        .ajaxComplete(function () {
            $('#donationFormContainer').unblock();
            // setTimeout(function () {
            //
            // }, 3000);
        });

    // makes a post request to wp ajax and returns the cart and total datas. This method is intended to run on load.
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

    // run the initial method
    initialCartDataFetch();

    // gets the donation type by the given id. donation type list defined in the view page (in /public/donate_form_view.php:3)
    function getDonationType(id) {
        for (var i in dt) {
            if (dt[i]['id'] == id) {
                return dt[i];
            }
        }
        return false;
    }

    // resets donation type select and custom donation amount input
    function resetDonationTypeFields() {
        $('#donation_types').prop('selectedIndex', 0);
        $('#donation_amount').val("").attr("disabled", false);
    }

    // makes an ajax call and adds selected basket item to the session, and refreshes the card according to the returned data.
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

    // makes an ajax call and removes seleted basket item from session by the basket item id ( not donation type id), and refreshes the card according to the returned data.
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

    // calculation and adding to cart of selected donation type and amount. Called when add to cart button is clicked.
    function addToCart(id) {
        if (id != '') {
            var data = getDonationType(id);
            if (null == data['default_price']) {
                var customAmount = $('#donation_amount').val();
                if (parseFloat(customAmount) > 0) {
                    data['price'] = parseFloat(customAmount).toFixed(2);
                } else {
                    alert(labels[0]);
                    return;
                }
            } else {
                data['price'] = data['default_price'];
            }
            addToSession(data);
            resetDonationTypeFields();
        }
    }

    // Refreshes and re-prints the donation cart to the list by the given cart info fetched from ajax result.
    function displayCart(cartObj) {
        $('#donationCart').html('');
        donationBasket = cartObj.items;
        for (var i in cartObj.items) {
            var item = cartObj.items[i];
            $('#donationCart').append('<li>' + item['label'] + '<a style="font-size: 12px;cursor: pointer;box-shadow: none;color: red;padding-left: 8px;" class="removeFromCart" data-id="' + i + '">( ' + labels[1] + ' )</a><span style="float:right;">' + parseFloat(item['price']).toFixed(2) + ' ' + labels[2] + '</span></li>');
        }
        $('#donationCart').append('<hr/>');
        $('#donationCart').append('<li>' + labels[3] + '<span style="float:right;">' + cartObj.total.toFixed(2) + ' ' + labels[2] + '</span></li>');
        registerDeleteLinks();
    }

    // registers cart item delete button click event and its handler.
    function registerDeleteLinks() {
        $('a.removeFromCart').off('click').click(function (e) {
            var id = parseInt($(this).data('id'));
            if (id > -1) {
                deleteFromSession(id);
            }
        });
    }

    // adds donation types as select's option donation types defined in the view page (in /public/donate_form_view.php:3)
    for (var i in dt) {
        $('#donation_types').append('<option value="' + dt[i]['id'] + '">' + dt[i]['label'] + '</option>');
    }

    // Donation type select change event registration
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

    // Donation card add button event registration
    $('#addToCart').click(function (e) {
        var id = parseInt($('#donation_types').val());

        if (null == id || id < 1) {
            alert("Lütfen bir bağış tipi seçiniz.");
            return;
        }
        addToCart(id);
    });

    // first step continue button actions
    $('#firstStepContinueButton').click(function () {
        if (donationBasket.length < 1) {
            alert(labels[4]);
            return false;
        } else {
            $('#step1').addClass("hideStep");
            $('#step2').removeClass("hideStep").addClass("fadeInRight");
        }
    });

    // second step back button actions
    $('#secondStepBackButton').click(function () {
        $('#step2').addClass("hideStep");
        $('#step1').removeClass("hideStep").addClass("fadeInLeft");
    });

    // checks if donator infos are correctly entered
    function isReadyForThirdStep() {
        var infoFieldIds = ["name", "tel", "email", "tckn"];
        for (var i in infoFieldIds) {
            if ("" === $("#" + infoFieldIds[i]).val()) {
                return false;
            }
        }
        return true;
    }

    // makes an ajax call and saves entered donator info to database.
    function saveDonatorInfos(data, ajax_callback) {
        var url = adminUrl;
        var data = {
            action: "dwc_basket_operations",
            basket_operation: "addDonatorInfos",
            data: data
        };
        var callback = function (obj) {
            console.log(obj);
            ajax_callback();
        };
        $.post(url, data, callback, 'json');
    }

    //second step forward button actions
    $('#secondStepContinueButton').click(function () {
        if (isReadyForThirdStep() != true) {
            alert("Lütfen bilgilerinizi giriniz.");
            return false;
        }

        var data = {};
        data.name = $('#name').val();
        data.tel = $('#tel').val();
        data.email = $('#email').val();
        data.tckn = $('#tckn').val();
        data.donation_notes = $('#donation_notes').val();
        saveDonatorInfos(data, function () {
            $('#step2').addClass("hideStep");
            $('#step3').removeClass("hideStep").addClass("fadeInRight");
            // Credit card beautifier initialization.
            $('form#donation_infos').card({
                form: 'form#donation_infos',
                placeholders: {
                    number: 'xxxx xxxx xxxx xxxx',
                    name: labels[5],
                    expiry: 'xx/xxx',
                    cvc: 'xxx'
                },
                formSelectors: {
                    numberInput: 'input#card_number',
                    expiryInput: 'input#card_expiry',
                    cvcInput: 'input#card_cvc',
                    nameInput: 'input#cardholder_name'
                },
                container: '.card-wrapper'
                // masks: {
                //     cardNumber: '•' // optional - mask card number
                // },
            });
        });
    });

    // third step back button actions
    $('#thirdStepBackButton').click(function () {
        $('#step3').addClass("hideStep");
        $('#step2').removeClass("hideStep").addClass("fadeInLeft");
    });

    $('#thirdButtonContinueButton').click(function () {
        $('#donation_infos').submit();
    });

});