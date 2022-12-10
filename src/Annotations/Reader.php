<?php

namespace One23\PhalconPhp\Annotations;

use ReflectionClass;
use function One23\PhalconPhp\phannot_parse_annotations;

/**
 * Parses docblocks returning an array with the found annotations
 */
class Reader implements ReaderInterface {
    /**
     * Reads annotations from the class docblocks, its methods and/or properties
     */
    public function parse(string $className): array
    {
        $annotations = [];

        $reflection = new ReflectionClass($className);

        $comment = $reflection->getDocComment();
        if ($comment !== false) {
            /**
             * Read annotations from class
             */
            $classAnnotations = phannot_parse_annotations(
                $comment,
                $reflection->getFileName(),
                $reflection->getStartLine()
            );

            /**
             * Append the class annotations to the annotations var
             */
            if(is_array($classAnnotations)) {
                $annotations["class"] = $classAnnotations;
            }
        }

        /**
         * Get class constants
         */
        $constants = $reflection->getConstants();

        if (count($constants)) {
            /**
             * Line declaration for constants isn't available
             */
            $line = 1;
            $arrayKeys = array_keys($constants);
            $anotationsConstants = [];

            foreach ($arrayKeys as $constant) {
                /**
                 * Read comment from constant docblock
                 */
                $constantReflection = $reflection->getReflectionConstant($constant);
                $comment = $constantReflection->getDocComment();
                if ($comment !== false) {
                    /**
                     * Parse constant docblock comment
                     */
                    $constantAnnotations = phannot_parse_annotations(
                        $comment,
                        $reflection->getFileName(),
                        $line
                    );

                    if (is_array($constantAnnotations)) {
                        $anotationsConstants[$constant] = $constantAnnotations;
                    }
                }
            }

            if (count($anotationsConstants)) {
                $annotations["constants"] = $anotationsConstants;
            }
        }

        /**
         * Get the class properties
         */
        $properties = $reflection->getProperties();

        if (count($properties)) {
            /**
             * Line declaration for properties isn't available
             */
            $line = 1;
            $annotationsProperties = [];

            foreach ($properties as $property) {
                /**
                 * Read comment from property
                 */
                $comment = $property->getDocComment();
                if ($comment !== false) {
                    /**
                     * Parse property docblock comment
                     */
                    $propertyAnnotations = phannot_parse_annotations(
                        $comment,
                        $reflection->getFileName(),
                        $line
                    );

                    if (is_array($propertyAnnotations)) {
                        $annotationsProperties[$property->name] = $propertyAnnotations;
                    }
                }
            }

            if (count($annotationsProperties)) {
                $annotations["properties"] = $annotationsProperties;
            }
        }

        /**
         * Get the class methods
         */
        $methods = $reflection->getMethods();

        if (!empty($methods)) {
            $annotationsMethods = [];

            foreach($methods as $method) {
                /**
                 * Read comment from method
                 */
                $comment = $method->getDocComment();
                if ($comment !== false) {
                    /**
                     * Parse method docblock comment
                     */
                    $methodAnnotations = phannot_parse_annotations(
                        $comment,
                        $method->getFileName(),
                        $method->getStartLine()
                    );

                    if (is_array($methodAnnotations)) {
                        $annotationsMethods[$method->name] = $methodAnnotations;
                    }
                }
            }

            if (count($annotationsMethods)) {
                $annotations["methods"] = $annotationsMethods;
            }
        }

        return $annotations;
    }

    /**
     * Parses a raw doc block returning the annotations found
     */
    public static function parseDocBlock(string $docBlock, $file = null, $line = null): array
    {
        if (!is_string($file)) {
            $file = "eval code";
        }

        return phannot_parse_annotations($docBlock, $file, $line);
    }
}
