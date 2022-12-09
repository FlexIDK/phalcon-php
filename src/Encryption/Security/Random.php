<?php

namespace One23\PhalconPhp\Encryption\Security;

/**
 * Secure random number generator class.
 *
 * Provides secure random number generator which is suitable for generating
 * session key in HTTP cookies, etc.
 *
 * `Random` could be mainly useful for:
 * - Key generation (e.g. generation of complicated keys)
 * - Generating random passwords for new user accounts
 * - Encryption systems
 *
 * This class partially borrows SecureRandom library from Ruby
 *
 * @link https://ruby-doc.org/stdlib-2.2.2/libdoc/securerandom/rdoc/SecureRandom.html
 */
class Random
{
    const DEFAULT_LENGTH = 16;

    protected function len(int $len = null) {
        if (is_null($len) || $len <= 0) {
            return self::DEFAULT_LENGTH;
        }

        return $len;
    }

    /**
     * Generates a random base58 string
     *
     * @link   https://en.wikipedia.org/wiki/Base58
     * @throws Exception
     */
    public function base58(int $len = null): string
    {
        return $this->base(
            "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz",
            58,
            $len
        );
    }

    /**
     * Generates a random base62 string
     *
     * @throws Exception
     */
    public function base62(int $len = null): string
    {
        return $this->base(
            "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz",
            62,
            $len
        );
    }

    /**
     * Generates a random base64 string
     *
     * @throws Exception
     */
    public function base64(int $len = null): string
    {
        return base64_encode(
            $this->bytes($len)
        );
    }

    /**
     * Generates a random URL-safe base64 string
     *
     * @link https://www.ietf.org/rfc/rfc3548.txt
     * @throws Exception
     */
    public function base64Safe(int $len = null, bool $padding = false): string
    {
        $s = strtr(
            $this->base64($len),
            "+/",
            "-_"
        );

        $s = preg_replace(
            "#[^a-z0-9_=-]+#i",
            "",
            $s
        );

        if (!$padding) {
            return rtrim($s, "=");
        }

        return $s;
    }

    /**
     * Generates a random binary string
     *
     * The `Random::bytes` method returns a string and accepts as input an int
     * representing the length in bytes to be returned.
     *
     * @throws Exception
     */
    public function bytes(int $len = null): string
    {
        $len = $this->len($len);

        return random_bytes($len);
    }

    /**
     * Generates a random hex string
     *
     * @throws Exception
     */
    public function hex(int $len = null): string
    {
        $len = $this->len($len);

        $a = unpack(
            "H*",
            $this->bytes($len)
        );

        $str = array_shift($a);

        return mb_substr($str, 0, $len);
//        return $str; // todo
    }

    /**
     * Generates a random number between 0 and $len
     *
     * Returns an integer: 0 <= result <= $len.
     *
     * @throws Exception
     */
    public function number(int $len): int
    {
        if ($len <= 0) {
            throw new Exception("Input number must be a positive integer");
        }

        return random_int(0, $len);
    }

    /**
     * Generates a v4 random UUID (Universally Unique IDentifier)
     *
     * The version 4 UUID is purely random (except the version). It doesn't
     * contain meaningful information such as MAC address, time, etc. See RFC
     * 4122 for details of UUID.
     *
     * This algorithm sets the version number (4 bits) as well as two reserved
     * bits. All other bits (the remaining 122 bits) are set using a random or
     * pseudorandom data source. Version 4 UUIDs have the form
     * xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx where x is any hexadecimal digit and
     * y is one of 8, 9, A, or B (e.g., f47ac10b-58cc-4372-a567-0e02b2c3d479).
     *
     * @link https://www.ietf.org/rfc/rfc4122.txt
     * @throws Exception
     */
    public function uuid(): string
    {
        $ary = array_values(
            unpack(
                "N1a/n1b/n1c/n1d/n1e/N1f",
                $this->bytes(16)
            )
        );

        $ary[2] = ($ary[2] & 0x0fff) | 0x4000;
        $ary[3] = ($ary[3] & 0x3fff) | 0x8000;

        array_unshift(
            $ary,
            "%08x-%04x-%04x-%04x-%04x%08x"
        );

        return call_user_func_array("sprintf", $ary);
    }


    /**
     * Generates a random string based on the number ($base) of characters
     * ($alphabet).
     *
     * @throws Exception
     */
    protected function base(string $alphabet, int $base, int $len = null): string
    {
        $byteString = "";

        $bytes = unpack(
            "C*",
            $this->bytes($len)
        );

        // fix phalcon base
        $cnt = mb_strlen($alphabet);
        foreach ($bytes as $idx) {
            $idx = $idx % $cnt;

            if ($idx >= $base) {
                $idx = $this->number($base - 1);
            }

            $byteString .= $alphabet[$idx];
        }

        return $byteString;
    }
}
