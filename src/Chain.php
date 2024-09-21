<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\StructuredOutput\ResponseFormatFactory;
use PhpLlm\LlmChain\ToolBox\RegistryInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class Chain
{
    public function __construct(
        private LanguageModel $llm,
        private ?RegistryInterface $toolRegistry = null,
        private ?ResponseFormatFactory $responseFormatFactory = null,
        private ?SerializerInterface $serializer = null,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBag $messages, array $options = []): string|object
    {
        $llmOptions = $options;

        if (!array_key_exists('tools', $llmOptions) && null !== $this->toolRegistry && $this->llm->hasToolSupport()) {
            $llmOptions['tools'] = $this->toolRegistry->getMap();
        }

        if (array_key_exists('output_structure', $llmOptions) && null !== $this->responseFormatFactory && $this->llm->hasStructuredOutputSupport()) {
            $llmOptions['response_format'] = $this->responseFormatFactory->create($llmOptions['output_structure']);
            unset($llmOptions['output_structure']);
        }

        $response = $this->llm->call($messages, $llmOptions);

        while ($response->hasToolCalls()) {
            $clonedMessages = clone $messages;
            $clonedMessages[] = Message::ofAssistant(toolCalls: $response->getToolCalls());

            foreach ($response->getToolCalls() as $toolCall) {
                $result = $this->toolRegistry->execute($toolCall);
                $clonedMessages[] = Message::ofToolCall($toolCall, $result);
            }

            $response = $this->llm->call($clonedMessages, $llmOptions);
        }

        if (!array_key_exists('output_structure', $options) || null === $this->serializer) {
            return $response->getContent();
        }

        return $this->serializer->deserialize($response->getContent(), $options['output_structure'], 'json');
    }
}
