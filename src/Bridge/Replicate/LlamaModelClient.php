<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Replicate;

use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Bridge\Meta\LlamaPromptConverter;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class LlamaModelClient implements ModelClient
{
    public function __construct(
        private Client $client,
        private LlamaPromptConverter $promptConverter,
    ) {
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Llama && $input instanceof MessageBag;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        Assert::isInstanceOf($model, Llama::class);
        Assert::isInstanceOf($input, MessageBag::class);

        return $this->client->request(sprintf('meta/meta-%s', $model->getVersion()), 'predictions', [
            'system' => $this->promptConverter->convertMessage($input->getSystemMessage() ?? new SystemMessage('')),
            'prompt' => $this->promptConverter->convertToPrompt($input->withoutSystemMessage()),
        ]);
    }
}
