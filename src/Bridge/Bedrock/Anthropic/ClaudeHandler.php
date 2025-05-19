<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Bedrock\Anthropic;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use AsyncAws\BedrockRuntime\Input\InvokeModelRequest;
use AsyncAws\BedrockRuntime\Result\InvokeModelResponse;
use PhpLlm\LlmChain\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Bridge\Bedrock\BedrockModelClient;
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
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use Webmozart\Assert\Assert;

use function Symfony\Component\String\u;

final readonly class ClaudeHandler implements BedrockModelClient
{
    public function __construct(
        private BedrockRuntimeClient $bedrockRuntimeClient,
        private string $version = '2023-05-31',
    ) {
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Claude && $input instanceof MessageBagInterface;
    }

    public function request(Model $model, object|array|string $input, array $options = []): LlmResponse
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
            'anthropic_version' => 'bedrock-'.$this->version,
            'max_tokens' => $model->getOptions()['max_tokens'],
            'temperature' => $model->getOptions()['temperature'],
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
                                    'media_type' => u($content->url)->after('data:')->before(';')->replace('jpg', 'jpeg')->toString(),
                                    'data' => u($content->url)->after('base64,')->toString(),
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

        $request = [
            'modelId' => $this->getModelId($model),
            'contentType' => 'application/json',
            'body' => json_encode(array_merge($options, $body), JSON_THROW_ON_ERROR),
        ];

        $invokeModelResponse = $this->bedrockRuntimeClient->invokeModel(new InvokeModelRequest($request));

        return $this->convert($invokeModelResponse);
    }

    public function convert(InvokeModelResponse $bedrockResponse): LlmResponse
    {
        $data = json_decode($bedrockResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['content']) || 0 === count($data['content'])) {
            throw new RuntimeException('Response does not contain any content');
        }

        if (!isset($data['content'][0]['text'])) {
            throw new RuntimeException('Response content does not contain any text');
        }

        $toolCalls = [];
        foreach ($data['content'] as $content) {
            if ('tool_use' === $content['type']) {
                $toolCalls[] = new ToolCall($content['id'], $content['name'], $content['input']);
            }
        }
        if (!empty($toolCalls)) {
            return new ToolCallResponse(...$toolCalls);
        }

        return new TextResponse($data['content'][0]['text']);
    }

    private function getModelId(Model $model): string
    {
        $configuredRegion = $this->bedrockRuntimeClient->getConfiguration()->get('region');
        $regionPrefix = substr((string) $configuredRegion, 0, 2);

        return $regionPrefix.'.anthropic.'.$model->getName().'-v1:0';
    }
}
