<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Chain\ChainAwareProcessor;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\LanguageModel;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;

final readonly class Chain implements ChainInterface
{
    /**
     * @var InputProcessor[]
     */
    private array $inputProcessor;

    /**
     * @var OutputProcessor[]
     */
    private array $outputProcessor;

    /**
     * @param InputProcessor[]  $inputProcessor
     * @param OutputProcessor[] $outputProcessor
     */
    public function __construct(
        private PlatformInterface $platform,
        private LanguageModel $llm,
        iterable $inputProcessor = [],
        iterable $outputProcessor = [],
    ) {
        $this->inputProcessor = $this->initializeProcessors($inputProcessor, InputProcessor::class);
        $this->outputProcessor = $this->initializeProcessors($outputProcessor, OutputProcessor::class);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBagInterface $messages, array $options = []): ResponseInterface
    {
        $input = new Input($this->llm, $messages, $options);
        array_map(fn (InputProcessor $processor) => $processor->processInput($input), $this->inputProcessor);

        $llm = $input->llm;
        $messages = $input->messages;
        $options = $input->getOptions();

        if ($messages->containsAudio() && !$llm->supportsAudioInput()) {
            throw MissingModelSupport::forAudioInput($llm::class);
        }

        if ($messages->containsImage() && !$llm->supportsImageInput()) {
            throw MissingModelSupport::forImageInput($llm::class);
        }

        $response = $this->platform->request($llm, $messages, $options);

        if ($response instanceof AsyncResponse) {
            $response = $response->unwrap();
        }

        $output = new Output($llm, $response, $messages, $options);
        array_map(fn (OutputProcessor $processor) => $processor->processOutput($output), $this->outputProcessor);

        return $output->response;
    }

    /**
     * @param InputProcessor[]|OutputProcessor[] $processors
     *
     * @return InputProcessor[]|OutputProcessor[]
     */
    private function initializeProcessors(iterable $processors, string $interface): array
    {
        foreach ($processors as $processor) {
            if (!$processor instanceof $interface) {
                throw new InvalidArgumentException(sprintf('Processor %s must implement %s interface.', $processor::class, $interface));
            }

            if ($processor instanceof ChainAwareProcessor) {
                $processor->setChain($this);
            }
        }

        return $processors instanceof \Traversable ? iterator_to_array($processors) : $processors;
    }
}
