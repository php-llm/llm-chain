<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\StructuredOutput;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Response\StructuredResponse;
use Symfony\Component\Serializer\SerializerInterface;

final class ChainProcessor implements InputProcessor, OutputProcessor
{
    private string $outputStructure;

    public function __construct(
        private readonly ResponseFormatFactoryInterface $responseFormatFactory,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!isset($options['output_structure'])) {
            return;
        }

        if (!$input->llm->supportsStructuredOutput()) {
            throw MissingModelSupport::forStructuredOutput($input->llm::class);
        }

        if (true === ($options['stream'] ?? false)) {
            throw new InvalidArgumentException('Streamed responses are not supported for structured output');
        }

        $options['response_format'] = $this->responseFormatFactory->create($options['output_structure']);

        $this->outputStructure = $options['output_structure'];
        unset($options['output_structure']);

        $input->setOptions($options);
    }

    public function processOutput(Output $output): ?StructuredResponse
    {
        $options = $output->options;

        if (!isset($options['output_structure'])) {
            return null;
        }

        return new StructuredResponse(
            $this->serializer->deserialize($output->response->getContent(), $this->outputStructure, 'json')
        );
    }
}
