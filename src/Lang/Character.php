<?php
declare(strict_types=1);

namespace BlackBonjour\Stdlib\Lang;

use BlackBonjour\Stdlib\Exception\InvalidArgumentException;
use BlackBonjour\Stdlib\Util\Assert;
use TypeError;

/**
 * Represents a single character
 *
 * @author    Erick Dyck <info@erickdyck.de>
 * @since     04.12.2017
 * @package   BlackBonjour\Stdlib\Lang
 * @copyright Copyright (c) 2017 Erick Dyck
 */
class Character extends StdObject implements Comparable
{
    public const DEFAULT_VALUE = '';

    /** @var string */
    protected $data;

    /**
     * Constructor
     *
     * @param string $char
     * @throws InvalidArgumentException
     */
    public function __construct(string $char = self::DEFAULT_VALUE)
    {
        if (mb_strlen($char) > 1) {
            throw new InvalidArgumentException('Only one character can be represented!');
        }

        $this->data = $char;
    }

    /**
     * This strings value
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->data;
    }

    /**
     * Determines the number of char values needed to represent the specified character
     *
     * @param Character|string $char
     * @return int
     * @throws InvalidArgumentException
     * @throws TypeError
     */
    public static function charCount($char) : int
    {
        self::handleIncomingChar($char);
        return unpack('N', mb_convert_encoding($char, 'UCS-4BE', 'UTF-8'))[1] > 0xFFFF ? 2 : 1;
    }

    /**
     * Returns the unicode code point at specified index
     *
     * @param CharSequence|self[] $chars
     * @param int                 $index
     * @param int                 $limit
     * @return int
     * @throws InvalidArgumentException
     */
    public static function codePointAt($chars, int $index, int $limit = null) : int
    {
        if (\is_array($chars) === false && ($chars instanceof CharSequence) === false) {
            throw new InvalidArgumentException('Only char arrays and sequences can be processed!');
        }

        // Validate char array
        if (\is_array($chars)) {
            $test = reset($chars);

            if ($test === false || ($test instanceof self) === false) {
                throw new InvalidArgumentException('Array must contain char elements!');
            }
        }

        // Validate specified index
        if ($index < 0) {
            throw new InvalidArgumentException('Index cannot be negative!');
        }

        // Validate length
        if ($limit !== null) {
            if ($limit < 0 || $index >= $limit) {
                throw new InvalidArgumentException('Limit cannot be negative and index must be lower than specified limit!');
            }

            $length = \is_array($chars) ? \count($chars) : $chars->length();

            if ($limit > $length) {
                throw new InvalidArgumentException('Limit cannot be greater than char array/sequence!');
            }
        }

        $char = \is_array($chars) ? $chars[$index] : $chars->charAt($index);
        return unpack('N', mb_convert_encoding($char, 'UCS-4BE', 'UTF-8'))[1];
    }

    /**
     * Returns the unicode code point before specified index
     *
     * @param CharSequence|self[] $chars
     * @param int                 $index
     * @param int                 $start
     * @return int
     * @throws InvalidArgumentException
     */
    public static function codePointBefore($chars, int $index, int $start = null) : int
    {
        if ($start !== null && ($start < 0 || $index <= $start)) {
            throw new InvalidArgumentException('Start cannot be negative and index must be greater than start!');
        }

        return self::codePointAt($chars, $index - 1);
    }

    /**
     * Compares specified characters numerically
     *
     * @param Character|string $charA
     * @param Character|string $charB
     * @return int
     * @throws InvalidArgumentException
     * @throws TypeError
     */
    public static function compare($charA, $charB) : int
    {
        self::handleIncomingChar($charA, $charB);
        return (string) $charA <=> (string) $charB;
    }

    /**
     * Compares given character with this character
     *
     * @param Character|string $char
     * @return int
     * @throws InvalidArgumentException
     * @throws TypeError
     */
    public function compareTo($char) : int
    {
        self::handleIncomingChar($char);
        return strcmp($this->data, (string) $char) <=> 0;
    }

    /**
     * Validates given character and throws an exception if required
     *
     * @param mixed[] $chars
     * @return void
     * @throws InvalidArgumentException
     * @throws TypeError
     */
    private static function handleIncomingChar(...$chars) : void
    {
        foreach ($chars as $char) {
            Assert::typeOf(['string', __CLASS__], $char);

            if (mb_strlen((string) $char) !== 1) {
                throw new InvalidArgumentException('Only one character can be represented!');
            }
        }
    }

    /**
     * Checks if specified character is lower case
     *
     * @param Character|string $char
     * @return boolean
     * @throws InvalidArgumentException
     * @throws TypeError
     */
    public static function isLowerCase($char) : bool
    {
        self::handleIncomingChar($char);
        return self::compare($char, self::toLowerCase($char)) === 0;
    }

    /**
     * Checks if specified character is upper case
     *
     * @param Character|string $char
     * @return boolean
     * @throws InvalidArgumentException
     * @throws TypeError
     */
    public static function isUpperCase($char) : bool
    {
        self::handleIncomingChar($char);
        return self::compare($char, self::toUpperCase($char)) === 0;
    }

    /**
     * Converts specified character to lower case
     *
     * @param Character|string $char
     * @return Character
     * @throws InvalidArgumentException
     * @throws TypeError
     */
    public static function toLowerCase($char) : Character
    {
        self::handleIncomingChar($char);
        return new self(mb_strtolower((string) $char));
    }

    /**
     * Converts specified character to upper case
     *
     * @param Character|string $char
     * @return Character
     * @throws InvalidArgumentException
     * @throws TypeError
     */
    public static function toUpperCase($char) : Character
    {
        self::handleIncomingChar($char);
        return new self(mb_strtoupper((string) $char));
    }

    /**
     * Returns specified value as character
     *
     * @param Character|string $char
     * @return self
     * @throws InvalidArgumentException
     */
    public static function valueOf($char) : self
    {
        if (\is_string($char) || $char instanceof self) {
            return new self((string) $char);
        }

        throw new InvalidArgumentException('Unsupported character type!');
    }
}
