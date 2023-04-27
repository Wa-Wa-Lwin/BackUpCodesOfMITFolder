<?php

namespace MIT\Customer\Api\Data;

interface AccountConfigInterface {
    const STREET_LINES = 'street_lines';
    const PREFIX_SHOW = 'prefix_show';
    const PREFIX_OPTIONS = 'prefix_options';
    const MIDDLENAME_SHOW = 'middlename_show';
    const SUFFIX_SHOW = 'suffix_show';
    const SUFFIX_OPTIONS = 'suffix_options';
    const DOB_SHOW = 'dob_show';
    const TAXVAT_SHOW = 'taxvat_show';
    const GENDER_SHOW = 'gender_show';
    const TELEPHONE_SHOW = 'telephone_show';
    const COMPNAY_SHOW = 'company_show';
    const FAX_SHOW = 'fax_show';
    
    /**
     * get street lines
     * @return int
     */
    public function getStreetLines();

    /**
     * set street lines
     * @param int $streetLines
     * @return $this
     */
    public function setStreetLines($streetLines);

    /**
     * get prefix show
     * @return string
     */
    public function getPrefixShow();

    /**
     * set prefix show
     * @param string $prefixShow
     * @return $this
     */
    public function setPrefixShow($prefixShow);

    /**
     * get prefix options
     * @return string
     */
    public function getPrefixOptions();

    /**
     * set prefix options
     * @param string $prefixOptions
     * @return $this
     */
    public function setPrefixOptions($prefixOptions);

    /**
     * get middlename show
     * @return string
     */
    public function getMiddlenameShow();

    /**
     * set middlename show
     * @param string $middlenameShow
     * @return $this
     */
    public function setMiddlenameShow($middlenameShow);

    /**
     * get suffix show
     * @return string
     */
    public function getSuffixShow();

    /**
     * set suffix show
     * @param string $suffixShow
     * @return $this
     */
    public function setSuffixShow($suffixShow);

    /**
     * get suffix options
     * @return string
     */
    public function getSuffixOptions();

    /**
     * set suffix options
     * @param string $suffixOptions
     * @return $this
     */
    public function setSuffixOptions($suffixOptions);

    /**
     * get dob show
     * @return string
     */
    public function getDobShow();

    /**
     * set dob show
     * @param string $dobShow
     * @return $this
     */
    public function setDobShow($dobShow);

    /**
     * get taxvat show
     * @return string
     */
    public function getTaxvatShow();

    /**
     * set taxvat show
     * @param string $taxvatShow
     * @return $this
     */
    public function setTaxvatShow($taxvatShow);

    /**
     * get gender show
     * @return string
     */
    public function getGenderShow();

    /**
     * set gender show
     * @param string $genderShow
     * @return $this
     */
    public function setGenderShow($genderShow);

    /**
     * get telephone show
     * @return string
     */
    public function getTelephoneShow();

    /**
     * set telephone show
     * @param string $telephoneShow
     * @return $this
     */
    public function setTelephoneShow($telephoneShow);

    /**
     * get company show
     * @return string
     */
    public function getCompanyShow();

    /**
     * set company show
     * @param string $companyShow
     * @return $this
     */
    public function setCompanyShow($companyShow);

    /**
     * get fax show
     * @return string
     */
    public function getFaxShow();

    /**
     * set fax show
     * @param string $faxShow
     * @return $this
     */
    public function setFaxShow($faxShow);
}