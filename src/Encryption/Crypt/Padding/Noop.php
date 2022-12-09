<?php

namespace One23\PhalconPhp\Encryption\Crypt\Padding;

class Noop implements PadInterface
{
    public function pad(int $paddingSize): string
    {
        return "";
    }

    public function unpad(string $input, int $blockSize): int
    {
        return 0;
    }
}
