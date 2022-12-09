<?php

namespace One23\PhalconPhp\Encryption\Crypt\Padding;

class IsoIek implements PadInterface
{

    public function pad(int $paddingSize): string
    {
        return mb_chr(0x80) . str_repeat(mb_chr(0), $paddingSize - 1);
    }

    public function unpad(string $input, int $blockSize): int
    {
        $paddingSize = 0;
        $length      = mb_strlen($input);
        $inputArray  = mb_str_split($input);
        $counter     = $length - 1;

        while (
            $counter > 0 &&
            $inputArray[$counter] === mb_chr(0) &&
            $paddingSize < $blockSize
        ) {
            $paddingSize++;
            $counter--;
        }

        if ($inputArray[$counter] == mb_chr(0x80)) {
            $paddingSize++;
        }
        else {
            $paddingSize = 0;
        }

        return $paddingSize;
    }

}
