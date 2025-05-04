<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic;

use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformModelClient;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ModelClient implements PlatformModelClient
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
}
