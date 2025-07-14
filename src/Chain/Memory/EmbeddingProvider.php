<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Memory;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Platform\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\MessageInterface;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\PlatformInterface;
use PhpLlm\LlmChain\Store\VectorStoreInterface;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class EmbeddingProvider implements MemoryProviderInterface
{
    public function __construct(
        private PlatformInterface $platform,
        private Model $model,
        private VectorStoreInterface $vectorStore,
    ) {
    }

    public function loadMemory(Input $input): array
    {
        $messages = $input->messages->getMessages();
        /** @var MessageInterface|null $userMessage */
        $userMessage = $messages[array_key_last($messages)] ?? null;

        if (!$userMessage instanceof UserMessage) {
            return [];
        }

        $userMessageTextContent = array_filter(
            $userMessage->content,
            static fn (ContentInterface $content): bool => $content instanceof Text,
        );

        if (0 === \count($userMessageTextContent)) {
            return [];
        }

        $userMessageTextContent = array_shift($userMessageTextContent);
        \assert($userMessageTextContent instanceof Text);

        $vectors = $this->platform->request($this->model, $userMessageTextContent->text)->asVectors();
        $foundEmbeddingContent = $this->vectorStore->query($vectors[0]);
        if (0 === \count($foundEmbeddingContent)) {
            return [];
        }

        $content = '## Dynamic memories fitting user message'.\PHP_EOL.\PHP_EOL;
        foreach ($foundEmbeddingContent as $document) {
            $content .= json_encode($document->metadata);
        }

        return [new Memory($content)];
    }
}
