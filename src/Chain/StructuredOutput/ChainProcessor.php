<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\StructuredOutput;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\Response\StructuredResponse;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class ChainProcessor implements InputProcessor, OutputProcessor
{
    private string $outputStructure;

    public function __construct(
        private readonly ResponseFormatFactoryInterface $responseFormatFactory = new ResponseFormatFactory(),
        private ?SerializerInterface $serializer = null,
    ) {
        if (null === $this->serializer) {
            $propertyInfo = new PropertyInfoExtractor([], [new PhpDocExtractor()]);
            $normalizers = [new ObjectNormalizer(propertyTypeExtractor: $propertyInfo), new ArrayDenormalizer()];
            $this->serializer = $serializer ?? new Serializer($normalizers, [new JsonEncoder()]);
        }
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

    public function processOutput(Output $output): void
    {
        $options = $output->options;

        if ($output->response instanceof StructuredResponse) {
            return;
        }

        if (!isset($options['response_format'])) {
            return;
        }

        if (!isset($this->outputStructure)) {
            $output->response = new StructuredResponse(json_decode($output->response->getContent(), true));

            return;
        }

        $output->response = new StructuredResponse(
            $this->serializer->deserialize($output->response->getContent(), $this->outputStructure, 'json')
        );
    }
}
