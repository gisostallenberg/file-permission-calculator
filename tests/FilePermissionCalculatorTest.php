<?php

namespace GisoStallenberg\Test;

use GisoStallenberg\FilePermissionCalculator\FilePermissionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * FilePermissionCalculatorTest.
 *
 * @author  Giso Stallenberg <gisostallenberg@gmail.com>
 */
class FilePermissionCalculatorTest extends TestCase
{
    /**
     * Setup for the test.
     */
    protected function setUp(): void
    {
        chmod(__DIR__ . '/Resources/writable-regular', 0100644);
    }

    /**
     * Tests instantiation of FilePermissionCalculator.
     */
    public function testConstruct()
    {
        $this->assertSame(0777, (new FilePermissionCalculator(0777))->getMode());
    }

    /**
     * Tests if construction using a string fails.
     */
    public function testInvalidStringConstruct()
    {
        $this->expectException(InvalidArgumentException::class);
        new FilePermissionCalculator('0777');
    }

    /**
     * Test if creating from mode string fails with empty string.
     */
    public function testGetModeFromStringRepresentationFailsOnShortArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        FilePermissionCalculator::getModeFromStringRepresentation('');
    }

    /**
     * Tests if the getters return expected results.
     */
    #[DataProvider('provideGettersAndResults')]
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
     */
    #[DataProvider('provideCreators')]
    public function testCreators($creator, $argument, $expected, $getter = 'getMode')
    {
        $filepermissioncalculator = FilePermissionCalculator::$creator($argument);
        $this->assertEquals($expected, $filepermissioncalculator->$getter());
    }

    /**
     * Tests the string representation for the full range of type and permission bits (issue #1).
     */
    #[DataProvider('provideStringRepresentations')]
    public function testStringRepresentation($mode, $expected)
    {
        $this->assertSame($expected, FilePermissionCalculator::getStringRepresentation($mode));
        // Round trip: the string must map back to the same mode.
        $this->assertSame($mode, FilePermissionCalculator::getModeFromStringRepresentation($expected));
    }

    /**
     * fromFile() must report a symbolic link as a symlink, not its target (issue #2).
     */
    public function testFromFileReportsSymlink()
    {
        $link = sys_get_temp_dir() . '/fpc-symlink-' . uniqid();
        symlink(__DIR__ . '/Resources/writable-regular', $link);

        try {
            $string = (string) FilePermissionCalculator::fromFile($link);
            $this->assertSame('l', $string[0]);
        } finally {
            unlink($link);
        }
    }

    /**
     * Provide data for getters test.
     *
     * @return array
     */
    public static function provideGettersAndResults()
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
    public static function provideCreators()
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

    /**
     * Mode <-> string representation pairs covering every file type and the setuid/setgid/sticky bits.
     *
     * @return array
     */
    public static function provideStringRepresentations()
    {
        return array(
            // File types.
            'socket'           => array(0140000, 's---------'),
            'symlink'          => array(0120000, 'l---------'),
            'regular'          => array(0100000, '----------'),
            'block special'    => array(0060000, 'b---------'),
            'directory'        => array(0040000, 'd---------'),
            'character special' => array(0020000, 'c---------'),
            'fifo'             => array(0010000, 'p---------'),
            'unknown'          => array(0000000, 'u---------'),
            // Regular permission bits.
            'regular rwxr-xr-x' => array(0100755, '-rwxr-xr-x'),
            'regular rw-r--r--' => array(0100644, '-rw-r--r--'),
            'dir rwxr-xr-x'    => array(0040755, 'drwxr-xr-x'),
            // Special bits set with execute bit.
            'setuid x'         => array(0104755, '-rwsr-xr-x'),
            'setgid x'         => array(0102755, '-rwxr-sr-x'),
            'sticky x'         => array(0101755, '-rwxr-xr-t'),
            // Special bits set without execute bit (capital letters).
            'setuid no x'      => array(0104644, '-rwSr--r--'),
            'setgid no x'      => array(0102644, '-rw-r-Sr--'),
            'sticky no x'      => array(0101644, '-rw-r--r-T'),
        );
    }
}
