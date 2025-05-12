<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\SerpApi;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENAI_API_KEY']) || empty($_ENV['SERP_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY and SERP_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new GPT(GPT::GPT_4O_MINI);

$serpApi = new SerpApi(HttpClient::create(), $_ENV['SERP_API_KEY']);
$toolbox = Toolbox::create($serpApi);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $model, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('Who is the current chancellor of Germany?'));
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
