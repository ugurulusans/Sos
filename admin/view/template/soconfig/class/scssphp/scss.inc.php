<?php
if (version_compare(PHP_VERSION, '5.4') < 0) {
    throw new \Exception('scssphp requires PHP 5.4 or above');
}

//if (! class_exists('Leafo\ScssPhp\Version', false)) {
    include_once __DIR__ . '/src/Base/Range.php';
    include_once __DIR__ . '/src/Block.php';
    include_once __DIR__ . '/src/Colors.php';
    include_once __DIR__ . '/src/Compiler.php';
    include_once __DIR__ . '/src/Compiler/Environment.php';
	include_once __DIR__ . '/src/ValueConverter.php';
	include_once __DIR__ . '/src/OutputStyle.php';
	include_once __DIR__ . '/src/CompilationResult.php';
	include_once __DIR__ . '/src/Warn.php';
    include_once __DIR__ . '/src/Exception/CompilerException.php';
    include_once __DIR__ . '/src/Exception/ParserException.php';
    include_once __DIR__ . '/src/Exception/RangeException.php';
    include_once __DIR__ . '/src/Formatter.php';
    include_once __DIR__ . '/src/Formatter/Compact.php';
    include_once __DIR__ . '/src/Formatter/Compressed.php';
    include_once __DIR__ . '/src/Formatter/Crunched.php';
    include_once __DIR__ . '/src/Formatter/Debug.php';
    include_once __DIR__ . '/src/Formatter/Expanded.php';
    include_once __DIR__ . '/src/Formatter/Nested.php';
    include_once __DIR__ . '/src/Formatter/OutputBlock.php';
    include_once __DIR__ . '/src/Node.php';
    include_once __DIR__ . '/src/Node/Number.php';
    include_once __DIR__ . '/src/Parser.php';
    include_once __DIR__ . '/src/SourceMap/Base64VLQEncoder.php';
    include_once __DIR__ . '/src/SourceMap/SourceMapGenerator.php';
    include_once __DIR__ . '/src/Type.php';
    include_once __DIR__ . '/src/Util.php';
    include_once __DIR__ . '/src/Version.php';
//}
