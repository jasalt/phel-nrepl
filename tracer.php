<?php

use function Patchwork\{redefine, relay, getMethod};

$logfile = fopen('trace.csv', 'w');

// TODO only patch Phel\Compiler
// Phel\Compiler\Domain\Analyzer\Environment\GlobalEnvironment
// Phel\Run\Infrastructure\Command
// Phel\Compiler\Domain\Analyzer*
// Phel\Lang\Registry* (internal stuff)

// Phel\Compiler\Domain\Analyzer\Environment*
// Phel\Run\Infrastructure\Command\ReplCommand::loadAllPhelNamespaces
// Phel\Lang\Registry::addDefinition
redefine('Phel\Run\RunFacade*', function(...$args) use ($logfile) {
    $begin = microtime(true);
    $result = relay($args);
    $end = microtime(true);
	$formatted_time = number_format($end - $begin, 4, '.', '');

	$formatValue = function($arg) {
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
    };

	$formatted_args = array_map($formatValue, $args);
	$formatted_result = $formatValue($result);

	// TODO log return values
    fputcsv($logfile, [$formatted_time, getMethod(), json_encode($formatted_args, JSON_PRETTY_PRINT)]);
    fputcsv($logfile, ["RETURNS", gettype($result), json_encode($formatted_result)]);  //json_encode($formatted_result, JSON_PRETTY_PRINT)

    return $result;
});
