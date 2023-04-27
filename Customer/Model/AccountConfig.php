<?php

namespace MIT\Customer\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Customer\Api\Data\AccountConfigInterface;

class AccountConfig extends AbstractExtensibleModel implements AccountConfigInterface {

    /**
     * get street lines
     * @return int
     */
    public function getStreetLines(){
        return $this->getData(self::STREET_LINES);
    }

    /**
     * set street lines
     * @param int $streetLines
     * @return $this
     */
    public function setStreetLines($streetLines){
        return $this->setData(self::STREET_LINES, $streetLines);
    }

    /**
     * get prefix show
     * @return string
     */
    public function getPrefixShow(){
        return $this->getData(self::PREFIX_SHOW);
    }

    /**
     * set prefix show
     * @param string $prefixShow
     * @return $this
     */
    public function setPrefixShow($prefixShow){
        return $this->setData(self::PREFIX_SHOW, $prefixShow);
    }

    /**
     * get prefix options
     * @return string
     */
    public function getPrefixOptions(){
        return $this->getData(self::PREFIX_OPTIONS);
    }

    /**
     * set prefix options
     * @param string $prefixOptions
     * @return $this
     */
    public function setPrefixOptions($prefixOptions){
        return $this->setData(self::PREFIX_OPTIONS, $prefixOptions);
    }

    /**
     * get middlename show
     * @return string
     */
    public function getMiddlenameShow(){
        return $this->getData(self::MIDDLENAME_SHOW);
    }

    /**
     * set middlename show
     * @param string $middlenameShow
     * @return $this
     */
    public function setMiddlenameShow($middlenameShow){
        return $this->setData(self::MIDDLENAME_SHOW, $middlenameShow);
    }

    /**
     * get suffix show
     * @return string
     */
    public function getSuffixShow(){
        return $this->getData(self::SUFFIX_SHOW);
    }

    /**
     * set suffix show
     * @param string $suffixShow
     * @return $this
     */
    public function setSuffixShow($suffixShow){
        return $this->setData(self::SUFFIX_SHOW, $suffixShow);
    }

    /**
     * get suffix options
     * @return string
     */
    public function getSuffixOptions(){
        return $this->getData(self::SUFFIX_OPTIONS);
    }

    /**
     * set suffix options
     * @param string $suffixOptions
     * @return $this
     */
    public function setSuffixOptions($suffixOptions){
        return $this->setData(self::SUFFIX_OPTIONS, $suffixOptions);
    }

    /**
     * get dob show
     * @return string
     */
    public function getDobShow(){
        return $this->getData(self::DOB_SHOW);
    }

    /**
     * set dob show
     * @param string $dobShow
     * @return $this
     */
    public function setDobShow($dobShow){
        return $this->setData(self::DOB_SHOW, $dobShow);
    }

    /**
     * get taxvat show
     * @return string
     */
    public function getTaxvatShow(){
        return $this->getData(self::TAXVAT_SHOW);
    }

    /**
     * set taxvat show
     * @param string $taxvatShow
     * @return $this
     */
    public function setTaxvatShow($taxvatShow){
        return $this->setData(self::TAXVAT_SHOW, $taxvatShow);
    }

    /**
     * get gender show
     * @return string
     */
    public function getGenderShow(){
        return $this->getData(self::GENDER_SHOW);
    }

    /**
     * set gender show
     * @param string $genderShow
     * @return $this
     */
    public function setGenderShow($genderShow){
        return $this->setData(self::GENDER_SHOW, $genderShow);
    }

    /**
     * get telephone show
     * @return string
     */
    public function getTelephoneShow(){
        return $this->getData(self::TELEPHONE_SHOW);
    }

    /**
     * set telephone show
     * @param string $telephoneShow
     * @return $this
     */
    public function setTelephoneShow($telephoneShow){
        return $this->setData(self::TELEPHONE_SHOW, $telephoneShow);
    }

    /**
     * get company show
     * @return string
     */
    public function getCompanyShow(){
        return $this->getData(self::COMPNAY_SHOW);
    }

    /**
     * set company show
     * @param string $companyShow
     * @return $this
     */
    public function setCompanyShow($companyShow){
        return $this->setData(self::COMPNAY_SHOW, $companyShow);
    }

    /**
     * get fax show
     * @return string
     */
    public function getFaxShow(){
        return $this->getData(self::FAX_SHOW);
    }

    /**
     * set fax show
     * @param string $faxShow
     * @return $this
     */
    public function setFaxShow($faxShow){
        return $this->setData(self::FAX_SHOW, $faxShow);
    }
}