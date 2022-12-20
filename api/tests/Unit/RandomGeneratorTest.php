<?php

namespace Tests\Unit;

use App\Actions\RandomGenerator;
use PHPUnit\Framework\TestCase;
use TypeError;

class RandomGeneratorTest extends TestCase
{
    private object $instance;

    protected function setUp(): void
    {
        $this->instance = new RandomGenerator();
    }

    public function test_random_generator_call_seed()
    {
        $this->assertIsNumeric($this->instance->seed(1));
        $this->assertIsNumeric($this->instance->seed(-1));
        $this->assertIsNumeric($this->instance->seed(29384723));
        $this->assertIsNumeric($this->instance->seed(intval(34.3)));
        $this->assertIsNumeric($this->instance->seed(0));
    }

    public function test_random_generator_call_generateNumber()
    {
        $this->assertIsNumeric($this->instance->generateNumber(0, 100));
        $this->assertIsNumeric($this->instance->generateNumber(-1, 100));
        $this->assertIsNumeric($this->instance->generateNumber(0, -100));
        $this->assertIsNumeric($this->instance->generateNumber(10, 1));
        $this->assertIsNumeric($this->instance->generateNumber(1023243, 31));
        $this->assertIsNumeric($this->instance->generateNumber(0, 0));
    }

    public function test_random_generator_call_generateFixedLengthNumber()
    {
        $val = $this->instance->generateFixedLengthNumber(0);

        $this->assertIsNumeric($val);
        $this->assertTrue(strlen((string) $val) == 2);

        $val = $this->instance->generateFixedLengthNumber(1);

        $this->assertIsNumeric($val);
        $this->assertTrue(strlen((string) $val) == 2);

        $val = $this->instance->generateFixedLengthNumber(3);

        $this->assertIsNumeric($val);
        $this->assertTrue(strlen((string) $val) == 3);

        $val = $this->instance->generateFixedLengthNumber(10);

        $this->assertIsNumeric($val);
        $this->assertTrue(strlen((string) $val) == 10);
    }

    public function test_random_generator_call_generateOne()
    {
        $this->assertIsNumeric($this->instance->generateOne(0));
        $this->assertIsNumeric($this->instance->generateOne(-2));
    }

    public function test_random_generator_call_generateAlphaNumeric()
    {
        $this->assertEmpty($this->instance->generateAlphaNumeric(0));
        $this->assertEmpty($this->instance->generateAlphaNumeric(-1));
        $this->assertNotEmpty($this->instance->generateAlphaNumeric(1));
        $this->assertNotEmpty($this->instance->generateAlphaNumeric(10));
        $this->assertNotEmpty($this->instance->generateAlphaNumeric(100));
    }

    public function test_random_generator_call_randomTrueOrFalse()
    {
        $this->assertIsBool($this->instance->randomTrueOrFalse());
        $this->assertIsBool($this->instance->randomTrueOrFalse(0));
        $this->assertIsBool($this->instance->randomTrueOrFalse(-10));

        $this->assertIsArray($this->instance->randomTrueOrFalse(10));
        $this->assertIsArray($this->instance->randomTrueOrFalse(100));
    }

    public function test_random_generator_call_randomTrueOrFalse_sending_null_parameter()
    {
        $this->expectException(TypeError::class);
        $this->instance->randomTrueOrFalse(null);
    }

    public function test_random_generator_call_generateRandomOneZero()
    {
        $this->assertIsNumeric($this->instance->generateRandomOneZero(-1));
        $this->assertIsNumeric($this->instance->generateRandomOneZero(0));
        $this->assertIsNumeric($this->instance->generateRandomOneZero(2));
    }
}
