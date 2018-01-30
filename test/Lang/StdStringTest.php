<?php
declare(strict_types=1);

namespace BlackBonjourTest\Stdlib\Lang;

use BlackBonjour\Stdlib\Lang\Character;
use BlackBonjour\Stdlib\Lang\StdObject;
use BlackBonjour\Stdlib\Lang\StdString;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Test for StdString class
 *
 * @author      Erick Dyck <info@erickdyck.de>
 * @since       29.11.2017
 * @package     BlackBonjourTest\Stdlib\Lang
 * @copyright   Copyright (c) 2017 Erick Dyck
 * @covers      \BlackBonjour\Stdlib\Lang\StdString
 */
class StdStringTest extends TestCase
{
    private function getObject(string $string = 'FooBar') : StdString
    {
        return new StdString($string);
    }

    public function dataProvider__construct() : array
    {
        $charArray = function (string $string) : array {
            $chars  = [];
            $length = \strlen($string);

            for ($i = 0; $i < $length; $i++) {
                $chars[] = new Character($string[$i]);
            }

            return $chars;
        };

        return [
            'valid-string'  => ['FooBar', 'FooBar'],
            'valid-class'   => [$this->getObject(), 'FooBar'],
            'valid-array'   => [$charArray('FooBar'), 'FooBar'],
            'invalid-input' => [true, '', InvalidArgumentException::class],
            'invalid-class' => [new class { public function __toString() { return 'FooBar'; }}, '', InvalidArgumentException::class],
            'invalid-array' => [['F', 'o', 'o'], '', InvalidArgumentException::class],
        ];
    }

    public function dataProviderCharAt() : array
    {
        $string = $this->getObject();

        return [
            'latin'               => [$string, 3, new Character('B')],
            'cyrillic'            => [$this->getObject('Тест'), 2, new Character('с')],
            'negative-index'      => [$string, -1, null, OutOfBoundsException::class],
            'index-equals-length' => [$string, 6, null, OutOfBoundsException::class],
            'index-above-length'  => [$string, 7, null, OutOfBoundsException::class],
        ];
    }

    public function dataProviderContains() : array
    {
        $string = $this->getObject();

        return [
            'latin-string-class'    => [$string, $this->getObject('oo'), true],
            'latin-string'          => [$string, 'oo', true],
            'cyrillic-string-class' => [$string, $this->getObject('оо'), false],
            'cyrillic-string'       => [$string, 'оо', false],
            'invalid-pattern'       => [$string, 666, false],
        ];
    }

    public function dataProviderContentEquals() : array
    {
        $string = $this->getObject();

        return [
            'latin-string-class'     => [$string, $string, true],
            'latin-string'           => [$string, 'FooBar', true],
            'latin-string-lowercase' => [$string, 'foobar', false],
            'cyrillic-string-class'  => [$string, $this->getObject('Тест'), false],
            'cyrillic-string'        => [$string, 'Тест', false],
            'invalid-pattern'        => [$string, 666, false],
        ];
    }

    public function dataProviderCopyValueOf() : array
    {
        $stringA = $this->getObject();
        $stringB = $this->getObject('Тест');

        return [
            'latin-string-class'    => [$stringA, $stringA],
            'latin-string'          => ['FooBar', $stringA],
            'cyrillic-string-class' => [$stringB, $stringB],
            'cyrillic-string'       => ['Тест', $stringB],
            'string-list'           => [['F', 'o', 'o', 'B', 'a', 'r'], $stringA],
            'char-list'             => [[new Character('F'), new Character('o'), new Character('o'), new Character('B'), new Character('a'), new Character('r')], $stringA],
            'invalid-char-list'     => [666, $stringA, InvalidArgumentException::class],
        ];
    }

    public function dataProviderEndsWith() : array
    {
        $string = $this->getObject();

        return [
            'default'          => [$string, 'Bar', false, true],
            'case-insensitive' => [$string, 'bar', true, true],
            'case-sensitive'   => [$string, 'bar', false, false],
            'invalid-pattern'  => [$string, null, false, false],
            'pattern-too-long' => [$string, 'FooBarBar', false, false],
        ];
    }

