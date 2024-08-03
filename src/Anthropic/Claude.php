<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic;

use PhpLlm\LlmChain\ChatInterface;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\Role;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Claude implements ChatInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $model = 'claude-3-5-sonnet-20240620',
        private float $temperature = 1.0,
        private int $maxTokens = 1000,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): array
    {
        $system = $messages->getSystemMessage();

        $response = $this->httpClient->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ],
            'json' => [
                'model' => $this->model,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'system' => $system->content,
                'messages' => $messages->withoutSystemMessage(),
            ],
        ]);

        try {

            return $response->toArray();
        } catch (ClientException $e) {
            dump($e->getResponse()->getContent(false));

            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
