<?php

use function Patchwork\{redefine, relay, getMethod};

// Function to create a tracer for a specific pattern
function createTracer($pattern, $logfileName = null) {
    // Create a unique log file for this pattern if not provided
    if ($logfileName === null) {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pattern);
        $logfileName = "trace_{$safeName}.csv";
    }

    $logfile = fopen($logfileName, 'w');

    // Create the tracer function
    $tracerFunction = function(...$args) use ($logfile) {
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

                // Use reflection to get object properties if no common methods found
                if (empty($representation)) {
                    $reflector = new ReflectionClass($arg);
                    $properties = $reflector->getProperties();

                    foreach ($properties as $property) {
                        $property->setAccessible(true);
                        if ($property->isInitialized($arg)) {
                            $value = $property->getValue($arg);
                            // Handle simple scalar values, convert complex values to type info
                            if (is_scalar($value) || is_null($value)) {
                                $representation[$property->getName()] = $value;
                            } else {
                                $representation[$property->getName()] = is_object($value) ?
                                "object:" . get_class($value) : gettype($value);
                            }
                        }
                    }
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

        fputcsv($logfile, [$formatted_time, getMethod(), json_encode($formatted_args, JSON_PRETTY_PRINT)]);
        fputcsv($logfile, ["RETURNS", gettype($result), json_encode($formatted_result)]);

        return $result;
    };

    // Apply the tracer to the pattern
    redefine($pattern, $tracerFunction);

    return $logfile;
}

// List of patterns to trace
$patternsToTrace = [
    //'Phel\Run\RunFacade*',
    // Uncomment and add more patterns as needed:
    //'Phel\Compiler\Domain\Analyzer\Environment*',
    'Phel\Compiler\Domain\Analyzer\Environment\GlobalEnvironment*',
    //'Phel\Run\Infrastructure\Command*',
    // 'Phel\Compiler\Domain\Analyzer*',
    'Phel\Lang\Registry*',
];

// Apply tracers to all patterns
$logfiles = [];
foreach ($patternsToTrace as $pattern) {
    $logfiles[$pattern] = createTracer($pattern);
}

// You can also create tracers with custom log file names:
// createTracer('Phel\Compiler\Domain\Analyzer\Environment*', 'trace_environment.csv');
// createTracer('Phel\Run\Infrastructure\Command\ReplCommand::loadAllPhelNamespaces', 'trace_repl_load.csv');
// createTracer('Phel\Lang\Registry::addDefinition', 'trace_registry_add.csv');