    public function dataProviderEqualsIgnoreCase() : array
    {
        $stringA = $this->getObject();
        $stringB = $this->getObject('Тест');

        return [
            'latin-string-class'             => [$stringA, $this->getObject('foobar'), true],
            'latin-string'                   => [$stringA, 'foobar', true],
            'latin-string-class-no-match'    => [$stringA, $this->getObject('f00bar'), false],
            'latin-string-no-match'          => [$stringA, 'f00bar', false],
            'cyrillic-string-class'          => [$stringB, $this->getObject('тест'), true],
            'cyrillic-string'                => [$stringB, 'тест', true],
            'cyrillic-string-class-no-match' => [$stringB, $this->getObject('теcт'), false], // With latin 'c'
            'cyrillic-string-no-match'       => [$stringB, 'теcт', false], // With latin 'c'
            'invalid-pattern'                => [$stringB, 666, false],
        ];
    }

    public function dataProviderExplode() : array
    {
        $string = $this->getObject();

        return [
            'default'         => [$string, 'oo', ['F', 'Bar']],
            'invalid-pattern' => [$string, 666, ['F', 'Bar'], InvalidArgumentException::class],
            'empty-pattern'   => [$string, '', ['F', 'Bar'], RuntimeException::class], // Should trigger a warning
        ];
    }

    public function dataProviderOffsetExists() : array
    {
        $string = $this->getObject();

        return [
            'offset-does-exist'     => [$string, 3, true],
            'offset-does-not-exist' => [$string, 6, false],
            'offset-numeric-string' => [$string, '3', true],
            'offset-illegal'        => [$string, 'o', false],
            'offset-negative'       => [$string, -2, false],
        ];
    }

    public function dataProviderOffsetGet() : array
    {
        $string = $this->getObject();

        return [
            'offset-does-exist'     => [$string, 3, 'B'],
            'offset-does-not-exist' => [$string, 6, '', OutOfBoundsException::class],
            'offset-numeric-string' => [$string, '3', 'B'],
            'offset-illegal'        => [$string, 'o', 'F'], // Should trigger a warning and return first letter
            'offset-negative'       => [$string, -2, '', OutOfBoundsException::class],
        ];
    }

    public function dataProviderOffsetSet() : array
    {
        return [
            'offset-does-exist'     => [$this->getObject(), 3, 'C', 'FooCar'],
            'offset-does-not-exist' => [$this->getObject(), 7, 'Donald Duck', 'FooBar Donald Duck'],
            'offset-numeric-string' => [$this->getObject(), '3', 'C', 'FooCar'],
            'offset-illegal'        => [$this->getObject(), 'o', '', 'FooBar'], // Should trigger a warning
            'offset-negative'       => [$this->getObject(), -2, '', 'FooBar'], // Should trigger a warning
            'offset-set-last-index' => [$this->getObject(), 5, 'zz', 'FooBazz'],
        ];
    }

    public function dataProviderRegionMatches() : array
    {
        $stringA = $this->getObject();
        $stringB = $this->getObject('ФооБарТест');

        return [
            'latin-string-class'          => [$stringA, 3, $this->getObject('TestBar'), 4, 3, false, true],
            'latin-string'                => [$stringA, 3, 'TestBar', 4, 3, false, true],
            'latin-case-insensitive'      => [$stringA, 3, 'Testbar', 4, 3, true, true],
            'latin-case-sensitive'        => [$stringA, 3, 'Testbar', 4, 3, false, false],
            'latin-case-sensitive-obj'    => [$stringA, 3, $this->getObject('Testbar'), 4, 3, false, false],
            'cyrillic-string-class'       => [$stringB, 6, $this->getObject('ФооТест'), 3, 4, false, true],
            'cyrillic-string'             => [$stringB, 6, 'ФооТест', 3, 4, false, true],
            'cyrillic-case-insensitive'   => [$stringB, 6, 'фоотест', 3, 4, true, true],
            'cyrillic-case-sensitive'     => [$stringB, 6, 'фоотест', 3, 4, false, false],
            'cyrillic-case-sensitive-obj' => [$stringB, 6, $this->getObject('фоотест'), 3, 4, false, false],
            'negative-offset'             => [$stringA, -1, 'TestBar', 4, 3, false, false],
            'negative-pattern-offset'     => [$stringA, 3, 'TestBar', -1, 3, false, false],
            'pattern-offset-too-high'     => [$stringA, 3, 'TestBar', 5, 3, false, false],
            'string-offset-too-high'      => [$stringA, 4, 'TestBar', 4, 3, false, false],
        ];
    }

    public function dataProviderReplace() : array
    {
        return [
            'latin'    => ['FooBar', 'FooB', 'Saftb', $this->getObject('Saftbar')],
            'cyrillic' => ['Тест', 'ест', 'ормоз', $this->getObject('Тормоз')],
        ];
    }

