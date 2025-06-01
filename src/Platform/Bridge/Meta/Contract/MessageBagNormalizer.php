<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Meta\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Platform\Bridge\Meta\LlamaPromptConverter;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class MessageBagNormalizer extends ModelContractNormalizer
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
     * @return array{prompt: string}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'prompt' => $this->promptConverter->convertToPrompt($data),
        ];
    }
}
