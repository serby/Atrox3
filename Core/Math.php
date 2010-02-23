<?php
/**
 * @package Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Advanced Maths function
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class MathControl {

	function numberOfPayments($rate, $pmt, $principle, $futureValue = 0.0, $type = 0) {
		if ($pmt == 0)
			return null;
		if ($rate == 0) {
			$numberOfPayments = -($principle + $futureValue) / $pmt;
		} else {
			$numberOfPayments = log10(($pmt * (1 + $rate * $type) - $futureValue * $rate) / ($pmt * (1 + $rate * $type) + $principle * $rate) ) / log10(1 + $rate);
		}
		return $numberOfPayments;
	}

	function pmt($rate, $numberOfPayments, $principle, $futureValue = 0.0, $type = 0) {
		if ($numberOfPayments == 0)
			return null;
		if ($rate == 0) {
			$pmt = -($principle + $futureValue) / $numberOfPayments;
		} else {
			$r = @pow(1 + $rate, $numberOfPayments);
			if ($r == false) {
				return null;
			}
			$pmt = ($futureValue + $principle * $r) / ((1 + $rate * $type) * (1 - $r) / $rate);
		}
		return $pmt;
	}

	function getTimeDifference(&$time1, $time2) {
		return strtotime($time1) - strtotime($time2);
	}

	function calculateDownloadTime($fileSizeBytes, $connectionType = IC_1MBBROADBAND) {
		$bitsPerSecond = 0;
		switch ($connectionType) {
			case IC_26KBPS:
				$bitsPerSecond = 26000;
				break;
			case IC_56KBPS:
				$bitsPerSecond = 56000;
				break;
			case IC_ISDN:
				$bitsPerSecond = 64000;
				break;
			case IC_LESSTHAN1MBBROADBAND:
				$bitsPerSecond = 500000;
				break;
			case IC_1MBBROADBAND:
				$bitsPerSecond = 1000000;
				break;
			case IC_2MBPLUSBROADBAND:
				$bitsPerSecond = 2000000;
				break;
				break;
			case IC_T1:
				$bitsPerSecond = 1500000;
				break;
			case IC_T2:
				$bitsPerSecond = 2000000;
				break;
			case IC_T3:
				$bitsPerSecond = 4400000;
				break;
			case IC_T4:
				$bitsPerSecond = 4000000;
				break;
			case IC_OC768:
				$bitsPerSecond = 39000000000000;
				break;
			default:
				$bitsPerSecond = 1000000;
		}
		$time = ($fileSizeBytes * 8) / $bitsPerSecond;
		return $time;
	}
	
	function getAge($birthdate) {
		list($year, $month, $day) = mb_split('-',mb_substr($birthdate, 0, 10));
		$currentYear = date("Y");
		$currentMonth = date("n");
		$currentDay = date("j");
		$age = $currentYear - $year;
		if ($currentMonth <= $month) {
			if ($currentMonth == $month) {
				 if ($day > $currentDay) {
							$age--;
				 }
			} else {
				$age--;
			}
		}
		return $age;
	}
}