<?php
/**
 * Macro file.
 *
 * @author    Mindaugas Valinskis <mindaugas@duomenucentras.lt>
 * @copyright 2014 UAB "Duomenų centras"
 * @version   $Id: $
 */

require_once 'DB.php';

/**
 * Macro class.
 */
class Macro
{


  /**
   * Deletes given configuration option by its name.
   *
   * @param string $sConfigName Configuration option name.
   *
   * @return void
   */
  public static function deleteConfigOptionByName($sConfigName)
  {
    $iGroupId = (int) DB::fetchOne('tblproductconfiggroups', 'id', array('name' => $sConfigName));

    if ($iGroupId > 0) {
      $aOptionList = DB::fetchAll('tblproductconfigoptions', array('gid' => $iGroupId));

      foreach ($aOptionList as $aOption) {
        $aSubList = DB::fetchAll('tblproductconfigoptions', array('configid' => $aOption['id']));

        foreach ($aSubList as $aSub) {
          delete_query('tblpricing', array('relid' => $aSub['id']));
        }

        delete_query('tblproductconfigoptionssub', array('configid' => $aOption['id']));
      }

      delete_query('tblproductconfiggroups', array('id' => $iGroupId));
      delete_query('tblproductconfiglinks', array('gid' => $iGroupId));
      delete_query('tblproductconfigoptions', array('gid' => $iGroupId));
    }

  }//end deleteConfigOptionByName()


  /**
   * Deletes given configuration option by its name.
   *
   * @param array   $aIpSet    IP addresses.
   * @param integer $iWhmcsSid Order / service id.
   *
   * @return void
   */
  public static function updateServiceIpSet(array $aIpSet, $iWhmcsSid)
  {
    $aIPList = array();
    $iCount  = count($aIpSet);

    for ($i = 1; $i < $iCount; $i++) {
      $aIPList[] = $aIpSet[$i];
    }

    update_query(
      'tblhosting',
      array(
       'assignedips' => implode(' ', $aIPList),
       'dedicatedip' => $aIpSet[0]
      ),
      array('id' => $iWhmcsSid)
    );

  }//end updateServiceIpSet()


  /**
   * Sets user name.
   *
   * @param string  $sUserName Server login user name.
   * @param integer $iWhmcsSid Order / service id.
   *
   * @return void
   */
  public static function setServerUsername($sUserName, $iWhmcsSid)
  {
    update_query(
      'tblhosting',
      array('username' => $sUserName),
      array('id' => $iWhmcsSid)
    );

  }//end setServerUsername()


}//end class

?>