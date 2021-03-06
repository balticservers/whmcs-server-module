<?php

add_hook('ProductEdit', 1, 'hook_balticservers_ProductEdit');


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

  $sPath = dirname(__FILE__).'/';

  require_once $sPath.'Api.php';
  require_once $sPath.'Macro.php';
  require_once $sPath.'DB.php';
  require_once $sPath.'Tools.php';
  
  $aGroupConfig = DB::fetchRow('tblproductconfiggroups', array('name' => $aConfig['name']));

  if (empty($aGroupConfig) === FALSE) {
    return;
  }

  $oApi       = new Api();
  $iProductId = (int) $aConfig['pid'];

  $aSrvPck = DB::fetchRow(
    'tblcustomfields',
    array(
     'relid'     => $iProductId,
     'type'      => 'product',
     'fieldname' => 'iSrvPackageId'
    )
  );

  if (empty($aSrvPck) === TRUE) {
    // Custom field: server package id.
    insert_query(
      'tblcustomfields',
      array(
       'type'        => 'product',
       'fieldname'   => 'iSrvPackageId',
       'relid'       => $iProductId,
       'fieldtype'   => 'text',
       'description' => 'System required field',
       'regexpr'     => '[0-9]+',
       'adminonly'   => 'on'
      )
    );
  }

  $aParams     = $oApi->translateConfig($aConfig);
  $aOptionList = $oApi->getServerPlanCustomization($aParams['sPlanName']);

  if (empty($aOptionList) === TRUE) {
    return;
  }

  $iGroupId = insert_query('tblproductconfiggroups', array('name' => $aConfig['name']));
  $aOptions = array();

  foreach ($aOptionList as $aOption) {
    $i = 1;

    do {
      if ($i === 1) {
        $sTempOptionName = $aOption['sName'];
      } else {
        $sTempOptionName = $aOption['sName'].$i;
      }

      $i++;
    } while (in_array($sTempOptionName, $aOptions) === TRUE);

    $iOptionId = insert_query(
      'tblproductconfigoptions',
      array(
       'gid'        => $iGroupId,
       'optionname' => $sTempOptionName,
       'optiontype' => 1
      )
    );

    $aOptions[] = $sTempOptionName;

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


?>
