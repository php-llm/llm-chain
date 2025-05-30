<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Replicate\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Platform\Bridge\Meta\LlamaPromptConverter;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Model;

final class LlamaMessageBagNormalizer extends ModelContractNormalizer
{
    public function __construct(
        private readonly LlamaPromptConverter $promptConverter = new LlamaPromptConverter(),
    ) {
    }

    protected function supportedDataClass(): string
    {
        return MessageBagInterface::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Llama;
    }

    /**
     * @param MessageBagInterface $data
     *
     * @return array{system: string, prompt: string}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'system' => $this->promptConverter->convertMessage($data->getSystemMessage() ?? new SystemMessage('')),
            'prompt' => $this->promptConverter->convertToPrompt($data->withoutSystemMessage()),
        ];
    }
}
