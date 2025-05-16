<?php

namespace PhpLlm\LlmChain\Bridge\Bedrock;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\PlatformInterface;

class Platform implements PlatformInterface
{
    /**
     * @var BedrockModelClient[]
     */
    private readonly array $modelClients;

    /**
     * @param iterable<BedrockModelClient> $modelClients
     */
    public function __construct(iterable $modelClients)
    {
        $this->modelClients = $modelClients instanceof \Traversable ? iterator_to_array($modelClients) : $modelClients;
    }

    public function request(Model $model, array|string|object $input, array $options = []): ResponseInterface
    {
        $options = array_merge($model->getOptions(), $options);

        return $this->doRequest($model, $input, $options);
    }

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    private function doRequest(Model $model, array|string|object $input, array $options = []): ResponseInterface
    {
        foreach ($this->modelClients as $modelClient) {
            if ($modelClient->supports($model, $input)) {
                return $modelClient->request($model, $input, $options);
            }
        }

        throw new RuntimeException('No response factory registered for model "'.$model::class.'" with given input.');
    }
}
