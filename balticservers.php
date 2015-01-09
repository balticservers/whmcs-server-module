<?php

require_once 'Api.php';
require_once 'Macro.php';
require_once 'DB.php';
require_once 'Tools.php';


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
  $aParams = array();

  $aParams['iPackageId'] = (int) $aConfig['customfields']['iSrvPackageId'];

  $aResponse = $oApi->call('suspendServer', $aParams);

  return Api::getResult($aResponse);

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
  $oApi      = new Api($aConfig);
  $aParams   = $oApi->translateConfig($aConfig);
  $aResponse = $oApi->call('orderServerPlan', $aParams);

  if ($aResponse['bSuccess'] === TRUE) {
    $iSrvPackageId = (int) $aResponse['aResult']['aPackageIDs'][0];
    $iServiceId    = (int) $aParams['iServiceId'];
    $iProductId    = (int) $aParams['iProductId'];

    Macro::setSrvPackageId($iSrvPackageId, $iServiceId, $iProductId);
  }//end if

  return Api::getResult($aResponse);

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
  $oApi      = new Api($aConfig);
  $aParams   = array('iPackageId' => (int) $aConfig['customfields']['iSrvPackageId']);
  $aResponse = $oApi->call('getServerStatus', $aParams);

  if ((bool) $aResponse['bSuccess'] === FALSE) {
    return array();
  }

  $aCommands = array();
  $sStatus   = $aResponse['aResult']['sStatus'];

  if ($sStatus === 'running' || $sStatus === 'on') {
    $aCommands['Stop server']     = 'stop';
    $aCommands['Flush IP tables'] = 'ipTablesFlush';
    $aCommands['Rebuild server']  = 'rebuild';
    $aCommands['Reboot server']   = 'reboot';
  } else if ($sStatus !== 'unknown' && $sStatus !== 'suspended') {
    $aCommands['Start server'] = 'start';
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
  $aParams = array();

  $aParams['iPackageId'] = (int) $aConfig['customfields']['iSrvPackageId'];

  $aResponse = $oApi->call('restartServer', $aParams);

  return Api::getResult($aResponse);

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
  $aParams = array();

  $aParams['iPackageId'] = (int) $aConfig['customfields']['iSrvPackageId'];

  $aResponse = $oApi->call('startServer', $aParams);

  return Api::getResult($aResponse);

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
  $aParams = array();

  $aParams['iPackageId'] = (int) $aConfig['customfields']['iSrvPackageId'];

  $aResponse = $oApi->call('stopServer', $aParams);

  return Api::getResult($aResponse);

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
  $aParams = array();

  $aParams['sNewPassword'] = $aConfig['password'];
  $aParams['iPackageId']   = (int) $aConfig['customfields']['iSrvPackageId'];

  $aResponse = $oApi->call('rebuildServer', $aParams);

  return Api::getResult($aResponse);

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
  $oApi    = new Api($aConfig);
  $aParams = array();

  $aParams['iPackageId'] = (int) $aConfig['customfields']['iSrvPackageId'];

  $aResponse = $oApi->call('flushServerIpTable', $aParams);

  return Api::getResult($aResponse);

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
  $aParams = array();

  $aParams['sNewPassword'] = $aConfig['password'];
  $aParams['iPackageId']   = (int) $aConfig['customfields']['iSrvPackageId'];

  $aResponse = $oApi->call('changeServerPassword', $aParams);

  return Api::getResult($aResponse);

}//end balticservers_ChangePassword()


/**
 * Terminates package.
 *
 * @param array $aConfig WHMCS configuration values.
 *
 * @return string
 */
function balticservers_TerminateAccount(array $aConfig)
{
  $oApi   = new Api($aConfig);
  $iPckId = (int) $aConfig['customfields']['iSrvPackageId'];
  $aRes   = $oApi->call('suspendServer', array('iPackageId' => $iPckId));

  if ((bool) $aRes['bSuccess'] === TRUE) {
    $aParams = array('aPackageIDs' => array((int) $aConfig['customfields']['iSrvPackageId']));
    $aRes    = $oApi->call('stopPackagesInvoiceRenewal', $aParams);
  }

  return Api::getResult($aRes);

}//end balticservers_TerminateAccount()


?>