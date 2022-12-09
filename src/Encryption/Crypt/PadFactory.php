<?php

namespace One23\PhalconPhp\Encryption\Crypt;

use One23\PhalconPhp\Encryption\Crypt;
use One23\PhalconPhp\Encryption\Crypt\Padding\PadInterface;
use One23\PhalconPhp\Factory\AbstractFactory;
use function One23\PhalconPhp\create_instance;

class PadFactory extends AbstractFactory
{
    /**
     * @var string
     */
    protected string $exception = __NAMESPACE__ . "\\Exception\\Exception";

    /**
     * AdapterFactory constructor.
     */
    public function __construct(?array $services = [])
    {
        $this->init($services);
    }

    /**
     * Create a new instance of the adapter
     */
    public function newInstance(?string $name): PadInterface
    {
        $definition = $this->getService($name);

        return create_instance($definition);
    }

    /**
     * Gets a Crypt pad constant and returns the unique service name for the
     * padding class
     */
    public function padNumberToService(int $number): string
    {
        $map = [
            Crypt::PADDING_ANSI_X_923       => "ansi",
            Crypt::PADDING_ISO_10126        => "iso10126",
            Crypt::PADDING_ISO_IEC_7816_4   => "isoiek",
            Crypt::PADDING_PKCS7            => "pjcs7",
            Crypt::PADDING_SPACE            => "space",
            Crypt::PADDING_ZERO             => "zero"
        ];

        return $map[$number] ?? 'noop';
    }

    /**
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            "ansi"      => Padding\Ansi::class,
            "iso10126"  => Padding\Iso10126::class,
            "isoiek"    => Padding\IsoIek::class,
            "noop"      => Padding\Noop::class,
            "pjcs7"     => Padding\Pkcs7::class,
            "space"     => Padding\Space::class,
            "zero"      => Padding\Zero::class
        ];
    }
}
