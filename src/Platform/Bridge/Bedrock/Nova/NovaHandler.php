<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use AsyncAws\BedrockRuntime\Input\InvokeModelRequest;
use AsyncAws\BedrockRuntime\Result\InvokeModelResponse;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\BedrockModelClient;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;

/**
 * @author Björn Altmann
 */
class NovaHandler implements BedrockModelClient
{
    public function __construct(
        private readonly BedrockRuntimeClient $bedrockRuntimeClient,
    ) {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Nova;
    }

    public function request(Model $model, array|string $payload, array $options = []): InvokeModelResponse
    {
        $modelOptions = [];
        if (isset($options['tools'])) {
            $modelOptions['toolConfig']['tools'] = $options['tools'];
        }

        if (isset($options['temperature'])) {
            $modelOptions['inferenceConfig']['temperature'] = $options['temperature'];
        }

        if (isset($options['max_tokens'])) {
            $modelOptions['inferenceConfig']['maxTokens'] = $options['max_tokens'];
        }

        $request = [
            'modelId' => $this->getModelId($model),
            'contentType' => 'application/json',
            'body' => json_encode(array_merge($payload, $modelOptions), \JSON_THROW_ON_ERROR),
        ];

        return $this->bedrockRuntimeClient->invokeModel(new InvokeModelRequest($request));
    }

    public function convert(InvokeModelResponse $bedrockResponse): ToolCallResponse|TextResponse
    {
        $data = json_decode($bedrockResponse->getBody(), true, 512, \JSON_THROW_ON_ERROR);

        if (!isset($data['output']) || 0 === \count($data['output'])) {
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
        if (0 !== \count($toolCalls)) {
            return new ToolCallResponse(...$toolCalls);
        }

        return new TextResponse($data['output']['message']['content'][0]['text']);
    }

    private function getModelId(Model $model): string
    {
        $configuredRegion = $this->bedrockRuntimeClient->getConfiguration()->get('region');
        $regionPrefix = substr((string) $configuredRegion, 0, 2);

        return $regionPrefix.'.amazon.'.$model->getName().'-v1:0';
    }
}
