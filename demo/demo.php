<?php

include(dirname(__DIR__).'/src/Processor.php');

$processor = new \TechBird\CsvTest\Processor();

$processor->setInputFileName(dirname(__DIR__).'/demo/test.csv');
$processor->setOutputFileName('testOutput.csv');

$processor->process();