<?php
/**
 * Tools file.
 *
 * @author    Mindaugas Valinskis <mindaugas@duomenucentras.lt>
 * @copyright 2014 UAB "Duomenų centras"
 * @version   $Id: $
 */

require_once 'DB.php';

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


}//end class

?>