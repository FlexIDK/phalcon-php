<?php

namespace One23\PhalconPhp\Support;

class Version
{
    /**
     * The constant referencing the major version. Returns 0
     */
    const VERSION_MAJOR = 0;

    /**
     * The constant referencing the major version. Returns 1
     * ```
     */
    const VERSION_MEDIUM = 1;

    /**
     * The constant referencing the major version. Returns 2
     */
    const VERSION_MINOR = 2;

    /**
     * The constant referencing the major version. Returns 3
     */
    const VERSION_SPECIAL = 3;

    /**
     * The constant referencing the major version. Returns 4
     */
    const VERSION_SPECIAL_NUMBER = 4;

    /**
     * Area where the version number is set. The format is as follows:
     * ABBCCDE
     *
     * A - Major version
     * B - Med version (two digits)
     * C - Min version (two digits)
     * D - Special release: 1 = alpha, 2 = beta, 3 = RC, 4 = stable
     * E - Special release version i.e. RC1, Beta2 etc.
     */
    protected function getVersion(): array
    {
        return [5, 1, 2, 4, 0];
    }

    /**
     * Translates a number to a special release.
     */
    protected final function getSpecial(int $special): string
    {
        return match ($special) {
            1 => "alpha",
            2 => "beta",
            3 => "RC",
            default => "",
        };
    }

    /**
     * Returns the active version (string)
     */
    public function get(): string
    {
        $version = $this->getVersion();

        $major         = $version[self::VERSION_MAJOR];
        $medium        = $version[self::VERSION_MEDIUM];
        $minor         = $version[self::VERSION_MINOR];
        $special       = $version[self::VERSION_SPECIAL];
        $specialNumber = $version[self::VERSION_SPECIAL_NUMBER];

        $result  = $major . "." . $medium . "." . $minor;
        $suffix  = $this->getSpecial($special);

        if ($suffix != "") {
            $result .= $suffix;

            if ($specialNumber != 0) {
                $result .= $specialNumber;
            }
        }

        return $result;
    }

    /**
     * Returns the numeric active version
     */
    public function getId(): string
    {
        $version = $this->getVersion();

        $major         = $version[self::VERSION_MAJOR];
        $medium        = $version[self::VERSION_MEDIUM];
        $minor         = $version[self::VERSION_MINOR];
        $special       = $version[self::VERSION_SPECIAL];
        $specialNumber = $version[self::VERSION_SPECIAL_NUMBER];

        return $major
            . sprintf("%02s", $medium)
            . sprintf("%02s", $minor)
            . $special
            . $specialNumber;
    }

    /**
     * Returns a specific part of the version. If the wrong parameter is passed
     * it will return the full version
     */
    public function getPart(int $part): string
    {
        $version = $this->getVersion();

        switch ($part) {
            case self::VERSION_MAJOR:
            case self::VERSION_MEDIUM:
            case self::VERSION_MINOR:
            case self::VERSION_SPECIAL_NUMBER:
                return (string)$version[$part];

            case self::VERSION_SPECIAL:
                return $this->getSpecial($version[self::VERSION_SPECIAL]);
        }

        return $this->get();
    }
}
