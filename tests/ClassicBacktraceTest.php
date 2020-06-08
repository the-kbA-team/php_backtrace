<?php

namespace Tests\kbATeam\PhpBacktrace;

use kbATeam\PhpBacktrace\Backtrace;
use kbATeam\PhpBacktrace\ClassicBacktrace;
use PHPUnit\Framework\TestCase;

/**
 * Class ClassicBacktraceTest
 * @package Tests\kbATeam\PhpBacktrace
 */
class ClassicBacktraceTest extends TestCase
{
    /**
     * Test the inheritance chain.
     */
    public function testInheritance()
    {
        $trace = new ClassicBacktrace();
        static::assertInstanceOf(Backtrace::class, $trace);
        static::assertInstanceOf(ClassicBacktrace::class, $trace);
    }

    /**
     * Test classic trace string creation.
     */
    public function testClassicString()
    {
        $trace1 = ClassicBacktrace::classicString(null, '/app');
        static::assertInternalType('string', $trace1);
        $steps = explode(PHP_EOL, $trace1);
        static::assertInternalType('array', $steps);
        static::assertCount(11, $steps, $trace1);
        //Reflection has no file or line, just a class and its method.
        static::assertSame(
            '#0  ' . __CLASS__ . '->' . __FUNCTION__ . '()',
            $steps[0]
        );
        $trace2 = (string)(new ClassicBacktrace(null, '/app'));
        static::assertSame($trace1, $trace2);
    }
}
