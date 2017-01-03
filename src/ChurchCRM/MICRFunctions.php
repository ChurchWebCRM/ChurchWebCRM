<?php

namespace ChurchCRM;

// Utility functions used to process MICR data

class MICRReader
{
    public $CHECKNO_FIRST = 1; // o<check>o t<route>t <account>o
  public $ROUTE_FIRST1 = 2;   // t<route>t <account>o <check>
  public $ROUTE_FIRST2 = 3;   // t<route>t o<account>o <check>
  public $NOT_RECOGNIZED = 4;

    public function IdentifyFormat($micr)
    {
        // t000000000t0000o0000000000000o ROUTE_FIRST2
    // t000000000t 0000000000o   0000 ROUTE_FIRST1
    // o000000o t000000000t 0000000000o CHECKNO_FIRST

    $firstChar = substr($micr, 0, 1);
        if ($firstChar == "o") {
            return ($this->CHECKNO_FIRST);
        } elseif ($firstChar == "t") {
            $firstSmallO = strpos($micr, "o");
            $secondSmallO = strrpos($micr, "o");
            if ($firstSmallO == $secondSmallO) {
                // Only one 'o'
        $len = strlen($micr);
                if ($len - $firstSmallO > 12) {
                    return ($this->NOT_RECOGNIZED);
                } else {
                    return ($this->ROUTE_FIRST1);
                }
            } else {
                return ($this->ROUTE_FIRST2);
            }
        }
    }

    public function FindRoute($micr)
    {
        $routeAndAccount = $this->FindRouteAndAccount($micr);
        $breakChar = strpos($routeAndAccount, "t", 1);
        return (substr($micr, 1, $breakChar - 1));
    }

    public function FindAccount($micr)
    {
        $routeAndAccount = $this->FindRouteAndAccount($micr);
        $breakChar = strpos($routeAndAccount, "t", 1);
        return (substr($routeAndAccount, $breakChar + 1, strlen($micr) - $breakChar));
    }

    public function FindRouteAndAccount($micr)
    {
        $formatID = $this->IdentifyFormat($micr);

        if ($formatID == $this->CHECKNO_FIRST) {
            $firstSmallT = strpos($micr, "t");
            return (substr($micr, $firstSmallT, strlen($micr) - $firstSmallT));
        } elseif ($formatID == $this->ROUTE_FIRST1) {
            $firstSmallO = strpos($micr, "o");
            return (substr($micr, 0, $firstSmallO));
        } elseif ($formatID == $this->ROUTE_FIRST2) {
            // t000000000t0000o0000000000000o ROUTE_FIRST2
      // check number is in the middle, strip it out for family matching
      $routeNo = substr($micr, 0, 10);
            $firstSmallO = strpos($micr, "o");
            $accountNo = substr($micr, $firstSmallO, strlen($micr) - $firstSmallO);
            return ($routeNo . $accountNo);
        } else {
            return ("");
        }
    }

    public function FindCheckNo($micr)
    {
        $formatID = $this->IdentifyFormat($micr);
        if ($formatID == $this->CHECKNO_FIRST) {
            $micrWithoutFirstO = substr($micr, 1, strlen($micr) - 1);
            $secondSmallO = strpos($micrWithoutFirstO, "o");
            return (substr($micrWithoutFirstO, 0, $secondSmallO));
        } elseif ($formatID == $this->ROUTE_FIRST1) {
            $firstSmallO = strpos($micr, "o");
            return (substr($micr, $firstSmallO + 1, strlen($micr) - $firstSmallO - 1));
        } elseif ($formatID == $this->ROUTE_FIRST2) {
            $secondSmallt = strrpos($micr, "t");
            $firstSmallO = strpos($micr, "o");
            return (substr($micr, $secondSmallt + 1, $firstSmallO - $secondSmallt - 1));
        } else {
            return ("");
        }
    }
}
