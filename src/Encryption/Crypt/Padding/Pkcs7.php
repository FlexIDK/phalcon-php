<?php

namespace One23\PhalconPhp\Encryption\Crypt\Padding;

class Pkcs7 implements PadInterface
{

    public function pad(int $paddingSize): string
    {
        return str_repeat(mb_chr($paddingSize), $paddingSize);
    }

    public function unpad(string $input, int $blockSize): int
    {
        $paddingSize = 0;
        $length = mb_strlen($input);
        $last   = mb_substr($input, $length - 1, 1);
        $ord    = mb_ord($last);

        if ($ord <= $blockSize) {
            $paddingSize = $ord;
            $padding     = str_repeat(mb_chr($paddingSize), $paddingSize);

            if (mb_substr($input, $length - $paddingSize) !== $padding) {
                $paddingSize = 0;
            }
        }

        return $paddingSize;
    }

}
