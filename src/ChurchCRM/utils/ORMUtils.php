<?php

namespace ChurchCRM\Utils;

class ORMUtils
{
    public static function getValidationErrors($failures)
    {
        $validationErrors = array();
        foreach ($failures as $failure) {
            array_push($validationErrors, 'Property '.$failure->getPropertyPath().': '.$failure->getMessage());
        }

        return $validationErrors;
    }
}
