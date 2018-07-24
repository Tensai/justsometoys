<?php

require_once('ReversePolishNotationHandler.php');

class ReversePolishNotationHandlerTest extends PHPUnit\Framework\TestCase
{
    public function providerPublicMethods()
    {
        return [
            ['7 + (5 - 2) * 4', '7 5 2 - 4 * +', 19],
            ['8 / (2 + 2) ^ 2', '8 2 2 + 2 ^ /', 0.5],
            ['3 + 4 * 2 / (1 - 5)^2', '3 4 2 * 1 5 - 2 ^ / +', 3.5],
            ['(1 + 4.5) * (3 + 3 - 2) - 16/2', '1 4.5 + 3 3 + 2 - * 16 2 / -', 14],
            ['-2 + 0.5 * 3 * 4 + (13 - 9) * (3 * -3 + 19)', '-2 0.5 3 * 4 * 13 9 - 3 -3 * 19 + * + +', 44],
        ];
    }

    /**
     * @dataProvider providerPublicMethods
     * @param string $expression
     * @param string $expected_convert
     * @param float $expected_result
     */
    public function testPublicMethods($expression, $expected_convert, $expected_result)
    {
        $Handler = new ReversePolishNotationHandler($expression);
        $this->assertEquals($expression, $Handler->getExpression(), 'Некоректное поведение getExpression()');
        $this->assertEquals($expected_convert, $Handler->convert(), 'Некорректное поведение convert()');
        $this->assertEquals($expected_result, $Handler->calc(), 'Некорректное поведение calc()');
    }

}