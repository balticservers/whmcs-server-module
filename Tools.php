<?php
/**
 * Tools file.
 *
 * @author    Mindaugas Valinskis <mindaugas@duomenucentras.lt>
 * @copyright 2014 UAB "Duomenų centras"
 * @version   $Id: $
 */

/**
 * Tools class.
 */
class Tools
{


  /**
   * Converts currency to another.
   *
   * @param string $sFrom   Given currency of the amount.
   * @param string $sTo     To which currency convert.
   * @param float  $fAmount Amount sum to convert.
   *
   * @return float / boolean
   */
  public static function convertCurrency($sFrom, $sTo, $fAmount)
  {
    $sTo   = strtoupper($sTo);
    $sFrom = strtoupper($sFrom);

    if ($sFrom === $sTo) {
      return $fAmount;
    }

    $fRateBase = (float) DB::fetchOne('tblcurrencies', 'rate', array('default' => 1));
    $fRateTo   = (float) DB::fetchOne('tblcurrencies', 'rate', array('code' => $sTo));
    $fRateFrom = (float) DB::fetchOne('tblcurrencies', 'rate', array('code' => $sFrom));

    if ($fRateTo >= 0.00 && $fRateFrom >= 0.00 && $fRateBase > 0.00) {
      return (float) number_format((($fAmount / $fRateFrom) * $fRateTo), 2, '.', '');
    }

    return FALSE;

  }//end convertCurrency()


  /**
   * Text to cycle id. Cycles are constants.
   *
   * @param string $sText Period in word.
   *
   * @return integer
   */
  public static function parseCycleId($sText)
  {
    $sText = trim(strtolower($sText));

    if (strpos($sText, 'monthly') !== FALSE) {
      return 3;
    }

    if (strpos($sText, 'quarterly') !== FALSE) {
      return 4;
    }

    if (strpos($sText, 'semi-annually') !== FALSE) {
      return 5;
    }

    if (strpos($sText, 'annually') !== FALSE
        || strpos($sText, 'biennially') !== FALSE
        || strpos($sText, 'triennially') !== FALSE
    ) {
      return 6;
    }

    return 3;

  }//end parseCycleId()


  /**
   * Cycle id to months.
   *
   * @param integer $iCycleId Cycle id.
   *
   * @return integer
   */
  public static function cycleIdToMonths($iCycleId)
  {
    $aMonths = array(
                3 => 1,
                4 => 3,
                5 => 6,
                6 => 12
               );

    if (isset($aMonths[$iCycleId]) === FALSE) {
      return 1;
    }

    return $aMonths[$iCycleId];

  }//end cycleIdToMonths()


}//end class

?>