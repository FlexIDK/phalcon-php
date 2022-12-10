<?php

namespace One23\PhalconPhp\Annotations;

use One23\PhalconPhp\Annotations\Adapter\AdapterInterface;
use One23\PhalconPhp\Factory\AbstractFactory;
use One23\PhalconPhp\Config\Config;
use function One23\PhalconPhp\create_instance_params;

class AnnotationsFactory extends AbstractFactory
{
    /**
     * AdapterFactory constructor.
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * @param array|Config $config = [
     *     'adapter' => 'apcu',
     *     'options' => [
     *         'prefix' => 'phalcon',
     *         'lifetime' => 3600,
     *         'annotationsDir' => 'phalconDir'
     *     ]
     * ]
     *
     * Factory to create an instance from a Config object
     */
    public function load(array|Config $config): mixed
    {
        $config = $this->checkConfig($config);
        $config = $this->checkConfigElement($config, "adapter");
        $name   = $config["adapter"];

        unset($config["adapter"]);

        $options = $config['options'] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param array $options = [
     *     'prefix' => 'phalcon',
     *     'lifetime' => 3600,
     *     'annotationsDir' => 'phalconDir'
     * ]
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        $definition = $this->getService($name);

        return create_instance_params(
            $definition,
            compact('options')
        );
    }

    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * Returns the available adapters
     *
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            "apcu"   => Adapter\Apcu::class,
            "memory" => Adapter\Memory::class,
            // todo
//            "stream" => "Phalcon\\Annotations\\Adapter\\Stream"
        ];
    }
}
