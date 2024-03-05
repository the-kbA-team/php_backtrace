<?php

namespace kbATeam\PhpBacktrace;

/**
 * Class Backtrace
 * @package kbATeam\PhpBacktrace
 */
class Backtrace
{
    /**
     * The minimum number of steps between the debug_backtrace() of this class
     * and the expected first step of the backtrace.
     * The first step to remove is the constructor of this class.
     */
    const MIN_OFFSET = 1;

    /**
     * @var array<int, array<mixed>>
     */
    public $backtrace;

    /**
     * @var int
     */
    protected $steps;

    /**
     * Creates a new debug backtrace, removes the number of trace steps given
     * and removes the root path from the file path of each step.
     * @param int    $offset   The number of steps between this constructor and the expected first step of the backtrace.
     * @param string $fileRoot The root path of the files in backtrace.
     */
    public function __construct($offset = null, $fileRoot = null)
    {
        $this->backtrace = $this->sliceBacktrace($offset);
        $this->steps = count($this->backtrace);
        if (is_string($fileRoot) && $fileRoot !== '/') {
            $this->removeFileRootFromPath($fileRoot);
        }
    }

    /**
     * Remove the given number of steps from the backtrace. The minimum number
     * of steps will always be removed.
     * @param int|null $offset
     * @return array<int, array<mixed>>
     */
    private function sliceBacktrace($offset)
    {
        $offset = $this->calculateOffset($offset);
        $offset++; //step to remove: sliceBacktrace()
        return array_slice(debug_backtrace(), $offset);
    }

    /**
     * Normalize offset to either be int, string or null or return null.
     * @param mixed $offset
     * @return int|null
     */
    protected static function normalizeNumber($offset)
    {
        if (!in_array(gettype($offset), ['integer', 'double', 'bool', 'string'])) {
            return null;
        }
        if (is_string($offset) && !is_numeric($offset)) {
            return null;
        }
        /** @var int $offset */
        return (int)$offset;
    }

    /**
     * Get a valid offset.
     * @param int|null $offset
     * @return int
     */
    private function calculateOffset($offset)
    {
        $offset = static::normalizeNumber($offset);
        if ($offset === null) {
            return static::MIN_OFFSET;
        }
        $offset += static::MIN_OFFSET;
        if ($offset < static::MIN_OFFSET) {
            $offset = static::MIN_OFFSET;
        }
        return $offset;
    }

    /**
     * Remove the root path from the file paths of the backtrace.
     * @param string $fileRoot
     * @return void
     */
    private function removeFileRootFromPath($fileRoot)
    {
        //append trailing slash
        if (substr($fileRoot, -1, 1) !== '/') {
            $fileRoot .= '/';
        }
        $fileRootStrlen = strlen($fileRoot);
        foreach ($this->backtrace as &$step) {
            if (array_key_exists('file', $step)
                && strpos($step['file'], $fileRoot) === 0
            ) {
                $step['file'] = substr($step['file'], $fileRootStrlen);
            }
        }
        unset($step);
    }

    /**
     * @param array<mixed> $step
     * @return string
     */
    protected function funcString(array $step)
    {
        if (array_key_exists('class', $step)) {
            return sprintf(
                '%s%s%s(%s)',
                $step['class'],
                $step['type'],
                $step['function'],
                $this->funcArgsString($step)
            );
        }

        return sprintf(
            '%s(%s)',
            /** @phpstan-ignore-next-line */
            $step['function'],
            $this->funcArgsString($step)
        );
    }

    /**
     * @param array<mixed> $step
     * @return string
     */
    protected function funcArgsString(array $step)
    {
        if (!array_key_exists('args', $step)) {
            return '';
        }
        $result = [];
        foreach ($step['args'] as $arg) {
            $type = gettype($arg);
            switch ($type) {
                case 'string':
                case 'double':
                case 'integer':
                    $result[] = sprintf('%s', $arg);
                    break;
                case 'boolean':
                    $result[] = $arg ? 'true' : 'false';
                    break;
                default:
                    $result[] = $type;
                    break;
            }
        }
        return implode(', ', $result);
    }

    /**
     * Count the number of steps of the backtrace.
     * @return int
     */
    public function countSteps()
    {
        return $this->steps;
    }

    /**
     * @param int|null $pos
     * @param string|null $attribute
     * @return array<mixed>|string|int|object|null|mixed
     */
    public function getStep($pos = null, $attribute = null)
    {
        $pos = (int)static::normalizeNumber($pos);
        if ($pos < 0 || $pos >= $this->steps) {
            return null;
        }
        if ($attribute !== null && array_key_exists($attribute, $this->backtrace[$pos])) {
            return $this->backtrace[$pos][$attribute];
        }
        if ($attribute === null) {
            return $this->backtrace[$pos];
        }
        return null;
    }

    /**
     * Get the last step of the backtrace. Most likely where the error occurred.
     * @param string|null $attribute
     * @return array<mixed>|int|object|string|null|mixed
     */
    public function lastStep($attribute = null)
    {
        return $this->getStep(0, $attribute);
    }
}
