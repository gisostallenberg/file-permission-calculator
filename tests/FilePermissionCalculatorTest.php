<?php

namespace GisoStallenberg\Test;

use GisoStallenberg\FilePermissionCalculator\FilePermissionCalculator;
use PHPUnit_Framework_TestCase;

/**
 * FilePermissionCalculatorTest.
 *
 * @author  Giso Stallenberg <gisostallenberg@gmail.com>
 */
class FilePermissionCalculatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests instantiation of FilePermissionCalculator.
     */
    public function testConstruct()
    {
        $this->assertAttributeSame(0777, 'permissions', new FilePermissionCalculator(0777));
    }

    /**
     * Tests if construction using a string fails.
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidStringConstruct()
    {
        new FilePermissionCalculator('0777');
    }

    /**
     * Test if creating from mode string fails with empty string.
     *
     * @expectedException InvalidArgumentException
     */
    public function testGetModeFromStringRepresentationFailsOnShortArgument()
    {
        FilePermissionCalculator::getModeFromStringRepresentation('');
    }

    /**
     * Tests if the getters return expected results.
     *
     * @dataProvider provideGettersAndResults
     */
    public function testGetterResults($initial, $getter, $expected)
    {
        if ($initial instanceof FilePermissionCalculator) {
            $filepermissioncalculator = $initial;
        } else {
            $filepermissioncalculator = new FilePermissionCalculator($initial);
        }

        $this->assertEquals($expected, $filepermissioncalculator->$getter());
    }

    /**
     * Tests if the creators work.
     *
     * @dataProvider provideCreators
     */
    public function testCreators($creator, $argument, $expected, $getter = 'getMode')
    {
        $filepermissioncalculator = FilePermissionCalculator::$creator($argument);
        $this->assertEquals($expected, $filepermissioncalculator->$getter());
    }

    /**
     * Provide data for getters test.
     *
     * @return array
     */
    public function provideGettersAndResults()
    {
        return array(
            array(0777, 'getMode', 511),
            array(0777, 'getDecimal', 511),
            array(0777, 'getString', 777),
            array(0777, 'getOctalString', '777'),
            array(0100777, 'getOctalString', '100777'),
        );
    }

    /**
     * Returns an array with creators that make a FilePermissionCalculator and the expected results.
     *
     * @return array
     */
    public function provideCreators()
    {
        return array(
            array('fromOctal', 0777, 0777),
            array('fromOctal', 0000, 0),
            array('fromOctalString', '0777', 0777),
            array('fromOctalString', 777, 0777),
            array('fromDecimal', 33279, 0100777),
            array('fromDecimal', '33279', 0100777),
            array('fromDecimal', 33279.0, 0100777),
            array('fromStringRepresentation', '-rwxrwxrwx', 0100777),
            array('fromStringRepresentation', 'urwxrwxrwx', 0777),
            array('fromStringRepresentation', '----------', 0100000),
            array('fromStringRepresentation', 'u---------', 0),
            array('fromFile', 'tests/Resources/writable-regular', 0100644),
        );
    }
}
