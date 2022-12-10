<?php

namespace One23\PhalconPhp;

use \ReflectionMethod;
use \ReflectionClass;

function create_instance(string $class) {
    if (!class_exists($class)) {
        throw new Exception("Class '{$class}' does not exist");
    }

    return new $class;
}

function create_instance_params(string $class, array $parameters = []) {
    if (!class_exists($class)) {
        throw new Exception("Class '{$class}' does not exist");
    }

    $re_args = [];
    $refMethod = new ReflectionMethod($class, '__construct');
    foreach ($refMethod->getParameters() as $key => $param) {
        if ($param->isPassedByReference()) {
            $re_args[$key] = &$parameters[$key];
        }
        else {
            $re_args[$key] = $parameters[$key];
        }
    }

    // Create Class Instance
    $refClass = new ReflectionClass($class);
    return $refClass->newInstanceArgs($re_args);
}


function phannot_parse_annotations($comment, $file_path, $line): ?array {
    $result = [];

	$error_msg = NULL;

    if (is_string($comment)) {
        $comment_str = $comment;
        $comment_len = mb_strlen($comment);
    }
    else {
        $comment_str = "";
        $comment_len = 0;
    }

    $file_path_str = is_string($file_path) ? $file_path : "eval";
    $line_num = is_int($line) ? $line : 0;

	if (phannot_internal_parse_annotations($result, $comment_str, $comment_len, $file_path_str, $line_num, $error_msg) === false) {
        if (!is_null($error_msg)) {
            zephir_throw_exception_string('phalcon_annotations_exception_ce', $error_msg, strlen($error_msg));
        }
        else {
            zephir_throw_exception_string('phalcon_annotations_exception_ce', "There was an error parsing annotation");
        }
    }

	return $result;
}

function zephir_throw_exception_string(string $object, string $error_msg = "", int $code = 0) {
    throw new \Exception(
        trim("[{$object}] {$error_msg}"),
        $code
    );
}

function phannot_internal_parse_annotations(array &$result, $comment, $comment_len, $file_path, $line, ?string &$error_msg): bool {
    $status = true;
    $parser_status = NULL;
	$error_msg = NULL;
    $processed_comment_len = 0;

	/**
     * Check if the comment has content
     */
	if (empty($comment)) {
        $error_msg = "Empty annotation";
		return false;
	}

	if ($comment_len < 2) {
        $result = [];
		return true;
	}

    // TODO

	return $status;
}
