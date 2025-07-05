<?php

use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\PlatformFactory;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['HUGGINGFACE_KEY'])) {
    echo 'Please set the HUGGINGFACE_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['HUGGINGFACE_KEY']);
$model = new Model('facebook/bart-large-cnn');

$longText = <<<TEXT
    The tower is 324 metres (1,063 ft) tall, about the same height as an 81-storey building, and the tallest structure
    in Paris. Its base is square, measuring 125 metres (410 ft) on each side. During its construction, the Eiffel Tower
    surpassed the Washington Monument to become the tallest man-made structure in the world, a title it held for 41
    years until the Chrysler Building in New York City was finished in 1930. It was the first structure to reach a
    height of 300 metres. Due to the addition of a broadcasting aerial at the top of the tower in 1957, it is now taller
    than the Chrysler Building by 5.2 metres (17 ft). Excluding transmitters, the Eiffel Tower is the second tallest
    free-standing structure in France after the Millau Viaduct.
    TEXT;

$response = $platform->request($model, $longText, [
    'task' => Task::SUMMARIZATION,
]);

echo $response->asText().\PHP_EOL;
