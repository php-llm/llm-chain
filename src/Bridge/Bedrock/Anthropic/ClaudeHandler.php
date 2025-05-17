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
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use Webmozart\Assert\Assert;

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
        $euRegions = ['eu-west-1', 'eu-central-1', 'eu-west-2', 'eu-west-3', 'eu-south-1', 'eu-north-1'];
        $usRegions = ['us-east-1', 'us-east-2', 'us-west-1', 'us-west-2'];

        $configuredRegion = $this->bedrockRuntimeClient->getConfiguration()->get('region');
        $isEuRegion = in_array($configuredRegion, $euRegions, true);
        $isUsRegion = in_array($configuredRegion, $usRegions, true);
        if ($isEuRegion && $isUsRegion) {
            throw new RuntimeException('Unsupported region: '.$configuredRegion);
        }

        $supportedModels = [
            Claude::SONNET_37,
            Claude::SONNET_35,
            Claude::SONNET_3,
            Claude::HAIKU_3,
            Claude::HAIKU_35,
            Claude::OPUS_3,
        ];

        if (!in_array($model->getName(), $supportedModels, true)) {
            throw new RuntimeException('Unsupported model: '.$model->getName());
        }

        $prefix = $isEuRegion ? 'eu' : 'us';

        return $prefix.'.anthropic.'.$model->getName().'-v1:0';
    }
}
