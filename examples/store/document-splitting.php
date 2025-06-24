<?php

declare(strict_types=1);

use PhpLlm\LlmChain\Store\Document\Loader\TextFileLoader;
use PhpLlm\LlmChain\Store\Document\Transformer\TextSplitTransformer;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

$loader = new TextFileLoader();
$splitter = new TextSplitTransformer();
$source = dirname(__DIR__, 2).'/tests/Fixture/lorem.txt';

$documents = iterator_to_array($splitter($loader($source)));

dump($documents);
