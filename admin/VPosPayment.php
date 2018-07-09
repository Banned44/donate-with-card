<?php

class VPosPayment
{
    private $name;
    private $cardNo;
    private $expireMonth;
    private $expireYear;
    private $securityCode;
    private $amount;

    private $cardValidateResultRawData;
    private $cardValidateResultData;

    private $validatedCardProvisionResultRawData;
    private $validatedCardProvisionResultData;

    /**
     * VPosPayment constructor.
     */
    public function __construct($name, $cardNo, $expiry, $cvv2, $amount)
    {
        $this->name = $name;
        $this->cardNo = $cardNo;
        $splittedExpiry = split('/', $expiry);
        if (is_array($splittedExpiry) && count($splittedExpiry) == 2) {
            $this->expireMonth = trim($splittedExpiry[0]);
            $this->expireYear = trim($splittedExpiry[1]);
        }
        $this->securityCode = $cvv2;
        $this->amount = $amount;
    }

    public function firstStepCardValidation()
    {

    }

    public function getProvisionFromValidatedCard()
    {

    }
}