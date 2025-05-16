<?php

namespace PhpLlm\LlmChain\Bridge\Bedrock\Meta;

use AsyncAws\BedrockRuntime\BedrockRuntimeClient;
use AsyncAws\BedrockRuntime\Input\InvokeModelRequest;
use AsyncAws\BedrockRuntime\Result\InvokeModelResponse;
use PhpLlm\LlmChain\Bridge\Bedrock\BedrockModelClient;
use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Bridge\Meta\LlamaPromptConverter;
use PhpLlm\LlmChain\Exception\RuntimeException;
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
        $euRegions = ['eu-west-1', 'eu-central-1', 'eu-west-2', 'eu-west-3', 'eu-south-1', 'eu-north-1'];
        $usRegions = ['us-east-1', 'us-east-2', 'us-west-1', 'us-west-2'];

        $configuredRegion = $this->bedrockRuntimeClient->getConfiguration()->get('region');
        $isEuRegion = in_array($configuredRegion, $euRegions, true);
        $isUsRegion = in_array($configuredRegion, $usRegions, true);
        if ($isEuRegion && $isUsRegion) {
            throw new RuntimeException('Unsupported region: '.$configuredRegion);
        }

        $prefix = $isEuRegion ? 'eu' : 'us';
        $modifiedModelName = str_replace('llama-3', 'llama3', $model->getName());

        return $prefix.'.meta.'.str_replace('.', '-', $modifiedModelName).'-v1:0';
    }
}
