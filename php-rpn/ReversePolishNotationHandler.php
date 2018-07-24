<?php
/**
 * @package simpletoys
 * @maintainer Vitaliy Fedorov <900dw1n@gmail.com>
 */

class ReversePolishNotationHandler
{
    const POSSIBLE_TOKENS = ['+', '*', '/', '-', '^', '(', ')'];
    const OPERATORS       = [
        '+' => 1,
        '-' => 1,
        '*' => 2,
        '/' => 2,
        '^' => 3,
    ];

    /** @var string $expression */
    protected $expression;

    /** @var callable[] $operations */
    protected static $operations = [];

    /**
     * RVN constructor.
     * @param string $expression
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
        $this->initOperations();
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * Финальное вычисление выражения
     *
     * @return float
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function calc() {
        $result_stack = [];
        foreach ($this->convertGenerator() as $token) {
            if (array_key_exists($token, static::OPERATORS)) {
                // встретили оператор - забираем последние два числа из стэка и производим вычисление
                $b = array_pop($result_stack);
                $a = array_pop($result_stack);
                if ($a === null || $b === null) {
                    throw new \InvalidArgumentException('Некорректное выражение для вычисления');
                }
                $operator = $this->getOperatorHandler($token);
                $token = $operator($a, $b);
            }
            // кладем в результирующий стек число из выражения либо вычисленный результат
            array_push($result_stack, $token);
        }

        if (count($result_stack) !== 1) {
            throw new \RuntimeException('Ошибка вычисления: стек не пуст на финальной стадии');
        }

        return array_pop($result_stack);
    }

    /**
     * Получение выражения в ОПН
     *
     * @return string
     */
    public function convert()
    {
        return implode(' ', iterator_to_array($this->convertGenerator()));
    }

    /**
     * Инициализация обработчиков операторов
     *
     * return RVN
     */
    protected function initOperations()
    {
        static::$operations = [
            '+' => function ($a, $b) {
                return $a + $b;
            },
            '-' => function ($a, $b) {
                return $a - $b;
            },
            '*' => function ($a, $b) {
                return $a * $b;
            },
            '/' => function ($a, $b) {
                return $a / $b;
            },
            '^' => function ($a, $b) {
                return pow($a, $b);
            },
        ];

        return $this;
    }

    /**
     * Разбивает строковое выражение на операторы, операнды и скобки
     *
     * @return Generator
     */
    protected function expressionStringSplit() {
        $expression = str_split(str_replace(' ', '', $this->expression));
        $numeric = '';
        $last_char = '';
        foreach ($expression as $char) {
            if (!$numeric && in_array($char, ['+', '-'])
                && (!$last_char || ($last_char != ')' && in_array($last_char, static::POSSIBLE_TOKENS)))) {
                // для чисел вида -3 или +7.5
                $numeric = $char;
            } elseif (in_array($char, static::POSSIBLE_TOKENS)) {
                if ($numeric) {
                    yield $numeric;
                    $numeric = '';
                }
                yield $char;
            } else {
                $numeric .= $char;
            }
            $last_char = $char;
        }

        if ($numeric) {
            yield $numeric;
        }
    }

    /**
     * Возвращает генератор токенов выражения в ОПН
     * @see https://dic.academic.ru/dic.nsf/ruwiki/1722670
     *
     * @return Generator
     */
    protected function convertGenerator() {
        $operations_stack = [];
        foreach ($this->expressionStringSplit() as $token) {
            if ($token === '(') {
                array_push($operations_stack, $token);
            } elseif ($token === ')') {
                while (true) {
                    $prev_operation = array_pop($operations_stack);
                    if ($prev_operation === null || $prev_operation === '(') {
                        break;
                    }
                    yield $prev_operation;
                }
            } elseif (isset(static::OPERATORS[$token])) {
                if ($operations_stack) {
                    $last_operation = array_pop($operations_stack);
                    if ($last_operation === '(' || !$last_operation || static::OPERATORS[$last_operation] < static::OPERATORS[$token]) {
                        array_push($operations_stack, $last_operation); // returning last operation back to operational stack
                    } else {
                        yield $last_operation;
                    }
                }
                array_push($operations_stack, $token);
            } else {
                yield $token;
            }
        }

        while ($operation = array_pop($operations_stack)) {
            yield $operation;
        }
    }

    /**
     * @param string $operator
     * @return callable
     * @throws \RuntimeException
     */
    private function getOperatorHandler($operator) {
        if (array_key_exists($operator, static::$operations)) {
            return static::$operations[$operator];
        }

        throw new \RuntimeException('Неизветный оператор ' . $operator . ' найден при вычислении выражения');
    }

}