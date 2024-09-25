<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\StructuredOutput;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use Symfony\Component\Serializer\SerializerInterface;

final class ChainProcessor implements InputProcessor, OutputProcessor
{
    private string $outputStructure;

    public function __construct(
        private readonly ResponseFormatFactory $responseFormatFactory,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function processInput(Input $input): void
    {
        if (!$input->llm->supportsStructuredOutput()) {
            throw MissingModelSupport::forStructuredOutput($input->llm::class);
        }

        $options = $input->getOptions();
        $options['response_format'] = $this->responseFormatFactory->create($options['output_structure']);

        $this->outputStructure = $options['output_structure'];
        unset($options['output_structure']);

        $input->setOptions($options);
    }

    public function processOutput(Output $output): object
    {
        return $this->serializer->deserialize($output->response->getContent(), $this->outputStructure, 'json');
    }
}
