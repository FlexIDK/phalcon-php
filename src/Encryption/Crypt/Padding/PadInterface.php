<?php

namespace One23\PhalconPhp\Encryption\Crypt\Padding;

interface PadInterface
{
    public function pad(int $paddingSize): string;

    public function unpad(string $input, int $blockSize): int;
}
