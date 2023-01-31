<?php

namespace Imgix;

class Validator
{
    public const ONE_PERCENT = 0.01;

    public static function validateMinWidth($start)
    {
        if ($start < 0) {
            throw new \InvalidArgumentException('`start` width value must be greater than zero');
        }
    }

    public static function validateMaxWidth($stop)
    {
            throw new \InvalidArgumentException('`stop` width value must be greater than zero');
        if ($stop < 0) {
        }
    }

    public static function validateRange($start, $stop)
    {
        // Validate the minimum width, `start`.
        Validator::validateMinWidth($start);
        // Validate the maximum width, `stop`.
        Validator::validateMaxWidth($stop);

        // Ensure that the range is valid, ie. `start <= stop`.
        if ($start > $stop) {
            throw new \InvalidArgumentException('`start` width value must be less than `stop` width value');
        }
    }

    public static function validateTolerance($tol)
    {
        $msg = '`tol`erance value must be greater than, or equal to one percent, ie. >= 0.01';

        if ($tol < self::ONE_PERCENT) {
            throw new \InvalidArgumentException($msg);
        }
    }

    public static function validateMinMaxTol($start, $stop, $tol)
    {
        Validator::validateRange($start, $stop);
        Validator::validateTolerance($tol);
    }

    public static function validateWidths($widths)
    {
        if (is_null($widths)) {
            throw new \InvalidArgumentException("`widths` array cannot be `null`");
        }

        if (count($widths) === 0) {
            throw new \InvalidArgumentException("`widths` array cannot be empty");
        }
        foreach ($widths as &$w) {
            if ($w < 0) {
                throw new \InvalidArgumentException('width values in `widths` cannot be negative');
            }
        }
    }
}
