<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Document\Transformer;

use PhpLlm\LlmChain\Store\Document\TransformerInterface;

final readonly class ChainTransformer implements TransformerInterface
{
    /**
     * @var TransformerInterface[]
     */
    private array $transformers;

    /**
     * @param iterable<TransformerInterface> $transformers
     */
    public function __construct(iterable $transformers)
    {
        $this->transformers = $transformers instanceof \Traversable ? iterator_to_array($transformers) : $transformers;
    }

    public function __invoke(iterable $documents, array $options = []): iterable
    {
        foreach ($this->transformers as $transformer) {
            $documents = $transformer($documents, $options);
        }

        return $documents;
    }
}
