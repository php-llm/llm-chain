<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\StructuredOutput\ChainProcessor as StructuredOutputProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor as ToolProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Clock;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Clock\Clock as SymfonyClock;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$clock = new Clock(new SymfonyClock());
$toolbox = Toolbox::create($clock);
$toolProcessor = new ToolProcessor($toolbox);
$structuredOutputProcessor = new StructuredOutputProcessor();
$chain = new Chain($platform, $llm, [$toolProcessor, $structuredOutputProcessor], [$toolProcessor, $structuredOutputProcessor]);

$messages = new MessageBag(Message::ofUser('What date and time is it?'));
$response = $chain->call($messages, ['response_format' => [
    'type' => 'json_schema',
    'json_schema' => [
        'name' => 'clock',
        'strict' => true,
        'schema' => [
            'type' => 'object',
            'properties' => [
                'date' => ['type' => 'string', 'description' => 'The current date in the format YYYY-MM-DD.'],
                'time' => ['type' => 'string', 'description' => 'The current time in the format HH:MM:SS.'],
            ],
            'required' => ['date', 'time'],
            'additionalProperties' => false,
        ],
    ],
]]);

dump($response->getContent());
