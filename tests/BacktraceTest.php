<?php

namespace Tests\kbATeam\PhpBacktrace;

use kbATeam\PhpBacktrace\Backtrace;
use kbATeam\PhpBacktrace\ClassicBacktrace;
use PHPUnit\Framework\TestCase;

/**
 * Class BacktraceTest
 * @package Tests\kbATeam\PhpBacktrace
 */
class BacktraceTest extends TestCase
{
    /**
     * Test the default constructor settings.
     * @return void
     */
    public function testConstructorDefaults()
    {
        $trace = new Backtrace();
        static::assertSame(12, $trace->countSteps());
        static::assertSame(__CLASS__, $trace->getStep(0, 'class'));
        static::assertSame(__FUNCTION__, $trace->getStep(0, 'function'));
        static::assertSame(__CLASS__, $trace->lastStep('class'));
        static::assertSame(__FUNCTION__, $trace->lastStep('function'));
    }

    /**
     * Test whether an offset actually removes a trace step.
     * @return void
     */
    public function testOffset()
    {
        $trace = new Backtrace(1);
        static::assertSame(11, $trace->countSteps());
        static::assertSame('PHPUnit\Framework\TestCase', $trace->getStep(0, 'class'));
        static::assertSame('runTest', $trace->getStep(0, 'function'));
    }

    /**
     * @return array<mixed>
     */
    public static function provideInvalidOffsets()
    {
        return [
            [-1, 13],
            [null, 13],
            ['abc', 13],
            [0.7, 13],
            [[], 13],
            [new \stdClass(), 13]
        ];
    }

    /**
     * Test invalid offsets
     * @param int $offset
     * @param int $expectedSteps
     * @dataProvider provideInvalidOffsets
     * @return void
     */
    public function testInvalidOffsets($offset, $expectedSteps)
    {
        $trace = new Backtrace($offset);
        static::assertSame($expectedSteps, $trace->countSteps());
    }

    /**
     * Test whether removing a file root works.
     * @return void
     */
    public function testFileRoot()
    {
        $trace = new Backtrace(null, dirname(__DIR__));
        static::assertSame(
            'vendor/phpunit/phpunit/src/Framework/TestCase.php',
            $trace->getStep(1, 'file')
        );
    }

    /**
     * Test getting a single test step.
     * @return void
     */
    public function testGetStepPos()
    {
        $trace = new Backtrace();
        //Without an attribute, the whole trace step will be returned.
        static::assertIsArray($trace->getStep(0));
        //The first trace step a file
        static::assertNotNull($trace->getStep(0, 'file'));
        //There is no pos -1.
        static::assertNull($trace->getStep(-1, 'function'));
        //There is no pos after the last.
        static::assertNull($trace->getStep($trace->countSteps(), 'function'));
    }
}
