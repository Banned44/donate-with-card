<?php

class KuveytTurkVPosPayment
{
    // Constant xml field datas
    const API_VERSION = "1.0.0";
    const TRANSACTION_TYPE = "Sale";
    const TRANSACTION_SECURITY = 3;
    const TL_CURRENCY_CODE = "0949";
    const BATCH_ID = 0;
    const INSTALLMENT_COUNT = 0;

    // Basic regexes for card types
    const VISA_REGEX = "/^4[0-9]{0,15}$/i";
    const MASTERCARD_REGEX = "/^5[1-5][0-9]{5,}|222[1-9][0-9]{3,}|22[3-9][0-9]{4,}|2[3-6][0-9]{5,}|27[01][0-9]{4,}|2720[0-9]{3,}$/i";

    private $customerId = "400235";
    private $merchantId = "496";
    private $username = "apiuser10";
    private $password = "123456";
    private $validationUrl;
    private $provisionUrl;

    /**
     * KuveytTurkVPosPaymentOld constructor.
     * @param $customerId string  Müsteri Numarasi
     * @param $merchantId string  Magaza Kodu
     * @param $username string Web Yönetim ekranalrindan olusturulan api rollü kullanici
     * @param $password string Web Yönetim ekranalrindan olusturulan api rollü kullanici sifresi
     * @param $validationUrl string 3D secure kart doğrulama adımı istek url adresi
     * @param $provisionUrl string 3D secure kart provizyon istek url adresi
     */
    public function __construct($customerId, $merchantId, $username, $password, $validationUrl, $provisionUrl)
    {
        if (!extension_loaded('curl')) {
            die('This class depends on the php Curl extension!');
        } else if (!extension_loaded('simplexml')) {
            die('This class depends on the php SimpleXML extension!');
        }
        $this->customerId = $customerId;
        $this->merchantId = $merchantId;
        $this->username = $username;
        $this->password = $password;
        $this->validationUrl = $validationUrl;
        $this->provisionUrl = $provisionUrl;
    }

