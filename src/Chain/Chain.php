<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Chain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Chain\Exception\MissingModelSupportException;
use PhpLlm\LlmChain\Chain\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\PlatformInterface;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class Chain implements ChainInterface
{
    /**
     * @var InputProcessorInterface[]
     */
    private array $inputProcessors;

    /**
     * @var OutputProcessorInterface[]
     */
    private array $outputProcessors;

    /**
     * @param InputProcessorInterface[]  $inputProcessors
     * @param OutputProcessorInterface[] $outputProcessors
     */
    public function __construct(
        private PlatformInterface $platform,
        private Model $model,
        iterable $inputProcessors = [],
        iterable $outputProcessors = [],
        private LoggerInterface $logger = new NullLogger(),
    ) {
        $this->inputProcessors = $this->initializeProcessors($inputProcessors, InputProcessorInterface::class);
        $this->outputProcessors = $this->initializeProcessors($outputProcessors, OutputProcessorInterface::class);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBagInterface $messages, array $options = []): ResponseInterface
    {
        $input = new Input($this->model, $messages, $options);
        array_map(fn (InputProcessorInterface $processor) => $processor->processInput($input), $this->inputProcessors);

        $model = $input->model;
        $messages = $input->messages;
        $options = $input->getOptions();

        if ($messages->containsAudio() && !$model->supports(Capability::INPUT_AUDIO)) {
            throw MissingModelSupportException::forAudioInput($model::class);
        }

        if ($messages->containsImage() && !$model->supports(Capability::INPUT_IMAGE)) {
            throw MissingModelSupportException::forImageInput($model::class);
        }

        try {
            $response = $this->platform->request($model, $messages, $options)->getResponse();
        } catch (ClientExceptionInterface $e) {
            $message = $e->getMessage();
            $content = $e->getResponse()->toArray(false);

            $this->logger->debug($message, $content);

            throw new InvalidArgumentException('' === $message ? 'Invalid request to model or platform' : $message, previous: $e);
        } catch (HttpExceptionInterface $e) {
            throw new RuntimeException('Failed to request model', previous: $e);
        }

        $output = new Output($model, $response, $messages, $options);
        array_map(fn (OutputProcessorInterface $processor) => $processor->processOutput($output), $this->outputProcessors);

        return $output->response;
    }

    /**
     * @param InputProcessorInterface[]|OutputProcessorInterface[] $processors
     * @param class-string                                         $interface
     *
     * @return InputProcessorInterface[]|OutputProcessorInterface[]
     */
    private function initializeProcessors(iterable $processors, string $interface): array
    {
        foreach ($processors as $processor) {
            if (!$processor instanceof $interface) {
                throw new InvalidArgumentException(\sprintf('Processor %s must implement %s interface.', $processor::class, $interface));
            }

            if ($processor instanceof ChainAwareInterface) {
                $processor->setChain($this);
            }
        }

        return $processors instanceof \Traversable ? iterator_to_array($processors) : $processors;
    }
}