    public function dataProviderReplaceAll() : array
    {
        return [
            'latin' => [
                'She sells sea shells by the sea shore.',
                '/sea/',
                'ocean',
                $this->getObject('She sells ocean shells by the ocean shore.'),
            ],
            'cyrillic' => [
                'Режиссеру Риддли Скотту пришлось вырезать все сцены с участием Кевина Спейси из нового трейлера фильма "Все деньги мира", который выйдет на экраны в конце декабря. Причина столь радикальной редактуры – вспыхнувший вокруг Спейси секс-скандал, сообщает EW.',
                '/Спейси/',
                'Джеймс',
                $this->getObject('Режиссеру Риддли Скотту пришлось вырезать все сцены с участием Кевина Джеймс из нового трейлера фильма "Все деньги мира", который выйдет на экраны в конце декабря. Причина столь радикальной редактуры – вспыхнувший вокруг Джеймс секс-скандал, сообщает EW.'),
            ],
        ];
    }

    public function dataProviderReplaceFirst() : array
    {
        return [
            'latin' => [
                'She sells sea shells by the sea shore.',
                '/sea/',
                'ocean',
                $this->getObject('She sells ocean shells by the sea shore.'),
            ],
            'cyrillic' => [
                'Режиссеру Риддли Скотту пришлось вырезать все сцены с участием Кевина Спейси из нового трейлера фильма "Все деньги мира", который выйдет на экраны в конце декабря. Причина столь радикальной редактуры – вспыхнувший вокруг Спейси секс-скандал, сообщает EW.',
                '/Спейси/',
                'Джеймс',
                $this->getObject('Режиссеру Риддли Скотту пришлось вырезать все сцены с участием Кевина Джеймс из нового трейлера фильма "Все деньги мира", который выйдет на экраны в конце декабря. Причина столь радикальной редактуры – вспыхнувший вокруг Спейси секс-скандал, сообщает EW.'),
            ],
        ];
    }

    public function dataProviderValueOf() : array
    {
        $obj = new StdObject;

        return [
            'boolean-true'   => [true, $this->getObject('true')],
            'boolean-false'  => [false, $this->getObject('false')],
            'array'          => [[], null, true],
            'float'          => [1.25, $this->getObject('1.25')],
            'integer'        => [125, $this->getObject('125')],
            'std-object'     => [$obj, $this->getObject(StdObject::class . '@' . spl_object_hash($obj))],
            'invalid-object' => [new \stdClass(), null, true],
            'object'         => [new class {public function __toString() { return 'FooBar'; }}, $this->getObject()],
        ];
    }

