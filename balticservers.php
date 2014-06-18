<?php

require_once 'Api.php';


/**
 * WHMCS function.
 *
 * @return array
 */
function balticservers_ConfigOptions()
{
  $aConfig = array();

  $aConfig[] = array(
                'FriendlyName' => 'Client ID',
                'Type'         => 'text',
                'Description'  => 'Your ID from BalticServers.'
               );

  $aConfig[] = array(
                'FriendlyName' => 'Password',
                'Type'         => 'password',
                'Description'  => 'Enter the password.'
               );

  $oApi = new Api();

  $aConfig[] = array(
                'FriendlyName' => 'Product',
                'Type'         => 'dropdown',
                'Description'  => 'Select a product.',
                'Options'      => $oApi->serverProductString('en')
               );

  $aConfig[] = array(
                'FriendlyName' => 'Period',
                'Type'         => 'dropdown',
                'Description'  => 'Select month period.',
                'Options'      => $oApi->monthPeriodsString()
               );

  $aConfig[] = array(
                'FriendlyName' => 'Currency',
                'Type'         => 'dropdown',
                'Description'  => 'Select currency you want to order product.',
                'Options'      => $oApi->availableCurrencyString()
               );

  return $aConfig;

}//end balticservers_ConfigOptions()


/**
 * WHMCS function.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_SuspendAccount(array $aConfig)
{
  $oApi    = new Api($aConfig);
  $aParams = array(
              'iWhmcsSid' => (int) $aConfig['serviceid'],
              'iUserId'   => (int) $aConfig['clientsdetails']['userid'],
              'sReason'   => $aConfig['suspendreason']
             );

  $aResponse = $oApi->call('whmcsSuspendServer', $aParams);

  return $oApi->getResult($aResponse);

}//end balticservers_SuspendAccount()


/**
 * WHMCS function.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_CreateAccount(array $aConfig)
{
  $aResponse = array();
  $oApi      = new Api($aConfig);
  $aParams   = Api::translateConfig($aConfig);

  $aParams['sNewPassword'] = $aConfig['password'];
  $aParams['aContact']     = $oApi->getContactBlock($aConfig);
  $aParams['aOptions']     = $oApi->getOptionsBlock($aConfig);

  if ($aConfig['producttype'] === 'server') {
    $aResponse = $oApi->call('whmcsServerOrder', $aParams);
  }

  if (empty($aResponse) === FALSE) {
    if ($aResponse['bSuccess'] === TRUE) {
      Macro::updateServiceIpSet($aResponse['aResult']['aIPAddress'], $aParams['iWhmcsSid']);
      Macro::setServerUsername('root', $aParams['iWhmcsSid']);
    }

    return $oApi->getResult($aResponse);
  }

  return 'Product type is not supported.';

}//end balticservers_CreateAccount()


/**
 * WHMCS function.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return array
 */
function balticservers_ClientAreaCustomButtonArray(array $aConfig)
{
  $oApi = new Api($aConfig);

  $aCmdList = array();

  $aCmdList['reboot']        = 'Restart server';
  $aCmdList['rebuild']       = 'Rebuild server';
  $aCmdList['ipTablesFlush'] = 'Flush IP tables';
  $aCmdList['start']         = 'Start server';
  $aCmdList['stop']          = 'Stop server';

  $aCommands = array();
  $iWhmcsSid = (int) $aConfig['serviceid'];
  $iUserId   = (int) $aConfig['clientsdetails']['userid'];

  foreach ($oApi->getServerCommands($iWhmcsSid, $iUserId) as $sCommand) {
    $aCommands[$aCmdList[$sCommand]] = $sCommand;
  }

  return $aCommands;

}//end balticservers_ClientAreaCustomButtonArray()


/**
 * Restarts any type of server.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_reboot(array $aConfig)
{
  $oApi    = new Api($aConfig);
  $aParams = array(
              'iWhmcsSid' => (int) $aConfig['serviceid'],
              'iUserId'   => (int) $aConfig['clientsdetails']['userid']
             );

  $aResponse = $oApi->call('whmcsServerRestart', $aParams);

  return $oApi->getResult($aResponse);

}//end balticservers_reboot()


/**
 * Starts any type of server.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_start(array $aConfig)
{
  $oApi    = new Api($aConfig);
  $aParams = array(
              'iWhmcsSid' => (int) $aConfig['serviceid'],
              'iUserId'   => (int) $aConfig['clientsdetails']['userid']
             );

  $aResponse = $oApi->call('whmcsServerStart', $aParams);

  return $oApi->getResult($aResponse);

}//end balticservers_start()


/**
 * Stops any type of server.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_stop(array $aConfig)
{
  $oApi    = new Api($aConfig);
  $aParams = array(
              'iWhmcsSid' => (int) $aConfig['serviceid'],
              'iUserId'   => (int) $aConfig['clientsdetails']['userid']
             );

  $aResponse = $oApi->call('whmcsServerStop', $aParams);

  return $oApi->getResult($aResponse);

}//end balticservers_stop()


/**
 * Rebuild any type of server.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_rebuild(array $aConfig)
{
  $oApi    = new Api($aConfig);
  $aParams = Api::translateConfig($aConfig);

  // TODO: Encrypt password.
  $aParams['sNewPassword'] = $aConfig['password'];

  $aResponse = $oApi->call('whmcsServerRebuild', $aParams);

  return $oApi->getResult($aResponse);

}//end balticservers_rebuild()


/**
 * Flush Ip tables.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_ipTablesFlush(array $aConfig)
{
  $oApi      = new Api($aConfig);
  $aParams   = Api::translateConfig($aConfig);
  $aResponse = $oApi->call('whmcsServerIpTableFlush', $aParams);

  return $oApi->getResult($aResponse);

}//end balticservers_ipTablesFlush()


/**
 * Change any type of server password.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_ChangePassword(array $aConfig)
{
  $oApi    = new Api($aConfig);
  $aParams = Api::translateConfig($aConfig);

  // TODO: Encrypt password.
  $aParams['sNewPassword'] = $aConfig['password'];

  $aResponse = $oApi->call('whmcsServerSetPwd', $aParams);

  return $oApi->getResult($aResponse);

}//end balticservers_ChangePassword()


/**
 * Cancel or fraud invoice.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_TerminateAccount(array $aConfig)
{
  $oApi      = new Api($aConfig);
  $aParams   = Api::translateConfig($aConfig);
  $aResponse = $oApi->call('whmcsCancelInvoice', $aParams);

  return $oApi->getResult($aResponse);

}//end balticservers_TerminateAccount()


?>