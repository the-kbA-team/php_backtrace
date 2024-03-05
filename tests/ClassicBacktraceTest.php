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
     * @return void
     */
    public function testInheritance()
    {
        $trace = new ClassicBacktrace();
        static::assertInstanceOf(Backtrace::class, $trace);
        static::assertInstanceOf(ClassicBacktrace::class, $trace);
    }

    /**
     * Test classic trace string creation.
     * @return void
     */
    public function testClassicString()
    {
        $trace1 = ClassicBacktrace::classicString(null, '/app');
        static::assertIsString($trace1);
        $steps = explode(PHP_EOL, $trace1);
        static::assertIsArray($steps);
        static::assertCount(12, $steps, $trace1);
        //Reflection has no file or line, just a class and its method.
        $regexp = sprintf("/^#0\s+%s->%s\(\) called at \[.+\]$/", preg_quote(__CLASS__), preg_quote(__FUNCTION__));
        static::assertMatchesRegularExpression($regexp, $steps[0]);
        $trace2 = (string)(new ClassicBacktrace(null, '/app'));
        static::assertSame($trace1, $trace2);
    }
}