    /**
     * 3D Secure VPOS Payment Card validation step operations.
     * @param $cardHolderName string Cardholder name
     * @param $cardNo string 16-digit card no
     * @param $expireMonth string 2-digit expiration month
     * @param $expireYear string 2-digit expiration year
     * @param $securityCode string 3-digit cvv2 code
     * @param $okUrl string Successful operation redirection URL. & char must be used like this: &amp;
     * @param $failUrl string Failed operation redirection URL. & char must be used like this: &amp;
     * @param $displayAmount float amount to be paid. Must be a float and has 2 digits on decimals.
     * @param $merchantOrderId string generated order number for the payment store
     * @return string html string to be placed to page for SMS authorization.
     * @throws Exception if any problem occurs
     */
    public function cardValidation($cardHolderName, $cardNo, $expireMonth, $expireYear, $securityCode, $okUrl, $failUrl,
                                   $displayAmount, $merchantOrderId)
    {
        $cardType = null;
        if (preg_match(self::VISA_REGEX, $cardNo)) {
            $cardType = "Visa";
        } else if (preg_match(self::MASTERCARD_REGEX, $cardNo)) {
            $cardType = "MasterCard";
        }
        if (null == $cardType) {
            //throw new Exception("Card type couldn't be determined!");
            $cardType = "MasterCard";
        }
        if (!is_float($displayAmount)) {
            throw new Exception("Display amount must be a float parameter and should include 2 digits in the decimals.");
        }
        $amount = (float)$displayAmount * 100;

        $hashedPassword = base64_encode(sha1($this->password, "ISO-8859-9"));
        $hashData = base64_encode(sha1($this->merchantId . $merchantOrderId . $amount . $okUrl . $failUrl .
            $this->username . $hashedPassword, "ISO-8859-9"));
        $xmlStr = '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
            . '<APIVersion>' . self::API_VERSION . '</APIVersion>'
            . '<OkUrl>' . $okUrl . '</OkUrl>'
            . '<FailUrl>' . $failUrl . '</FailUrl>'
            . '<HashData>' . $hashData . '</HashData>'
            . '<MerchantId>' . $this->merchantId . '</MerchantId>'
            . '<CustomerId>' . $this->customerId . '</CustomerId>'
            . '<UserName>' . $this->username . '</UserName>'
            . '<CardNumber>' . $cardNo . '</CardNumber>'
            . '<CardExpireDateYear>' . $expireYear . '</CardExpireDateYear>'
            . '<CardExpireDateMonth>' . $expireMonth . '</CardExpireDateMonth>'
            . '<CardCVV2>' . $securityCode . '</CardCVV2>'
            . '<CardHolderName>' . $cardHolderName . '</CardHolderName>'
            . '<CardType>' . $cardType . '</CardType>'
            . '<BatchID>' . self::BATCH_ID . '</BatchID>'
            . '<TransactionType>' . self::TRANSACTION_TYPE . '</TransactionType>'
            . '<InstallmentCount>' . self::INSTALLMENT_COUNT . '</InstallmentCount>'
            . '<Amount>' . $amount . '</Amount>'
            . '<DisplayAmount>' . $amount . '</DisplayAmount>'
            . '<CurrencyCode>' . self::TL_CURRENCY_CODE . '</CurrencyCode>'
            . '<MerchantOrderId>' . $merchantOrderId . '</MerchantOrderId>'
            . '<TransactionSecurity>' . self::TRANSACTION_SECURITY . '</TransactionSecurity>'
            . '<TransactionSide>' . self::TRANSACTION_TYPE . '</TransactionSide>'
            . '</KuveytTurkVPosMessage>';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: ' . strlen($xmlStr)));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_URL, $this->validationUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true); // test this
            $cardValidationResultRawData = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            error_log("cardValidation exception: " . $e->getMessage());

            throw $e;
        }
        return $cardValidationResultRawData;
    }

    /**
     * Converts the card validation step response to XML
     * @return SimpleXMLElement XML object of the bank response
     * @throws Exception if $_POST["AuthenticationResponse"] cannot be converted to SimpleXMLElement, no ResponseCode
     * element or is not successful.
     */
    public function cardValidationBankReturnOperations()
    {
        $authenticationResponse = $_POST["AuthenticationResponse"];
        $decodedData = urldecode($authenticationResponse);
        $xml = simplexml_load_string($decodedData);
        if ($xml === false) {
            throw new Exception("XML error!");
//        } else if (isset($xml->ResponseCode)) {
//            throw new Exception("ResponseCode object not found!");
        } else if ($xml->ResponseCode != "00") {
            throw new Exception("3D Secure ödeme sonucu hatalı! `" . $xml->ResponseCode . "-" . $xml->ResponseMessage . "`");
        }
        return $xml;
    }

    /**
     * 3D Secure VPOS Payment Card provision step operations
     * @param $mdValue string MD value of the card validation step result
     * @param $displayAmount float amount to be paid. Must be a float and has 2 digits on decimals.
     * @param $merchantOrderId string generated order number for the payment store
     * @param bool $returnRawData if true returns the bank xml string
     * @return SimpleXMLElement|string xml string to be converted to an object and to be inspected
     * @throws Exception if any errors occur.
     */
    public function cardProvision($mdValue, $displayAmount, $merchantOrderId, $returnRawData = false)
    {
        if (!is_float($displayAmount)) {
            throw new Exception("Display amount must be a float parameter and should include 2 digits in the decimals.");
        }

        $amount = (float)$displayAmount * 100;
        $hashedPassword = base64_encode(sha1($this->password, "ISO-8859-9"));
        $hashData = base64_encode(sha1($this->merchantId . $merchantOrderId . $amount . $this->username . $hashedPassword, "ISO-8859-9"));
        $xml = '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
				<HashData>' . $hashData . '</HashData>
				<MerchantId>' . $this->merchantId . '</MerchantId>
				<CustomerId>' . $this->customerId . '</CustomerId>
				<UserName>' . $this->username . '</UserName>
				<TransactionType>' . self::TRANSACTION_TYPE . '</TransactionType>
				<InstallmentCount>' . self::INSTALLMENT_COUNT . '</InstallmentCount>
				<Amount>' . $amount . '</Amount>
				<MerchantOrderId>' . $merchantOrderId . '</MerchantOrderId>
				<TransactionSecurity>' . self::TRANSACTION_SECURITY . '</TransactionSecurity>
				<KuveytTurkVPosAdditionalData>
				<AdditionalData>
					<Key>MD</Key>
					<Data>' . $mdValue . '</Data>
				</AdditionalData>
			</KuveytTurkVPosAdditionalData>
			</KuveytTurkVPosMessage>';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: ' . strlen($xml)));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_URL, $this->provisionUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $cardProvisionResultRawData = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            error_log("cardProvision exception: " . $e->getMessage());
            throw $e;
        }
        $cardProvisionResult = simplexml_load_string($cardProvisionResultRawData);
        if ($cardProvisionResult === false) {
            throw new Exception("XML error!");
//        } else if (isset($cardProvisionResult->ResponseCode)) {
//            throw new Exception("ResponseCode object not found!");
        } else if ($cardProvisionResult->ResponseCode != "00") {
            throw new Exception("3D Secure ödeme sonucu hatalı! `" . $cardProvisionResult->ResponseCode . "-" . $cardProvisionResult->ResponseMessage . "`");
        }
        return $returnRawData === true ? $cardProvisionResultRawData : $cardProvisionResult;
    }

}