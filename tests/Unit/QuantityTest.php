<?php

namespace Tests\Unit;

use App\ValueObjects\Quantity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QuantityTest extends TestCase
{
    public function test_conversion_to_thousandths()
    {
        $q1 = new Quantity('1');
        $this->assertEquals(1000, $q1->getThousandths());
        $this->assertEquals('1.000', $q1->toDecimalString());

        $q2 = new Quantity('1.5');
        $this->assertEquals(1500, $q2->getThousandths());
        $this->assertEquals('1.500', $q2->toDecimalString());

        $q3 = new Quantity('1.250');
        $this->assertEquals(1250, $q3->getThousandths());
        $this->assertEquals('1.250', $q3->toDecimalString());

        $q4 = new Quantity('0.001');
        $this->assertEquals(1, $q4->getThousandths());
        $this->assertEquals('0.001', $q4->toDecimalString());
    }

    public function test_exact_addition_and_subtraction()
    {
        $q1 = new Quantity('10.500');
        $q2 = new Quantity('2.250');

        $sum = $q1->add($q2);
        $this->assertEquals('12.750', $sum->toDecimalString());
        $this->assertEquals(12750, $sum->getThousandths());

        $diff = $q1->subtract($q2);
        $this->assertEquals('8.250', $diff->toDecimalString());
        $this->assertEquals(8250, $diff->getThousandths());
    }

    public function test_three_decimals_allowed()
    {
        $q = new Quantity('999.999');
        $this->assertEquals(999999, $q->getThousandths());
        $this->assertEquals('999.999', $q->toDecimalString());
    }

    public function test_rejection_of_four_decimals()
    {
        $this->expectException(InvalidArgumentException::class);
        new Quantity('1.2345');
    }

    public function test_rejection_of_scientific_notation()
    {
        $this->expectException(InvalidArgumentException::class);
        new Quantity('1e-3');
    }

    public function test_rejection_of_negative_result()
    {
        $this->expectException(InvalidArgumentException::class);
        $q1 = new Quantity('5.000');
        $q2 = new Quantity('10.000');

        $q1->subtract($q2);
    }
}
