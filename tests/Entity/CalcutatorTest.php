<?php
namespace App\Tests\Entity;

use App\Entity\Calculator;
use PHPUnit\Framework\TestCase;


class CalcutatorTest extends TestCase
{
    public function testAdd()
    {
            $calculator = new Calculator();

            $result = $calculator->add(10,5);

            $this->assertEquals(15, $result);

    }
}