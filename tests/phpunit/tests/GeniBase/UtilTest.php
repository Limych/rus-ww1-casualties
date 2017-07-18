<?php
namespace GeniBase\Tests;

use GeniBase\Util;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Util.
 * Generated by PHPUnit on 2017-07-01 at 18:49:48.
 */
class UtilTest extends TestCase
{
    /**
     * @covers GeniBase\Util::arraySliceKeys
     */
    public function testArraySliceKeys()
    {
        $array = [
            1       => 'data',
            2       => 2,
            'key'   => 1,
            'key2'  => 'data',
        ];

        $this->assertEquals(
            [
                'key'   => 1,
            ],
            Util::arraySliceKeys($array, 'key')
        );
        $this->assertEquals(
            [
                1       => 'data',
                2       => 2,
                'key2' => 'data'
            ],
            Util::arraySliceKeys($array, 1, 2, 'key2')
        );
    }

    /**
     * @covers GeniBase\Util::parseArgs
     */
    public function testParseArgsObject()
    {
        $x = new MockClass();
        $x->_baba = 5;
        $x->yZ = "baba";
        $x->a = array(
            5,
            111,
            'x'
        );
        $this->assertEquals(array(
            '_baba' => 5,
            'yZ' => 'baba',
            'a' => array(
                5,
                111,
                'x'
            )
        ), Util::parseArgs($x));
        $y = new MockClass();
        $this->assertEquals(array(), Util::parseArgs($y));
    }

    /**
     * @covers GeniBase\Util::parseArgs
     */
    public function testParseArgsArray()
    {
        // arrays
        $a = array();
        $this->assertEquals(array(), Util::parseArgs($a));
        $b = array(
            '_baba' => 5,
            'yZ' => 'baba',
            'a' => array(
                5,
                111,
                'x'
            )
        );
        $this->assertEquals(array(
            '_baba' => 5,
            'yZ' => 'baba',
            'a' => array(
                5,
                111,
                'x'
            )
        ), Util::parseArgs($b));
    }

    /**
     * @covers GeniBase\Util::parseArgs
     */
    public function testParseArgsDefaults()
    {
        $x = new MockClass();
        $x->_baba = 5;
        $x->yZ = "baba";
        $x->a = array(
            5,
            111,
            'x'
        );
        $d = array(
            'pu' => 'bu'
        );
        $this->assertEquals(array(
            'pu' => 'bu',
            '_baba' => 5,
            'yZ' => 'baba',
            'a' => array(
                5,
                111,
                'x'
            )
        ), Util::parseArgs($x, $d));
        $e = array(
            '_baba' => 6
        );
        $this->assertEquals(array(
            '_baba' => 5,
            'yZ' => 'baba',
            'a' => array(
                5,
                111,
                'x'
            )
        ), Util::parseArgs($x, $e));
    }

    /**
     * @covers GeniBase\Util::parseArgs
     */
    public function testParseArgsOther()
    {
        $b = true;
        parse_str($b, $s);
        $this->assertEquals($s, Util::parseArgs($b));
        $q = 'x=5&_baba=dudu&';
        parse_str($q, $ss);
        $this->assertEquals($ss, Util::parseArgs($q));
    }

    /**
     * @covers GeniBase\Util::parseArgs
     */
    public function testParseArgsBooleanStrings()
    {
        $args = Util::parseArgs('foo=false&bar=true');
        $this->assertInternalType('string', $args['foo']);
        $this->assertInternalType('string', $args['bar']);
    }

    /**
     * @covers GeniBase\Util::isAssoc
     */
    public function testIsAssoc()
    {
        $q = null;
        $this->assertFalse(Util::isAssoc($q));
        $q = 'array';
        $this->assertFalse(Util::isAssoc($q));

        $q = array();
        $this->assertFalse(Util::isAssoc($q));
        $q = array(
            'a',
            'b',
            'c'
        );
        $this->assertFalse(Util::isAssoc($q));
        $q = array(
            "0" => 'a',
            "1" => 'b',
            "2" => 'c'
        );
        $this->assertFalse(Util::isAssoc($q));

        $q = array(
            "1" => 'a',
            "0" => 'b',
            "2" => 'c'
        );
        $this->assertTrue(Util::isAssoc($q));
        $q = array("a" => 'a', "b" => 'b', "c" => 'c');
        $this->assertTrue(Util::isAssoc($q));
    }
}
