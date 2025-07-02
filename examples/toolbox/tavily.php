<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Tavily;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['OPENAI_API_KEY'] || !$_ENV['TAVILY_API_KEY']) {
    echo 'Please set the OPENAI_API_KEY and TAVILY_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new GPT(GPT::GPT_4O_MINI);

$tavily = new Tavily(HttpClient::create(), $_ENV['TAVILY_API_KEY']);
$toolbox = Toolbox::create($tavily);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $model, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('What was the latest game result of Dallas Cowboys?'));
$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
