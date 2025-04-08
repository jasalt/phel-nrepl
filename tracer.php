<?php

use function Patchwork\{redefine, relay, getMethod};

$logfile = fopen('trace.csv', 'w');

// TODO only patch Phel\Compiler
redefine('Phel\Compiler\Domain\Analyzer\Environment\GlobalEnvironment*', function(...$args) use ($logfile) {
    $begin = microtime(true);
    $result = relay($args);
    $end = microtime(true);
	$formatted_time = number_format($end - $begin, 4, '.', '');

	$formatted_args = array_map(function($arg) {
        if (is_object($arg)) {
            $class = get_class($arg);
            $representation = [];

            // Try common methods for string representation
            if (method_exists($arg, 'getName')) {
                $representation['name'] = $arg->getName();
            }
            if (method_exists($arg, 'getValue')) {
                $representation['value'] = $arg->getValue();
            }
            if (method_exists($arg, '__toString')) {
                $representation['string'] = (string)$arg;
            }

            // Include the class name
            return [
                'class' => $class,
                'data' => !empty($representation) ? $representation : 'object'
            ];
        }
        return $arg;
    }, $args);

    fputcsv($logfile, [$formatted_time, getMethod(), json_encode($formatted_args, JSON_PRETTY_PRINT)]);
    return $result;
});
