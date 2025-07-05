<?php

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract as AnthropicContract;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract as NovaContract;
use PhpLlm\LlmChain\Platform\Bridge\Meta\Contract as LlamaContract;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\PlatformInterface;
use PhpLlm\LlmChain\Platform\Response\ResponsePromise;

/**
 * @author Björn Altmann
 */
class Platform implements PlatformInterface
{
    /**
     * @var BedrockModelClient[]
     */
    private readonly array $modelClients;

    /**
     * @param iterable<BedrockModelClient> $modelClients
     */
    public function __construct(
        iterable $modelClients,
        private ?Contract $contract = null,
    ) {
        $this->contract = $contract ?? Contract::create(
            new AnthropicContract\AssistantMessageNormalizer(),
            new AnthropicContract\DocumentNormalizer(),
            new AnthropicContract\DocumentUrlNormalizer(),
            new AnthropicContract\ImageNormalizer(),
            new AnthropicContract\ImageUrlNormalizer(),
            new AnthropicContract\MessageBagNormalizer(),
            new AnthropicContract\ToolCallMessageNormalizer(),
            new AnthropicContract\ToolNormalizer(),
            new LlamaContract\MessageBagNormalizer(),
            new NovaContract\AssistantMessageNormalizer(),
            new NovaContract\MessageBagNormalizer(),
            new NovaContract\ToolCallMessageNormalizer(),
            new NovaContract\ToolNormalizer(),
            new NovaContract\UserMessageNormalizer(),
        );
        $this->modelClients = $modelClients instanceof \Traversable ? iterator_to_array($modelClients) : $modelClients;
    }

    public function request(Model $model, array|string|object $input, array $options = []): ResponsePromise
    {
        $payload = $this->contract->createRequestPayload($model, $input);
        $options = array_merge($model->getOptions(), $options);

        if (isset($options['tools'])) {
            $options['tools'] = $this->contract->createToolOption($options['tools'], $model);
        }

        return $this->doRequest($model, $payload, $options);
    }

    /**
     * @param array<string, mixed>|string $payload
     * @param array<string, mixed>        $options
     */
    private function doRequest(Model $model, array|string $payload, array $options = []): ResponsePromise
    {
        foreach ($this->modelClients as $modelClient) {
            if ($modelClient->supports($model)) {
                $response = $modelClient->request($model, $payload, $options);

                return new ResponsePromise(
                    $modelClient->convert(...),
                    new RawBedrockResponse($response),
                    $options,
                );
            }
        }

        throw new RuntimeException('No response factory registered for model "'.$model::class.'" with given input.');
    }
}
