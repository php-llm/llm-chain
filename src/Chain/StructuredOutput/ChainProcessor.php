<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\StructuredOutput;

use PhpLlm\LlmChain\Chain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Chain\Exception\MissingModelSupportException;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessorInterface;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessorInterface;
use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Response\ObjectResponse;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ChainProcessor implements InputProcessorInterface, OutputProcessorInterface
{
    private string $outputStructure;

    public function __construct(
        private readonly ResponseFormatFactoryInterface $responseFormatFactory = new ResponseFormatFactory(),
        private ?SerializerInterface $serializer = null,
    ) {
        if (null === $this->serializer) {
            $propertyInfo = new PropertyInfoExtractor([], [new PhpDocExtractor()]);
            $normalizers = [new ObjectNormalizer(propertyTypeExtractor: $propertyInfo), new ArrayDenormalizer()];
            $this->serializer = new Serializer($normalizers, [new JsonEncoder()]);
        }
    }

    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!isset($options['output_structure'])) {
            return;
        }

        if (!$input->model->supports(Capability::STRUCTURED_OUTPUT)) {
            throw MissingModelSupportException::forStructuredOutput($input->model::class);
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

        if ($output->response instanceof ObjectResponse) {
            return;
        }

        if (!isset($options['response_format'])) {
            return;
        }

        if (!isset($this->outputStructure)) {
            $output->response = new ObjectResponse(json_decode($output->response->getContent(), true));

            return;
        }

        $output->response = new ObjectResponse(
            $this->serializer->deserialize($output->response->getContent(), $this->outputStructure, 'json')
        );
    }
}
