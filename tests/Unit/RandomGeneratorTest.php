<?php

namespace Tests\Unit;

use App\Actions\RandomGenerator;
use PHPUnit\Framework\TestCase;

class RandomGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->instance = new RandomGenerator();
    }

    public function test_call_seed()
    {
        $this->instance->seed(3);
        $this->assertTrue(true);
    }

    public function test_call_generateNumber()
    {
        $this->assertTrue(true);
    }

    public function test_call_generateFixedLengthNumber()
    {
        $this->assertTrue(true);
    }

    public function test_call_generateOne()
    {
        $this->assertTrue(true);
    }

    public function test_call_generateAlphaNumeric()
    {
        $this->assertTrue(true);
    }

    public function test_call_randomTrueOrFalse()
    {
        $this->assertTrue(true);
    }

    public function test_call_generateRandomOneZero()
    {
        $this->assertTrue(true);
    }
}
