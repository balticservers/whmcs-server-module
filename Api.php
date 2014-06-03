<?php
/**
 * Api file.
 *
 * @author    Mindaugas Valinskis <mindaugas@duomenucentras.lt>
 * @copyright 2013 UAB "Duomenų centras"
 * @version   $Id: $
 */

/**
 * Api class.
 */
class Api
{

  /**
   * Client ID.
   *
   * @var integer
   */
  private $_iClientId = 0;

  /**
   * Hashed password.
   *
   * @var string
   */
  private $_sPassWord = '';

  /**
   * URL to Public API.
   *
   * @var string
   */
  private $_sUrl = 'https://my.balticservers.com/korys/api/';

  /**
   * Environment.
   *
   * @var string
   */
  private $_sMode = 'prod';


  /**
   * Constructor.
   *
   * @param array $aConfig Module parameters.
   */
  public function __construct(array $aConfig=array())
  {
    if (isset($aConfig['configoption1'], $aConfig['configoption2']) === TRUE) {
      $this->_iClientId = $aConfig['configoption1'];
      $this->_sPassWord = sha1($aConfig['configoption2']);
    }

  }//end __construct()


  /**
   * Execute command.
   *
   * @param string $sFunction Function name to execute.
   * @param array  $aParams   Function parameters.
   *
   * @return array
   */
  public function call($sFunction, array $aParams=array())
  {
    if ($this->_iClientId === 0) {
      $sAuth = '';
    } else {
      $sAuth = $this->_iClientId.':'.$this->_sPassWord;
    }

    $ch = curl_init();

    $aOptions = array(
                 CURLOPT_URL            => $this->_sUrl.$sFunction,
                 CURLOPT_RETURNTRANSFER => TRUE,
                 CURLOPT_SSL_VERIFYPEER => TRUE,
                 CURLOPT_POST           => TRUE,
                 CURLOPT_POSTFIELDS     => json_encode($aParams),
                 CURLOPT_TIMEOUT        => 0,
                 CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
                 CURLOPT_USERPWD        => $sAuth,
                 CURLOPT_HTTPAUTH       => CURLAUTH_BASIC
                );

    curl_setopt_array($ch, $aOptions);

    $json = curl_exec($ch);
    $data = json_decode($json, TRUE);

    curl_close($ch);

    if ($data === NULL && $this->_sMode === 'dev') {
      die(var_export('JSON parse err: '.$json));
    }

    return $data;

  }//end call()


  /**
   * Convert product list into comma separated line.
   *
   * @param string $sLang User language.
   *
   * @return string
   */
  public function serverProductString($sLang)
  {
    $aResponse = $this->call('getServerProductNameList', array('sLanguage' => $sLang));

    if ($aResponse['bSuccess'] === TRUE) {
      return implode(',', $aResponse['aResult']);
    }

    return '';

  }//end serverProductString()


  /**
   * Convert period list into comma separated line.
   *
   * @return string
   */
  public function monthPeriodsString()
  {
    $aResponse = $this->call('getAvailableMonthPeriodList');

    if ($aResponse['bSuccess'] === TRUE) {
      return implode(',', $aResponse['aResult']);
    }

    return '';

  }//end monthPeriodsString()


  /**
   * Convert currency list into comma separated line.
   *
   * @return string
   */
  public function availableCurrencyString()
  {
    $aResponse = $this->call('getAvailableCurrencyList');

    if ($aResponse['bSuccess'] === TRUE) {
      return implode(',', $aResponse['aResult']);
    }

    return '';

  }//end availableCurrencyString()


  /**
   * WHMCS response to end client.
   *
   * @param array $aResponse API response.
   *
   * @return string
   */
  public function getResult(array $aResponse)
  {
    if (empty($aResponse) === TRUE) {
      return 'Unexpected error.';
    }

    $sResult = 'success';

    if ($aResponse['bSuccess'] === FALSE) {
      $sResult = end($aResponse['aError']);
    }

    return $sResult;

  }//end getResult()


