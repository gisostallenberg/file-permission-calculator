<?php
/*
 * This file is part of the file permission library
 *
 * (c) Giso Stallenberg <gisostallenberg@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace GisoStallenberg\FilePermissionCalculator;

use InvalidArgumentException;

/**
 * Class that can convert permission info to various formats.
 */
class FilePermissionCalculator
{
    /**
     * The permissions.
     *
     * @var int
     */
    private $permissions = 0;

    /**
     * Creates a new file permission calculator.
     *
     * @param type $permissions
     */
    public function __construct($permissions)
    {
        $this->verifyValue($permissions);
        $this->permissions = (int) $permissions;
    }

    /**
     * Make sure the argument is octal, please note that some decimals are also considered octal.
     *
     * @param mixed $value
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    private function verifyValue($value)
    {
        if (is_string($value)) {
            throw new InvalidArgumentException('Given value is a string, please use fromOctalString to make sure the value gets set up correctly');
        }

        return true;
    }

    /**
     * The decimal value.
     *
     * @return int
     */
    public function getMode()
    {
        return $this->permissions;
    }

    /**
     * The decimal value.
     *
     * @return int
     */
    public function getDecimal()
    {
        return $this->permissions;
    }

    /**
     * The octal value as string.
     *
     * @return string
     */
    public function getOctalString()
    {
        return decoct($this->permissions);
    }

    /**
     * The string value.
     *
     * @return int
     */
    public function getString()
    {
        return sprintf('%o', $this->permissions);
    }

    /**
     * Create a new instance asuming the argument is octal.
     *
     * @param octal $octal
     *
     * @return FilePermissionCalculator
     */
    public static function fromOctal($octal)
    {
        return new static($octal);
    }

    /**
     * Create a new instance asuming the argument is string representation of an octal.
     *
     * @param string $octalString
     *
     * @return FilePermissionCalculator
     */
    public static function fromOctalString($octalString)
    {
        return new static(octdec($octalString));
    }

    /**
     * Create a new instance asuming the argument is decimal.
     *
     * @param decimal $octal
     *
     * @return FilePermissionCalculator
     */
    public static function fromDecimal($decimal)
    {
        return static::fromOctalString(decoct($decimal));
    }

    /**
     * Create a new instance asuming the argument is octal.
     *
     * @param octal $octal
     *
     * @return FilePermissionCalculator
     */
    public static function fromStringRepresentation($permissions)
    {
        return new static(static::getModeFromStringRepresentation($permissions));
    }

    /**
     * Create a new instance using the permission info from the given file.
     *
     * @param string $file
     *
     * @return FilePermissionCalculator
     */
    public static function fromFile($file)
    {
        return new static(fileperms($file));
    }

    /**
     * Create a string representation of the permissions.
     *
     * @return FilePermissionCalculator
     */
    public function __toString()
    {
        return static::getStringRepresentation($this->permissions);
    }

    /**
     * Gives a string representation of the permissions.
     *
     * See "Example #2 Display full permissions" on http://php.net/manual/en/function.fileperms.php
     *
     * @param int $mode
     *
     * @return FilePermissionCalculator
     */
    public static function getStringRepresentation($mode)
    {
        $permissions = '';

        if (($mode & 0xC000) == 0xC000) {
            $permissions = 's'; // socket
        } elseif (($mode & 0xA000) == 0xA000) {
            $permissions = 'l'; // symbolic link
        } elseif (($mode & 0x8000) == 0x8000) {
            $permissions = '-'; // regular
        } elseif (($mode & 0x6000) == 0x6000) {
            $permissions = 'b'; // block special
        } elseif (($mode & 0x4000) == 0x4000) {
            $permissions = 'd'; // directory
        } elseif (($mode & 0x2000) == 0x2000) {
            $permissions = 'c'; // character special
        } elseif (($mode & 0x1000) == 0x1000) {
            $permissions = 'p'; // FIFO pipe
        } else {
            $permissions = 'u'; // unknown
        }

        // owner
        $permissions .= (($mode & 0x0100) ? 'r' : '-');
        $permissions .= (($mode & 0x0080) ? 'w' : '-');
        $permissions .= (($mode & 0x0040) ?
                    (($mode & 0x0800) ? 's' : 'x') :
                    (($mode & 0x0800) ? 'S' : '-'));

        // group
        $permissions .= (($mode & 0x0020) ? 'r' : '-');
        $permissions .= (($mode & 0x0010) ? 'w' : '-');
        $permissions .= (($mode & 0x0008) ?
                    (($mode & 0x0400) ? 's' : 'x') :
                    (($mode & 0x0400) ? 'S' : '-'));

        // world
        $permissions .= (($mode & 0x0004) ? 'r' : '-');
        $permissions .= (($mode & 0x0002) ? 'w' : '-');
        $permissions .= (($mode & 0x0001) ?
                    (($mode & 0x0200) ? 't' : 'x') :
                    (($mode & 0x0200) ? 'T' : '-'));

        return $permissions;
    }

    /**
     * Converts the string representation to a mode.
     *
     * See comment of 'paul maybe at squirrel mail org' on http://php.net/manual/en/function.chmod.php
     *
     * @param string $permissions
     *
     * @return int
     */
    public static function getModeFromStringRepresentation($permissions)
    {
        if (strlen($permissions) !== 10) {
            throw new InvalidArgumentException('Please provide a 10 character long string');
        }
        $mode = 0;

        if ($permissions[0] == 's') {
            $mode = 0140000;
        } elseif ($permissions[0] == 'l') {
            $mode = 0120000;
        } elseif ($permissions[0] == '-') {
            $mode = 0100000;
        } elseif ($permissions[0] == 'b') {
            $mode = 060000;
        } elseif ($permissions[0] == 'd') {
            $mode = 040000;
        } elseif ($permissions[0] == 'c') {
            $mode = 020000;
        } elseif ($permissions[0] == 'p') {
            $mode = 010000;
        } elseif ($permissions[0] == 'u') {
            $mode = 0;
        }

        if ($permissions[1] == 'r') {
            $mode += 0400;
        }
        if ($permissions[2] == 'w') {
            $mode += 0200;
        }
        if ($permissions[3] == 'x') {
            $mode += 0100;
        } elseif ($permissions[3] == 's') {
            $mode += 04100;
        } elseif ($permissions[3] == 'S') {
            $mode += 04000;
        }

        if ($permissions[4] == 'r') {
            $mode += 040;
        }
        if ($permissions[5] == 'w') {
            $mode += 020;
        }
        if ($permissions[6] == 'x') {
            $mode += 010;
        } elseif ($permissions[6] == 's') {
            $mode += 02010;
        } elseif ($permissions[6] == 'S') {
            $mode += 02000;
        }

        if ($permissions[7] == 'r') {
            $mode += 04;
        }
        if ($permissions[8] == 'w') {
            $mode += 02;
        }
        if ($permissions[9] == 'x') {
            $mode += 01;
        } elseif ($permissions[9] == 't') {
            $mode += 01001;
        } elseif ($permissions[9] == 'T') {
            $mode += 01000;
        }

        return $mode;
    }
}
