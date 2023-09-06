<?php

/**
 * Вычисление арифметический выражений в обратной польской нотации.
 * В качестве разделителя символов в выражении используется пробел.
 */
class PolishCalculator {

  private const TYPE_UNARY = 'unary';
  private const TYPE_BINARY = 'binary';

  /** @var string[] */
  private $expression = [];
  /** @var array */
  private $stack = [];
  /** @var array */
  private $known_operations = [
    '+' => ['plus', self::TYPE_BINARY],
    '-' => ['minus', self::TYPE_BINARY],
    '/' => ['div', self::TYPE_BINARY],
    '*' => ['mul', self::TYPE_BINARY],
    '^' => ['exp', self::TYPE_BINARY],
    'sin' => ['sin', self::TYPE_UNARY]
  ];

  /**
   * @param string $input
   * @throws InvalidArgumentException
   */
  public function __construct(string $input) {
    $this->expression = explode(' ', trim($input));
    if (empty($this->expression)) {
      throw new InvalidArgumentException('Empty input');
    }
  }

  /**
   * @return int|float
   * @throws InvalidArgumentException
   */
  public function calc() {
    foreach ($this->expression as $symbol) {
      if (is_numeric($symbol)) {
        if (ctype_digit($symbol)) {
          $argument = (int) $symbol;
        } else {
          $argument = (float) $symbol;
        }
        $this->stack[] = $argument;
      } else {
        [$operation, $type] = $this->getOperation($symbol);

        $arguments = [];
        $arguments[] = array_pop($this->stack);
        if ($type === self::TYPE_BINARY) {
          array_unshift($arguments, array_pop($this->stack));
        }

        $this->stack[] = call_user_func_array([$this, $operation], $arguments);
      }
    }

    if (count($this->stack) !== 1) {
      throw new InvalidArgumentException('Invalid expression');
    }
    return reset($this->stack);
  }

  /**
   * @param string $symbol
   * @return array
   * @throws RuntimeException
   */
  private function getOperation(string $symbol): array {
    if (!array_key_exists($symbol, $this->known_operations)) {
      throw new RuntimeException('Unknown operation: ' . $symbol);
    }

    $operation = $this->known_operations[$symbol];
    return ['calc' . ucfirst($operation[0]), $operation[1]];
  }

  /**
   * @param int|float $left
   * @param int|float $right
   * @return int|float
   */
  private function calcMinus($left, $right) {
    return $left - $right;
  }

  /**
   * @param int|float $left
   * @param int|float $right
   * @return int|float
   */
  private function calcPlus($left, $right) {
    return $left + $right;
  }

  /**
   * @param int|float $left
   * @param int|float $right
   * @return int|float
   * @throws RuntimeException
   */
  private function calcDiv($left, $right) {
    if ($right === 0) {
      throw new RuntimeException('Division by zero');
    }
    return $left / $right;
  }

  /**
   * @param int|float $left
   * @param int|float $right
   * @return int|float
   */
  private function calcMul($left, $right) {
    return $left * $right;
  }

  /**
   * @param int|float $left
   * @param int|float $right
   * @return int|float
   */
  private function calcExp($left, $right) {
    return pow($left, $right);
  }

  /**
   * @param int|float $argument
   * @return int|float
   */
  private function calcSin($argument) {
    return sin($argument);
  }
}