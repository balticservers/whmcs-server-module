<?php

require_once 'Api.php';
require_once 'Macro.php';
require_once 'DB.php';
require_once 'Tools.php';

add_hook('ProductEdit', 1, 'hook_balticservers_ProductEdit');
add_hook('ShoppingCartValidateProductUpdate', 1, 'hook_balticservers_ShoppingCartValidateProductUpdate');


/**
 * Runs when editing a product.
 *
 * @param array $aConfig Saved parameters.
 *
 * @return void
 */
function hook_balticservers_ProductEdit(array $aConfig)
{
  if (strtolower($aConfig['servertype']) !== 'balticservers') {
    return;
  }

  $aGroupConfig = DB::fetchRow('tblproductconfiggroups', array('name' => $aConfig['name']));

  if (empty($aGroupConfig) === FALSE) {
    return;
  }

  $oApi = new Api();

  $iProductId = (int) $aConfig['pid'];
  $aParams    = Api::translateConfig($aConfig);

  $aOptionList = $oApi->getServerPlanCustomization($aParams['sPlanName']);
  $iGroupId    = insert_query('tblproductconfiggroups', array('name' => $aConfig['name']));

  foreach ($aOptionList as $aOption) {
    $iOptionId = insert_query(
      'tblproductconfigoptions',
      array(
       'gid'        => $iGroupId,
       'optionname' => $aOption['sName'],
       'optiontype' => 1
      )
    );

    foreach ($aOption['aVariants'] as $aVariant) {
      $iSubId = insert_query(
        'tblproductconfigoptionssub',
        array(
         'configid'   => $iOptionId,
         'optionname' => $aVariant['sName']
        )
      );

      foreach (DB::fetchAll('tblcurrencies') as $aCurrency) {
        $fPrice = Tools::convertCurrency($aVariant['sCurrency'], $aCurrency['code'], $aVariant['fPrice']);

        $fMonthlyPrice = 0.00;
        $fSetupPrice   = 0.00;

        if ((bool) $aVariant['bIsOnce'] === TRUE) {
          $fSetupPrice = $fPrice;
        } else {
          $fMonthlyPrice = $fPrice;
        }

        insert_query(
          'tblpricing',
          array(
           'relid'     => $iSubId,
           'type'      => 'configoptions',
           'currency'  => $aCurrency['id'],
           'monthly'   => $fMonthlyPrice,
           'msetupfee' => $fSetupPrice
          )
        );
      }//end foreach
    }//end foreach
  }//end foreach

  insert_query(
    'tblproductconfiglinks',
    array(
     'gid' => $iGroupId,
     'pid' => $iProductId
    )
  );

}//end hook_balticservers_ProductEdit()


/**
 * Runs after updating cart, can use global $errormessage to pass back error.
 *
 * @param array $aItem Cart item.
 *
 * @return string
 */
function hook_balticservers_ShoppingCartValidateProductUpdate(array $aItem)
{
  $aProduct = DB::fetchRow('tblproducts', array('id' => $GLOBALS['pid']));

  if (strtolower($aProduct['servertype']) !== 'balticservers') {
    return '';
  }

  $oApi    = new Api();
  $aParams = Api::translateConfig($aProduct);

  if ($oApi->availableInStock($aParams['sPlanName']) === FALSE) {
    return $aProduct['name'].' is out of stock.';
  }

}//end hook_balticservers_ShoppingCartValidateProductUpdate()


?>