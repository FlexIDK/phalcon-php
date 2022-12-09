<?php

namespace One23\PhalconPhp\Encryption\Crypt\Padding;

class Iso10126 implements PadInterface
{

    public function pad(int $paddingSize): string
    {
        $padding = "";

        foreach(range(0, $paddingSize - 2) as $counter) {
            $padding .= chr(rand());
        }

        $padding .= chr($paddingSize);

        return $padding;
    }

    public function unpad(string $input, int $blockSize): int
    {
        $length = strlen($input);
        $last   = substr($input, $length - 1, 1);

        return ord($last);
    }

}