  /**
   * Gets available server commands.
   *
   * @param integer $iWhmcsSid WHMCS service ID.
   * @param integer $iUserId   WHMCS User ID.
   *
   * @return string
   */
  public function getServerCommands($iWhmcsSid, $iUserId)
  {
    $aParams = array();

    $aParams['iWhmcsSid'] = $iWhmcsSid;
    $aParams['iUserId']   = $iUserId;

    $aResponse = $this->call('whmcsServerCmdList', $aParams);

    if ($aResponse['bSuccess'] === TRUE) {
      return $aResponse['aResult'];
    }

    return array();

  }//end getServerCommands()


  /**
   * Translates WHMCS configurations into normal keys.
   *
   * @param array $aConfig WHMCS configuration values.
   *
   * @return string
   */
  public static function translateConfig(array $aConfig)
  {
    $aParams = array();

    $aParams['sPlanName']   = $aConfig['configoption3'];
    $aParams['sCurrency']   = $aConfig['configoption5'];
    $aParams['sCouponCode'] = $aConfig['configoption6'];
    $aParams['iMonths']     = $aConfig['configoption4'];
    $aParams['sLanguage']   = 'en';

    $aParams['sHostName'] = $aConfig['domain'];
    $aParams['iWhmcsSid'] = $aConfig['serviceid'];
    $aParams['iUserId']   = $aConfig['clientsdetails']['userid'];

    return $aParams;

  }//end translateConfig()


  /**
   * Client contact block.
   *
   * @param array $aConfig WHMCS configuration values.
   *
   * @return string
   */
  public function getContactBlock(array $aConfig)
  {
    $aParams = array();
    $aClient = $aConfig['clientsdetails'];

    $aParams['sFirstName']   = $aClient['firstname'];
    $aParams['sLastName']    = $aClient['lastname'];
    $aParams['sCompanyName'] = $aClient['companyname'];
    $aParams['sEmail']       = $aClient['email'];
    $aParams['sAddress1']    = $aClient['address1'];
    $aParams['sAddress2']    = $aClient['address2'];
    $aParams['sCity']        = $aClient['city'];
    $aParams['sPhone']       = $aClient['phonenumber'];
    $aParams['sCountryCode'] = $aClient['countrycode'];
    $aParams['sZipCode']     = $aClient['postcode'];
    $aParams['sLanguage']    = $aClient['language'];
    $aParams['sUsername']    = $aConfig['username'];

    if ($aParams['sLanguage'] === '') {
      $aParams['sLanguage'] = 'en';
    }

    return $aParams;

  }//end getContactBlock()


  /**
   * Server options block.
   *
   * @param array $aConfig WHMCS configuration values.
   *
   * @return string
   */
  public function getOptionsBlock(array $aConfig)
  {
    $aParams = array_merge($aConfig['configoptions'], $aConfig['customfields']);
    $aParams = array_filter($aParams, 'trim');
    $aParams = array_values($aParams);

    return $aParams;

  }//end getOptionsBlock()


  /**
   * Returns list of server custom options.
   *
   * @param string $sPlanName Plan name.
   *
   * @return array
   */
  public function getServerPlanCustomization($sPlanName)
  {
    $aParams   = array('sPlanName' => $sPlanName);
    $aResponse = $this->call('getServerPlanVariants', $aParams);

    if ($aResponse['bSuccess'] === TRUE) {
      return $aResponse['aResult'];
    }

    return array();

  }//end getServerPlanCustomization()


  /**
   * Checks if plan name is available in stock.
   *
   * @param string $sPlanName Plan name.
   *
   * @return boolean
   */
  public function availableInStock($sPlanName)
  {
    $aParams   = array('sPlanName' => $sPlanName);
    $aResponse = $this->call('checkServerStock', $aParams);

    if ($aResponse['bSuccess'] === TRUE) {
      return (bool) $aResponse['aResult']['bAvailable'];
    }

    return FALSE;

  }//end availableInStock()


}//end class

?>