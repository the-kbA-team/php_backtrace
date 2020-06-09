# PHP Backtrace

Extendable class holding the PHP backtrace minus the last n steps to avoid
showing the traces of logging.

## Usage

`composer require kba-team/php-backtrace`

### Example 1

Using the static function `classicString()` to echo the classic style output of
the backtrace, while removing the current directory from the file paths.

```php
<?php
require_once 'vendor/autoload.php';

use kbATeam\PhpBacktrace\ClassicBacktrace;

class Foo {
    public function __construct($a, $b)
    {
        $this->bar(true, 10.2);
    }
    public function bar($c, $d)
    {
        static::baz(404);
    }
    public static function baz($e)
    {
        echo 'Remove __DIR__:' . PHP_EOL;
        echo ClassicBacktrace::classicString(null, __DIR__) . PHP_EOL;
    }
}
new Foo('Hello', 'World');
```
Output:
```
Remove __DIR__:
#0  Foo::baz(404) called at [test.php:13]
#1  Foo->bar(true, 10.2) called at [test.php:9]
#2  Foo->__construct(Hello, World) called at [test.php:21]
```

### Example 2

Create an Instance of `ClassicBacktrace` and removing the last two steps from
the backtrace. Output is the same as in example 1.

```php
<?php
require_once 'vendor/autoload.php';

use kbATeam\PhpBacktrace\ClassicBacktrace;

class Foo {
    public function __construct($a, $b)
    {
        $this->bar(true, 10.2);
    }
    public function bar($c, $d)
    {
        static::baz(404);
    }
    public static function baz($e)
    {
        echo 'Increase offset by 2 steps:' . PHP_EOL;
        echo (new ClassicBacktrace(2))->getClassicString() . PHP_EOL;
    }
}
new Foo('Hello', 'World');
```
Output:
```
Increase offset by 2 steps:
#0  Foo->__construct(Hello, World) called at [/app/test.php:21]
```

### Example 3

Getting details of any step in the backtrace.

```php
<?php
require_once 'vendor/autoload.php';

use kbATeam\PhpBacktrace\Backtrace;

class Foo {
    public function __construct($a, $b)
    {
        $this->bar(true, 10.2);
    }
    public function bar($c, $d)
    {
        static::baz(404);
    }
    public static function baz($e)
    {
        echo 'Class and line of last trace step:' . PHP_EOL;
        $trace = new Backtrace(null, __DIR__);
        printf(
            'class: %s, line: %u%s',
            $trace->lastStep('class'),
            $trace->lastStep('line'),
            PHP_EOL
        );
        echo PHP_EOL . 'Function and params of trace step before the last:' . PHP_EOL;
        printf(
            'function: %s, params: %s%s',
            $trace->getStep(1, 'function'),
            implode(', ', $trace->getStep(1, 'args')),
            PHP_EOL
        );
    }
}
new Foo('Hello', 'World');
```
Output:
```
Class and line of last trace step:
class: Foo, line: 13

Function and params of trace step before the last:
function: bar, params: 1, 10.2
```