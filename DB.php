<?php
/**
 * Database file.
 *
 * @author    Mindaugas Valinskis <mindaugas@duomenucentras.lt>
 * @copyright 2014 UAB "Duomenų centras"
 * @version   $Id: $
 */

/**
 * DB class.
 */
class DB
{


  /**
   * A helper function to get all needed records in array.
   *
   * @param string $sTable Table name.
   * @param array  $aWhere Where clause.
   *
   * @return array
   */
  public static function fetchAll($sTable, array $aWhere=array())
  {
    $aRecords = array();
    $aQuery   = select_query($sTable, '*', $aWhere);

    while ($aRow = mysql_fetch_array($aQuery)) {
      $aRecords[] = $aRow;
    }

    return $aRecords;

  }//end fetchAll()


  /**
   * A helper function to get one needed record.
   *
   * @param string $sTable  Table name.
   * @param string $sSelect Column to select.
   * @param array  $aWhere  Where clause.
   *
   * @return array
   */
  public static function fetchOne($sTable, $sSelect, array $aWhere=array())
  {
    $aRecord = mysql_fetch_array(select_query($sTable, $sSelect, $aWhere));

    if (empty($aRecord[0]) === FALSE) {
      return $aRecord[0];
    }

    return NULL;

  }//end fetchOne()


  /**
   * A helper function to get needed row records in array.
   *
   * @param string $sTable Table name.
   * @param array  $aWhere Where clause.
   *
   * @return array
   */
  public static function fetchRow($sTable, array $aWhere=array())
  {
    return mysql_fetch_array(select_query($sTable, '*', $aWhere));

  }//end fetchRow()


}//end class

?>