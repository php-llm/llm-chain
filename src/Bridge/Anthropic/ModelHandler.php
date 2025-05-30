<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic;

use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Content;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\StreamResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

use function Symfony\Component\String\u;

final readonly class ModelHandler implements ModelClient, ResponseConverter
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
        private string $version = '2023-06-01',
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Claude && $input instanceof MessageBagInterface;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        Assert::isInstanceOf($input, MessageBagInterface::class);

        if (isset($options['tools'])) {
            $tools = $options['tools'];
            $options['tools'] = [];
            /** @var Metadata $tool */
            foreach ($tools as $tool) {
                $toolDefinition = [
                    'name' => $tool->name,
                    'description' => $tool->description,
                    'input_schema' => $tool->parameters ?? ['type' => 'object'],
                ];
                $options['tools'][] = $toolDefinition;
            }
            $options['tool_choice'] = ['type' => 'auto'];
        }

        $body = [
            'model' => $model->getName(),
            'messages' => $input->withoutSystemMessage()->jsonSerialize(),
        ];

        $body['messages'] = array_map(static function (MessageInterface $message) {
            if ($message instanceof ToolCallMessage) {
                return [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $message->toolCall->id,
                            'content' => $message->content,
                        ],
                    ],
                ];
            }
            if ($message instanceof AssistantMessage && $message->hasToolCalls()) {
                return [
                    'role' => 'assistant',
                    'content' => array_map(static function (ToolCall $toolCall) {
                        return [
                            'type' => 'tool_use',
                            'id' => $toolCall->id,
                            'name' => $toolCall->name,
                            'input' => empty($toolCall->arguments) ? new \stdClass() : $toolCall->arguments,
                        ];
                    }, $message->toolCalls),
                ];
            }
            if ($message instanceof UserMessage && $message->hasImageContent()) {
                // make sure images are encoded for Bedrock invocation
                return [
                    'role' => 'user',
                    'content' => array_map(static function (Content $content) {
                        if ($content instanceof Image) {
                            return [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => u($content->getFormat())->replace('jpg', 'jpeg')->toString(),
                                    'data' => $content->asBase64(),
                                ],
                            ];
                        }

                        return $content;
                    }, $message->content),
                ];
            }

            return $message;
        }, $body['messages']);

        if ($system = $input->getSystemMessage()) {
            $body['system'] = $system->content;
        }

        return $this->httpClient->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->version,
            ],
            'json' => array_merge($options, $body),
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        if ($options['stream'] ?? false) {
            return new StreamResponse($this->convertStream($response));
        }

        $data = $response->toArray();

        if (!isset($data['content']) || 0 === count($data['content'])) {
            throw new RuntimeException('Response does not contain any content');
        }

        $toolCalls = [];
        foreach ($data['content'] as $content) {
            if ('tool_use' === $content['type']) {
                $toolCalls[] = new ToolCall($content['id'], $content['name'], $content['input']);
            }
        }

        if (!isset($data['content'][0]['text']) && 0 === count($toolCalls)) {
            throw new RuntimeException('Response content does not contain any text nor tool calls.');
        }

        if (!empty($toolCalls)) {
            return new ToolCallResponse(...$toolCalls);
        }

        return new TextResponse($data['content'][0]['text']);
    }

    private function convertStream(ResponseInterface $response): \Generator
    {
        foreach ((new EventSourceHttpClient())->stream($response) as $chunk) {
            if (!$chunk instanceof ServerSentEvent || '[DONE]' === $chunk->getData()) {
                continue;
            }

            try {
                $data = $chunk->getArrayData();
            } catch (JsonException) {
                // try catch only needed for Symfony 6.4
                continue;
            }

            if ('content_block_delta' != $data['type'] || !isset($data['delta']['text'])) {
                continue;
            }

            yield $data['delta']['text'];
        }
    }
}
