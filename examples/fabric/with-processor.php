<?php

declare(strict_types=1);

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Fabric\FabricInputProcessor;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

require_once dirname(__DIR__).'/../vendor/autoload.php';

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

// Check if Fabric patterns package is installed
if (!is_dir(dirname(__DIR__, 2).'/vendor/php-llm/fabric-pattern')) {
    echo 'Fabric patterns are not installed.'.PHP_EOL;
    echo 'Please install them with: composer require php-llm/fabric-pattern'.PHP_EOL;
    exit(1);
}

// Initialize platform and model
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new GPT(GPT::GPT_4O_MINI);

// Create chain with Fabric processor
$processor = new FabricInputProcessor();
$chain = new Chain($platform, $model, [$processor]);

// Example code to analyze
$code = <<<'CODE'
    function processUserData($data) {
        $sql = "SELECT * FROM users WHERE id = " . $data['id'];
        $result = mysql_query($sql);

        while ($row = mysql_fetch_array($result)) {
            echo $row['name'] . " - " . $row['email'];
        }
    }
    CODE;

// Create messages
$messages = new MessageBag(
    Message::ofUser("Analyze this PHP code for security issues:\n\n".$code)
);

// Call with Fabric pattern
$response = $chain->call($messages, ['fabric_pattern' => 'analyze_code']);

echo 'Code Analysis using Fabric pattern "analyze_code":'.PHP_EOL;
echo '=================================================='.PHP_EOL;
echo $response->getContent().PHP_EOL;