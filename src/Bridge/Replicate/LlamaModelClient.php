<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Replicate;

use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class LlamaModelClient implements ModelClient
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Llama;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        Assert::isInstanceOf($model, Llama::class);

        return $this->client->request(sprintf('meta/meta-%s', $model->getName()), 'predictions', $payload);
    }
}
