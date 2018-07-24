<?php
/**
 * @package simpletoys
 * @maintainer Vitaliy Fedorov <900dw1n@gmail.com>
 */

require_once('ReversePolishNotationHandler.php');

$Handler = new ReversePolishNotationHandler('(1 + 4.5) * (3 + 3 - 2) - 16/2');

print 'Исходное выражение: ' . $Handler->getExpression() . PHP_EOL;
print 'Выражение в ОПН: ' . $Handler->convert() . PHP_EOL;
print 'Результат вычисления: ' . $Handler->calc() . PHP_EOL;