    /**
     * @param   StdString|Character[]|string    $string
     * @param   string                          $expectation
     * @param   string                          $exception
     * @dataProvider    dataProvider__construct
     */
    public function test__construct($string, string $expectation, string $exception = null)
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $stdString = new StdString($string);
        self::assertEquals($expectation, (string) $stdString);
    }

    public function test__toString()
    {
        $string = $this->getObject();
        self::assertEquals('FooBar', (string) $string);
    }

    /**
     * @param   StdString   $string
     * @param   int         $index
     * @param   Character   $expectation
     * @param   string      $exception
     * @dataProvider    dataProviderCharAt
     */
    public function testCharAt(StdString $string, int $index, Character $expectation = null, string $exception = null)
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        self::assertEquals($expectation, $string->charAt($index));
    }

    public function testCodePointAt()
    {
        self::assertEquals(97, $this->getObject()->codePointAt(4));
        self::assertEquals(1041, $this->getObject('ФооБар')->codePointAt(3));
    }

    public function testCodePointBefore()
    {
        self::assertEquals(97, $this->getObject()->codePointBefore(5));
        self::assertEquals(1041, $this->getObject('ФооБар')->codePointBefore(4));
    }

    public function testClone()
    {
        $string = $this->getObject();
        self::assertInstanceOf(StdString::class, $string->clone());
    }

    public function testCompareTo()
    {
        $string = $this->getObject();

        self::assertEquals(-1, $string->compareTo('Тест'));
        self::assertEquals(-1, $string->compareTo('foobar'));

        self::assertEquals(0, $string->compareTo('FooBar'));
        self::assertEquals(0, $string->compareTo($this->getObject()));

        self::assertEquals(1, $string->compareTo('Alpha'));
        self::assertEquals(1, $string->compareTo('Babushka'));
    }

    public function testCompareToIgnoreCase()
    {
        $string = $this->getObject();

        self::assertEquals(-1, $string->compareToIgnoreCase('тест'));
        self::assertEquals(-1, $string->compareToIgnoreCase($this->getObject('тест')));

        self::assertEquals(0, $string->compareToIgnoreCase('foobar'));
        self::assertEquals(0, $string->compareToIgnoreCase($this->getObject('foobar')));

        self::assertEquals(1, $string->compareToIgnoreCase('alpha'));
        self::assertEquals(1, $string->compareToIgnoreCase($this->getObject('alpha')));
    }

    public function testConcat()
    {
        $string = $this->getObject();

        self::assertEquals($this->getObject('FooBarTest'), $string->concat('Test'));
        self::assertEquals($this->getObject('FooBarTest'), $string->concat($this->getObject('Test')));
        self::assertEquals($this->getObject('FooBarТест'), $string->concat('Тест'));
    }

    /**
     * @param   StdString           $string
     * @param   StdString|string    $pattern
     * @param   boolean             $expectation
     * @dataProvider    dataProviderContains
     */
    public function testContains(StdString $string, $pattern, bool $expectation)
    {
        self::assertEquals($expectation, $string->contains($pattern));
    }

    /**
     * @param   StdString           $string
     * @param   StdString|string    $pattern
     * @param   boolean             $expectation
     * @dataProvider    dataProviderContentEquals
     */
    public function testContentEquals(StdString $string, $pattern, bool $expectation)
    {
        self::assertEquals($expectation, $string->contentEquals($pattern));
    }

    /**
     * @param   StdString|string|array $charList
     * @param   StdString              $expectation
     * @param   string                  $exception
     * @dataProvider    dataProviderCopyValueOf
     */
    public function testCopyValueOf($charList, StdString $expectation, string $exception = null)
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        self::assertEquals($expectation, StdString::copyValueOf($charList));
    }

    public function testCount()
    {
        self::assertCount(6, $this->getObject()); // Latin
        self::assertCount(6, $this->getObject('ФооБар')); // Cyrillic
    }

    /**
     * @param   StdString           $string
     * @param   StdString|string    $pattern
     * @param   boolean             $caseInsensitive
     * @param   boolean             $expectation
     * @dataProvider    dataProviderEndsWith
     */
    public function testEndsWith(StdString $string, $pattern, bool $caseInsensitive, bool $expectation)
    {
        self::assertEquals($expectation, $string->endsWith($pattern, $caseInsensitive));
    }

    public function testEquals()
    {
        $objA = $this->getObject();
        $objB = clone $objA;
        $objC = $this->getObject('Dis I Like');
        $objD = new StdObject;

        self::assertTrue($objA->equals($objB));
        self::assertFalse($objA->equals($objC));
        self::assertFalse($objA->equals($objD));
    }

    /**
     * @param   StdString           $string
     * @param   StdString|string    $pattern
     * @param   boolean             $expectation
     * @dataProvider    dataProviderEqualsIgnoreCase
     */
    public function testEqualsIgnoreCase(StdString $string, $pattern, bool $expectation)
    {
        self::assertEquals($expectation, $string->equalsIgnoreCase($pattern));
    }

    /**
     * @param   StdString           $string
     * @param   StdString|string    $pattern
     * @param   StdString[]         $expectation
     * @param   string              $exception
     * @dataProvider    dataProviderExplode
     */
    public function testExplode(StdString $string, $pattern, array $expectation, string $exception = null)
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        self::assertEquals($expectation, $string->explode($pattern));
    }

    public function testFormat()
    {
        $arg1     = $this->getObject('5');
        $arg2     = 'garage';
        $expected = $this->getObject('There are 5 cars in that garage.');
        $pattern  = $this->getObject('There are %s cars in that %s.');

        self::assertEquals($expected, StdString::format($pattern, $arg1, $arg2));
    }

    public function testGetBytes()
    {
        $string = $this->getObject();
        self::assertEquals([70, 111, 111, 66, 97, 114], $string->getBytes());
    }

    public function testGetChars()
    {
        $string  = $this->getObject();
        $result1 = [];
        $result2 = [];

        $string->getChars(0, 5, $result1, 0);
        $string->getChars(1, 2, $result2, 6);

        self::assertEquals(
            [
                new Character('F'),
                new Character('o'),
                new Character('o'),
                new Character('B'),
                new Character('a'),
                new Character('r'),
            ],
            $result1
        );

        self::assertEquals(
            [
                6 => new Character('o'),
                7 => new Character('o'),
            ],
            $result2
        );
    }

    public function testHashCode()
    {
        $string = $this->getObject();
        self::assertEquals(spl_object_hash($string), $string->hashCode());
    }

    public function testIndexOf()
    {
        $string = $this->getObject();

        self::assertEquals(1, $string->indexOf('o'));
        self::assertEquals(1, $string->indexOf($this->getObject('o')));
        self::assertEquals(-1, $string->indexOf('z'));
        self::assertEquals(2, $string->indexOf('o', 2));
    }

    public function testIsEmpty()
    {
        self::assertFalse($this->getObject()->isEmpty());
        self::assertTrue($this->getObject('')->isEmpty());
    }

    public function testLastIndexOf()
    {
        $string = $this->getObject();

        self::assertEquals(2, $string->lastIndexOf('o'));
        self::assertEquals(2, $string->lastIndexOf($this->getObject('o')));
        self::assertEquals(-1, $string->lastIndexOf('z'));
        self::assertEquals(1, $string->lastIndexOf('o', -5));
        self::assertEquals(2, $string->lastIndexOf('o', 1));
    }

    public function testLength()
    {
        self::assertEquals(6, $this->getObject()->length());
        self::assertEquals(4, $this->getObject('Тест')->length());
    }

    public function testMatches()
    {
        $stringA = $this->getObject();
        $stringB = $this->getObject('Тест');

        self::assertTrue($stringA->matches('/Bar$/'));
        self::assertTrue($stringA->matches($this->getObject('/Bar$/')));
        self::assertFalse($stringA->matches('/Foo$/'));
        self::assertFalse($stringA->matches($this->getObject('/Foo$/')));

        self::assertTrue($stringB->matches('/ст$/'));
        self::assertTrue($stringB->matches($this->getObject('/ст$/')));
        self::assertFalse($stringB->matches('/Те$/'));
        self::assertFalse($stringB->matches($this->getObject('/Те$/')));
    }

    /**
     * @param   StdString   $string
     * @param   mixed       $offset
     * @param   boolean     $expectation
     * @dataProvider    dataProviderOffsetExists
     */
    public function testOffsetExists(StdString $string, $offset, bool $expectation)
    {
        self::assertEquals($expectation, isset($string[$offset]));
    }

    /**
     * @param   StdString   $string
     * @param   mixed       $offset
     * @param   string      $expectation
     * @param   string      $exception
     * @dataProvider    dataProviderOffsetGet
     */
    public function testOffsetGet(StdString $string, $offset, string $expectation, string $exception = null)
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        self::assertEquals($expectation, $string[$offset]);
    }

    /**
     * @param   StdString   $string
     * @param   mixed       $offset
     * @param   mixed       $value
     * @param   string      $expectation
     * @dataProvider    dataProviderOffsetSet
     */
    public function testOffsetSet(StdString $string, $offset, $value, string $expectation)
    {
        $string[$offset] = $value;
        self::assertEquals($expectation, (string) $string);
    }

    /**
     * @param   StdString           $stringA
     * @param   int                 $offset
     * @param   StdString|string    $pattern
     * @param   int                 $strOffset
     * @param   int                 $length
     * @param   boolean             $ignoreCase
     * @param   boolean             $expectation
     * @param   string              $exception
     * @dataProvider    dataProviderRegionMatches
     */
    public function testRegionMatches(
        StdString $stringA,
        int $offset,
        $pattern,
        int $strOffset,
        int $length,
        bool $ignoreCase,
        bool $expectation,
        string $exception = null
    ) {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        self::assertEquals($expectation, $stringA->regionMatches($offset, $pattern, $strOffset, $length, $ignoreCase));
    }

    /**
     * @param   string      $string
     * @param   string      $old
     * @param   string      $new
     * @param   StdString   $expected
     * @dataProvider    dataProviderReplace
     */
    public function testReplace(string $string, string $old, string $new, StdString $expected)
    {
        $base = $this->getObject($string);

        self::assertEquals($expected, $base->replace($old, $new));
        self::assertEquals($expected, $base->replace($this->getObject($old), $this->getObject($new)));
    }

    /**
     * @param   string      $string
     * @param   string      $pattern
     * @param   string      $replacement
     * @param   StdString   $expected
     * @dataProvider    dataProviderReplaceAll
     */
    public function testReplaceAll(string $string, string $pattern, string $replacement, StdString $expected)
    {
        $base = $this->getObject($string);

        self::assertEquals($expected, $base->replaceAll($pattern, $replacement));
        self::assertEquals($expected, $base->replaceAll($this->getObject($pattern), $this->getObject($replacement)));
    }

    /**
     * @param   string      $string
     * @param   string      $pattern
     * @param   string      $replacement
     * @param   StdString   $expected
     * @dataProvider    dataProviderReplaceFirst
     */
    public function testReplaceFirst(string $string, string $pattern, string $replacement, StdString $expected)
    {
        $base = $this->getObject($string);

        self::assertEquals($expected, $base->replaceFirst($pattern, $replacement));
        self::assertEquals($expected, $base->replaceFirst($this->getObject($pattern), $this->getObject($replacement)));
    }

    public function testSplit()
    {
        self::assertEquals(
            [
                $this->getObject('F'),
                $this->getObject('Bar'),
            ],
            $this->getObject()->split('/oo/')
        );
    }

    public function testStartsWith()
    {
        self::assertTrue($this->getObject()->startsWith('Foo'));
        self::assertTrue($this->getObject()->startsWith($this->getObject('Foo')));
        self::assertFalse($this->getObject()->startsWith('Bar'));
        self::assertFalse($this->getObject()->startsWith($this->getObject('Bar')));

        self::assertTrue($this->getObject('Тест')->startsWith('Те'));
        self::assertTrue($this->getObject('Тест')->startsWith($this->getObject('Те')));
        self::assertFalse($this->getObject('Тест')->startsWith('ст'));
        self::assertFalse($this->getObject('Тест')->startsWith($this->getObject('ст')));
    }

    public function testSubSequence()
    {
        self::assertEquals(
            [
                new Character('o'),
                new Character('o'),
                new Character('B'),
                new Character('a'),
            ],
            $this->getObject()->subSequence(1, 4)
        );

        self::assertEquals(
            [
                new Character('о'),
                new Character('о'),
                new Character('Б'),
                new Character('а'),
            ],
            $this->getObject('ФооБар')->subSequence(1, 4)
        );
    }

    public function testSubstr()
    {
        self::assertEquals($this->getObject('oBa'), $this->getObject()->substr(2, 3));
        self::assertEquals($this->getObject('oBar'), $this->getObject()->substr(2));
        self::assertEquals($this->getObject('ест'), $this->getObject('Тест')->substr(1, 3));
    }

    public function testSubstring()
    {
        self::assertEquals($this->getObject('oBa'), $this->getObject()->substring(2, 4));
        self::assertEquals($this->getObject('oBar'), $this->getObject()->substring(2));
        self::assertEquals($this->getObject('ест'), $this->getObject('Тест')->substring(1, 3));
    }

    public function testToCharArray()
    {
        // Latin
        $result = $this->getObject()->toCharArray();

        self::assertInstanceOf(Character::class, reset($result));
        self::assertEquals([
            new Character('F'),
            new Character('o'),
            new Character('o'),
            new Character('B'),
            new Character('a'),
            new Character('r'),
        ], $result);

        // Cyrillic
        $result = $this->getObject('ФооБар')->toCharArray();

        self::assertInstanceOf(Character::class, reset($result));
        self::assertEquals([
            new Character('Ф'),
            new Character('о'),
            new Character('о'),
            new Character('Б'),
            new Character('а'),
            new Character('р'),
        ], $result);
    }

    public function testToLowercase()
    {
        self::assertEquals($this->getObject('foobar'), $this->getObject()->toLowerCase());
        self::assertEquals($this->getObject('тест'), $this->getObject('Тест')->toLowerCase());
    }

    public function testToUppercase()
    {
        self::assertEquals($this->getObject('FOOBAR'), $this->getObject()->toUpperCase());
        self::assertEquals($this->getObject('ТЕСТ'), $this->getObject('Тест')->toUpperCase());
    }

    public function testTrim()
    {
        self::assertEquals($this->getObject(), $this->getObject(' FooBar ')->trim());
        self::assertEquals($this->getObject(), $this->getObject("FooBar\n")->trim());
        self::assertEquals($this->getObject('Тест'), $this->getObject("Тест\n")->trim());
    }

    /**
     * @param   mixed       $value
     * @param   StdString   $expected
     * @param   boolean     $throwsException
     * @dataProvider    dataProviderValueOf
     */
    public function testValueOf($value, $expected, bool $throwsException = false)
    {
        if ($throwsException) {
            $this->expectException(InvalidArgumentException::class);
        }

        self::assertEquals($expected, StdString::valueOf($value));
    }
}
