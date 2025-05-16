<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Bedrock\Nova;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use AsyncAws\BedrockRuntime\Input\InvokeModelRequest;
use AsyncAws\BedrockRuntime\Result\InvokeModelResponse;
use PhpLlm\LlmChain\Bridge\Bedrock\BedrockModelClient;
use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use Webmozart\Assert\Assert;

class NovaHandler implements BedrockModelClient
{
    public function __construct(
        private readonly BedrockRuntimeClient $bedrockRuntimeClient,
        private readonly NovaPromptConverter $novaPromptConverter = new NovaPromptConverter(),
    ) {
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Nova && $input instanceof MessageBagInterface;
    }

    public function request(Model $model, object|array|string $input, array $options = []): LlmResponse
    {
        Assert::isInstanceOf($input, MessageBagInterface::class);

        $modelOptions = [];
        if (isset($options['tools'])) {
            $tools = $options['tools'];
            $modelOptions['toolConfig'] = [];
            /** @var Metadata $tool */
            foreach ($tools as $tool) {
                $toolDefinition = [
                    'name' => $tool->name,
                    'description' => $tool->description,
                    'inputSchema' => $tool->parameters ?? new \stdClass(),
                ];
                $modelOptions['toolConfig']['tools'][]['toolSpec'] = $toolDefinition;
            }
            // $modelOptions['toolChoice'] = ['auto' => []];
        }

        if (isset($options['temperature'])) {
            $modelOptions['inferenceConfig']['temperature'] = $options['temperature'];
        }

        if (isset($options['max_tokens'])) {
            $modelOptions['inferenceConfig']['maxTokens'] = $options['max_tokens'];
        }

        $body = [
            'messages' => $this->novaPromptConverter->convertToPrompt($input->withoutSystemMessage()),
        ];

        if ($input->getSystemMessage()) {
            $body['system'][]['text'] = $input->getSystemMessage()->content;
        }

        $request = [
            'modelId' => $this->getModelId($model),
            'contentType' => 'application/json',
            'body' => json_encode(array_merge($body, $modelOptions), JSON_THROW_ON_ERROR),
        ];

        $invokeModelResponse = $this->bedrockRuntimeClient->invokeModel(new InvokeModelRequest($request));

        return $this->convert($invokeModelResponse);
    }

    public function convert(InvokeModelResponse $bedrockResponse): LlmResponse
    {
        $data = json_decode($bedrockResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['output']) || 0 === count($data['output'])) {
            throw new RuntimeException('Response does not contain any content');
        }

        if (!isset($data['output']['message']['content'][0]['text'])) {
            throw new RuntimeException('Response content does not contain any text');
        }

        $toolCalls = [];
        foreach ($data['output']['message']['content'] as $content) {
            if (isset($content['toolUse'])) {
                $toolCalls[] = new ToolCall($content['toolUse']['toolUseId'], $content['toolUse']['name'], $content['toolUse']['input']);
            }
        }
        if (!empty($toolCalls)) {
            return new ToolCallResponse(...$toolCalls);
        }

        return new TextResponse($data['output']['message']['content'][0]['text']);
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

        $prefix = $isEuRegion ? 'eu' : 'us';

        return $prefix.'.amazon.'.$model->getName().'-v1:0';
    }
}
