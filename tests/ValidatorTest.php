<?php

namespace Imgix\Tests;

use Imgix\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public const LESS_THAN_ZERO = -1;

    /**
     * Test `validateMinWidth` throws if passed a value less than
     * zero.
     */
    public function testValidateMinWidth()
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::validateMinWidth(self::LESS_THAN_ZERO);
    }

    /**
     * Test `validateMaxWidth` throws if passed a value less than
     * zero.
     */
    public function testValidateMaxWidth()
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::validateMaxWidth(self::LESS_THAN_ZERO);
    }

    /**
     * Test `validateRange` throws if passed an invalid range,
     * ie. if `START > STOP`.
     */
    public function testValidateRange()
    {
        $this->expectException(InvalidArgumentException::class);
        $start = 400;
        $stop = 100;
        Validator::validateRange($start, $stop);
    }

    /**
     * Test `validateTolerance` throws if passed a `tol`erance
     * that is less than one percent.
     */
    public function testValidateTolerance()
    {
        $this->expectException(InvalidArgumentException::class);
        $lessThanOnePercent = 0.001;
        Validator::validateTolerance($lessThanOnePercent);
    }

    /**
     * Test `validateWidths` throws if passed a `null` array.
     */
    public function testValidateWidthsNullArray()
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::validateWidths(null);
    }

    /**
     * Test `validateWidths` throws if passed an empty array.
     */
    public function testValidateWidthsEmptyArray()
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::validateWidths([]);
    }

    /**
     * Test `validateWidths` throws if passed negative values.
     */
    public function testValidateWidthsNegativeValues()
    {
        $this->expectException(InvalidArgumentException::class);
        Validator::validateWidths([0, -1, 100]);
    }
}
