<?php

namespace kbATeam\PhpBacktrace;

/**
 * Class ClassicBacktrace
 * @package kbATeam\PhpBacktrace
 */
class ClassicBacktrace extends Backtrace
{
    /**
     * Returns the backtrace as a printable string similar to
     * debug_print_backtrace().
     * @return string
     */
    public function __toString()
    {
        return $this->getClassicString();
    }

    /**
     * Returns the backtrace as a printable string similar to
     * debug_print_backtrace().
     * @param int    $offset
     * @param string $fileRoot
     * @return string
     */
    public static function classicString($offset = null, $fileRoot = null)
    {
        $offset = (int) static::normalizeNumber($offset);
        $offset++; //this method adds to offset
        $trace = new self($offset, $fileRoot);
        return sprintf('%s', $trace->getClassicString());
    }

    /**
     * Returns the backtrace as a printable string similar to
     * debug_print_backtrace().
     * @return string
     */
    public function getClassicString()
    {
        $result = [];
        /** @var array<mixed> $step */
        foreach ($this->backtrace as $pos => $step) {
            $result[] = sprintf(
                '#%u%s %s%s',
                $pos,
                $pos < 10 ? ' ' : '',
                $this->funcString($step),
                $this->fileAndLine($step)
            );
        }
        return implode(PHP_EOL, $result);
    }

    /**
     * @param array<mixed> $step
     * @return string
     */
    private function fileAndLine(array $step)
    {
        if (array_key_exists('file', $step)) {
            return sprintf(' called at [%s:%u]', $step['file'], $step['line']);
        }
        return '';
    }
}
