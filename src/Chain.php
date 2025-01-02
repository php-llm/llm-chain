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
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\LanguageModel;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

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
        private LoggerInterface $logger = new NullLogger(),
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

        if ($messages->containsImage() && !$llm->supportsImageInput()) {
            throw MissingModelSupport::forImageInput($llm::class);
        }

        try {
            $response = $this->platform->request($llm, $messages, $options);

            if ($response instanceof AsyncResponse) {
                $response = $response->unwrap();
            }
        } catch (ClientExceptionInterface $e) {
            $message = $e->getMessage();
            $content = $e->getResponse()->toArray(false);

            $this->logger->debug($message, $content);

            throw new InvalidArgumentException('' === $message ? 'Invalid request to model or platform' : $message, 0, $e);
        } catch (HttpExceptionInterface $e) {
            throw new RuntimeException('Failed to request model', 0, $e);
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
