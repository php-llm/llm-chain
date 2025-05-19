<?php

namespace PhpLlm\LlmChain\Bridge\Bedrock\Meta;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use AsyncAws\BedrockRuntime\Input\InvokeModelRequest;
use AsyncAws\BedrockRuntime\Result\InvokeModelResponse;
use PhpLlm\LlmChain\Bridge\Bedrock\BedrockModelClient;
use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Bridge\Meta\LlamaPromptConverter;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use Webmozart\Assert\Assert;

class LlamaModelClient implements BedrockModelClient
{
    public function __construct(private readonly BedrockRuntimeClient $bedrockRuntimeClient, private readonly LlamaPromptConverter $promptConverter = new LlamaPromptConverter())
    {
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Llama && $input instanceof MessageBagInterface;
    }

    public function request(Model $model, object|array|string $input, array $options = []): LlmResponse
    {
        Assert::isInstanceOf($model, Llama::class);
        Assert::isInstanceOf($input, MessageBagInterface::class);

        $body = [
            'prompt' => $this->promptConverter->convertToPrompt($input),
        ];

        $response = $this->bedrockRuntimeClient->invokeModel(new InvokeModelRequest([
            'modelId' => $this->getModelId($model),
            'contentType' => 'application/json',
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ]));

        return $this->convert($response);
    }

    public function convert(InvokeModelResponse $bedrockResponse): LlmResponse
    {
        $responseBody = json_decode($bedrockResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($responseBody['generation'])) {
            throw new \RuntimeException('Response does not contain any content');
        }

        return new TextResponse($responseBody['generation']);
    }

    private function getModelId(Model $model): string
    {
        $configuredRegion = $this->bedrockRuntimeClient->getConfiguration()->get('region');
        $regionPrefix = substr((string) $configuredRegion, 0, 2);
        $modifiedModelName = str_replace('llama-3', 'llama3', $model->getName());

        return $regionPrefix.'.meta.'.str_replace('.', '-', $modifiedModelName).'-v1:0';
    }
}
