<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\AwsBedrock\Language;

use PhpLlm\LlmChain\Bridge\AwsBedrock\BedrockLanguageModel;
use PhpLlm\LlmChain\Bridge\AwsBedrock\BedrockRequestSigner;
use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Content\Content;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformResponseFactory;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class ModelClient implements PlatformResponseFactory
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter] private BedrockRequestSigner $requestSigner,
        private string $region,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $input instanceof MessageBag && $model instanceof BedrockLanguageModel;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        if ($input instanceof MessageBag) {
            $systemMessagesMap = null;

            if ($systemMessage = $input->getSystemMessage()) {
                $systemMessagesMap = [
                    [
                        'text' => $systemMessage->content,
                    ],
                ];
            }

            $messagesMap = array_map(
                function (MessageInterface $inputEntry) {
                    if ($inputEntry instanceof ToolCallMessage) {
                        return [
                            'role' => 'user',
                            'content' => [
                                [
                                    'toolResult' => [
                                        'toolUseId' => $inputEntry->toolCall->id,
                                        'content' => [
                                            [
                                                'text' => $inputEntry->content,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    } elseif ($inputEntry instanceof AssistantMessage) {
                        return [
                            'role' => 'assistant',
                            'content' => [
                                array_filter([
                                    'text' => $inputEntry->content,
                                    'toolUse' => ($inputEntry->toolCalls[0] ?? null) ? [
                                        'toolUseId' => $inputEntry->toolCalls[0]->id,
                                        'name' => $inputEntry->toolCalls[0]->name,
                                        'input' => $inputEntry->toolCalls[0]->arguments,
                                    ] : null,
                                ], fn ($content) => !is_null($content)
                                ),
                            ],
                        ];
                    } elseif ($inputEntry instanceof UserMessage) {
                        return [
                            'role' => 'user',
                            'content' => array_map(
                                function (Content $inputContent) {
                                    if ($inputContent instanceof Text) {
                                        return [
                                            'text' => $inputContent->text,
                                        ];
                                    } elseif ($inputContent instanceof Image) {
                                        return [
                                            'image' => [
                                                'format' => match ($inputContent->getFormat()) {
                                                    'image/png' => 'png',
                                                    'image/jpg', 'image/jpeg' => 'jpeg',
                                                    'image/gif' => 'gif',
                                                    'image/webp' => 'webp',
                                                    default => throw new \Exception('Invalid Image type')
                                                },
                                                'source' => [
                                                    'bytes' => $inputContent->asBase64(),
                                                ],
                                            ],
                                        ];
                                    }
                                }, $inputEntry->content
                            ),
                        ];
                    }
                },
                $input->withoutSystemMessage()->getMessages()
            );

            $toolConfig = null;
            if (isset($options['tools'])) {
                $toolConfig = [
                    'tools' => array_map(
                        fn (Metadata $toolConfig) => [
                            'toolSpec' => [
                                'description' => $toolConfig->description,
                                'name' => $toolConfig->name,
                                'inputSchema' => [
                                    'json' => $toolConfig->parameters,
                                ],
                            ],
                        ], $options['tools']
                    ),
                ];

                unset($options['tools']);
            }

            $inferenceConfig = array_reduce(
                array_filter(
                    $options,
                    fn ($value, $optionKey) => in_array($optionKey, ['max_tokens', 'stop_sequences', 'temperature', 'top_p'])
                ),
                function (array $inferenceConfigAcc, string $optionValue, string $optionKey) use (&$options) {
                    unset($options[$optionKey]);

                    $optionKey = implode(
                        '',
                        array_map(
                            fn ($part, $partIndex) => 0 === $partIndex ? mb_lcfirst($part) : mb_ucfirst($part),
                            explode('_', $optionKey)
                        )
                    );

                    $inferenceConfigAcc[$optionKey] = $optionValue;

                    return $inferenceConfigAcc;
                },
                []
            );

            $additionalModelRequestFields = array_reduce(
                array_filter(
                    $options,
                    fn ($value, $optionKey) => in_array($optionKey, ['top_k'])
                ),
                function (array $additionalModelRequestFieldsAcc, string $optionValue, string $optionKey) use (&$options) {
                    unset($options[$optionKey]);

                    $optionKey = implode(
                        '',
                        array_map(
                            fn ($part, $partIndex) => 0 === $partIndex ? mb_lcfirst($part) : mb_ucfirst($part),
                            explode('_', $optionKey)
                        )
                    );

                    $additionalModelRequestFieldsAcc[$optionKey] = $optionValue;

                    return $additionalModelRequestFieldsAcc;
                },
                []
            );

            $signedParameters = $this->requestSigner->signRequest(
                method: 'POST',
                endpoint: $bedrockEndpoint = sprintf(
                    'https://bedrock-runtime.%s.amazonaws.com/model/%s/%s',
                    $this->region,
                    $model->getName(),
                    ($options['stream'] ?? false) ? 'converse-stream' : 'converse'
                ),
                jsonBody: array_filter(
                    array_merge($options, [
                        'messages' => $messagesMap,
                        'system' => $systemMessagesMap,
                        'toolConfig' => $toolConfig,
                        'inferenceConfig' => count($inferenceConfig) > 0 ? $inferenceConfig : null,
                        'additionalModelRequestFields' => count($additionalModelRequestFields) > 0 ? [
                            'inferenceConfig' => $additionalModelRequestFields,
                        ] : null,
                    ]), fn ($cValue) => !is_null($cValue)
                )
            );

            return $this->httpClient->request('POST', $bedrockEndpoint, $signedParameters);
        } else {
            throw new \Exception('Invalid input, input must be a MessageBag');
        }
    }
}
