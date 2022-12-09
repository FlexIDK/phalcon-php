<?php

namespace One23\PhalconPhp\Annotations;

use One23\PhalconPhp\Constant;

class Annotation
{
    /**
     * Annotation Arguments
     */
    protected array $arguments = [];

    /**
     * Annotation ExprArguments
     */
    protected array $exprArguments = [];

    /**
     * Annotation Name
     */
    protected ?string $name;

    /**
     * Phalcon\Annotations\Annotation constructor
     */
    public function __construct(array $reflectionData)
    {
        $name = $reflectionData["name"] ?? null;
        if ($name) {
            $this->name = $reflectionData["name"];
        }

        /**
         * Process annotation arguments
         */
        $exprArguments = $reflectionData["arguments"] ?? null;
        if (is_array($exprArguments)) {
            $arguments = [];

            foreach ($exprArguments as $argument) {
                $resolvedArgument = $this->getExpression(
                    $argument["expr"]
                );

                $name = $argument["name"] ?? null;
                if ($name) {
                    $arguments[$name] = $resolvedArgument;
                }
                else {
                    $arguments[] = $resolvedArgument;
                }
            }

            $this->arguments = $arguments;
            $this->exprArguments = $exprArguments;
        }

    }

    /**
     * Returns an argument in a specific position
     */
    public function getArgument(mixed $position): mixed
    {
        return $this->arguments[$position] ?? null;
    }

    /**
     * Returns the expression arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns the expression arguments without resolving
     */
    public function getExprArguments(): array
    {
        return $this->exprArguments;
    }

    /**
     * Resolves an annotation expression
     */
    public function getExpression(array $expr): mixed
    {
        $type = $expr["type"] ?? null;

        switch ($type) {
            case Constant::PHANNOT_T_INTEGER:
            case Constant::PHANNOT_T_DOUBLE:
            case Constant::PHANNOT_T_STRING:
            case Constant::PHANNOT_T_IDENTIFIER:
                $value = $expr["value"] ?? null;
                break;

            case Constant::PHANNOT_T_NULL:
                $value = null;
                break;

            case Constant::PHANNOT_T_FALSE:
                $value = false;
                break;

            case Constant::PHANNOT_T_TRUE:
                $value = true;
                break;

            case Constant::PHANNOT_T_ARRAY:
                $arrayValue = [];

                $items = $expr["items"] ?? [];
                if (!is_array($items)) {
                    return [];
                }

                foreach ($items as $item) {
                    $resolvedItem = $this->getExpression(
                        $item["expr"] ?? null
                    );

                    $name = $item["name"] ?? null;
                    if ($name) {
                        $arrayValue[$name] = $resolvedItem;
                    }
                    else {
                        $arrayValue[] = $resolvedItem;
                    }
                }

                return $arrayValue;

            case Constant::PHANNOT_T_ANNOTATION:
                return new Annotation($expr);

            default:
                throw new Exception("The expression '{$type}' is unknown");
        }

        return $value;
    }

    /**
     * Returns the annotation's name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns a named argument
     */
    public function getNamedArgument(string $name): mixed
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Returns a named parameter
     */
    public function getNamedParameter(string $name): mixed
    {
        return $this->getNamedArgument($name);
    }

    /**
     * Returns an argument in a specific position
     */
    public function hasArgument($position): bool
    {
        return isset($this->arguments[$position]);
    }

    /**
     * Returns the number of arguments that the annotation has
     */
    public function numberArguments(): int
    {
        return count($this->arguments);
    }
}
