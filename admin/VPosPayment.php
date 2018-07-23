<?php

class VPosPayment
{
    protected $name;
    protected $cardNo;
    protected $expireMonth;
    protected $expireYear;
    protected $securityCode;
    protected $amount;

    protected $cardValidateResultRawData;
    protected $cardValidateResultData;

    protected $validatedCardProvisionResultRawData;
    protected $validatedCardProvisionResultData;

    /**
     * VPosPayment constructor.
     */
    public function __construct($name, $cardNo, $expiry, $cvv2, $amount)
    {
        $this->name = $name;
        $this->cardNo = $cardNo;
        $splittedExpiry = explode('/', $expiry);
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