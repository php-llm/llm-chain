<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\StructuredOutput\SchemaFactory;
use Symfony\Component\Serializer\SerializerInterface;
use function Symfony\Component\String\u;

final class StructuredOutputChain
{
    public function __construct(
        private LanguageModel $llm,
        private SchemaFactory $schemaFactory,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @param class-string         $responseFormat
     * @param array<string, mixed> $options
     */
    public function call(MessageBag $messages, string $responseFormat, array $options = []): object
    {
        $schema = $this->schemaFactory->buildSchema($responseFormat);
        $options['response_format'] = [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => u($responseFormat)->afterLast('\\')->toString(),
                'schema' => $schema,
                'strict' => true,
            ],
        ];

        $response = $this->llm->call($messages, $options);

        return $this->serializer->deserialize($response['choices'][0]['message']['content'], $responseFormat, 'json');
    }
}
