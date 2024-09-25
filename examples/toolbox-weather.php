<?php

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\ToolBox\Tool\OpenMeteo;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$httpClient = HttpClient::create();
$platform = new OpenAI($httpClient, getenv('OPENAI_API_KEY'));
$llm = new Gpt($platform, Version::gpt4oMini());

$wikipedia = new OpenMeteo($httpClient);
$toolBox = new ToolBox(new ToolAnalyzer(), [$wikipedia]);
$chain = new Chain($llm, $toolBox);

$messages = new MessageBag(Message::ofUser('How is the weather currently in Berlin?'));
$response = $chain->call($messages);

echo $response.PHP_EOL;
