<?php

namespace One23\PhalconPhp\Encryption\Crypt\Padding;

class Ansi implements PadInterface
{

    public function pad(int $paddingSize): string
    {
        return str_repeat(chr(0), $paddingSize - 1) . chr($paddingSize);
    }

    public function unpad(string $input, int $blockSize): int
    {
        $paddingSize = 0;
        $length      = strlen($input);
        $last        = substr($input, $length - 1, 1);
        $ord         = ord($last);

        if ($ord <= $blockSize) {
            $paddingSize = $ord;
            $repeat      = "";

            if ($paddingSize > 1) {
                $repeat = str_repeat(chr(0), $paddingSize - 1);
            }

            $padding = $repeat . $last;

            if (substr($input, $length - $paddingSize) !== $padding) {
                $paddingSize = 0;
            }
        }

        return $paddingSize;
    }

}
