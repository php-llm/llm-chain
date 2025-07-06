<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock\Anthropic;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use AsyncAws\BedrockRuntime\Input\InvokeModelRequest;
use AsyncAws\BedrockRuntime\Result\InvokeModelResponse;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\BedrockModelClient;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;

/**
 * @author Björn Altmann
 */
final readonly class ClaudeHandler implements BedrockModelClient
{
    public function __construct(
        private BedrockRuntimeClient $bedrockRuntimeClient,
        private string $version = '2023-05-31',
    ) {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Claude;
    }

    public function request(Model $model, array|string $payload, array $options = []): LlmResponse
    {
        unset($payload['model']);

        if (isset($options['tools'])) {
            $options['tool_choice'] = ['type' => 'auto'];
        }

        if (!isset($options['anthropic_version'])) {
            $options['anthropic_version'] = 'bedrock-'.$this->version;
        }

        $request = [
            'modelId' => $this->getModelId($model),
            'contentType' => 'application/json',
            'body' => json_encode(array_merge($options, $payload), \JSON_THROW_ON_ERROR),
        ];

        $invokeModelResponse = $this->bedrockRuntimeClient->invokeModel(new InvokeModelRequest($request));

        return $this->convert($invokeModelResponse);
    }

    public function convert(InvokeModelResponse $bedrockResponse): LlmResponse
    {
        $data = json_decode($bedrockResponse->getBody(), true, 512, \JSON_THROW_ON_ERROR);

        if (!isset($data['content']) || 0 === \count($data['content'])) {
            throw new RuntimeException('Response does not contain any content');
        }

        if (!isset($data['content'][0]['text']) && !isset($data['content'][0]['type'])) {
            throw new RuntimeException('Response content does not contain any text or type');
        }

        $toolCalls = [];
        foreach ($data['content'] as $content) {
            if ('tool_use' === $content['type']) {
                $toolCalls[] = new ToolCall($content['id'], $content['name'], $content['input']);
            }
        }
        if (0 !== \count($toolCalls)) {
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
