<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\Toolbox\Event\ToolCallArgumentsResolved;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolExecutionException;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\ReflectionToolFactory;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class Toolbox implements ToolboxInterface
{
    /**
     * List of executable tools.
     *
     * @var list<mixed>
     */
    private readonly array $tools;

    /**
     * List of tool metadata objects.
     *
     * @var Tool[]
     */
    private array $map;

    /**
     * @param iterable<mixed> $tools
     */
    public function __construct(
        private readonly ToolFactoryInterface $toolFactory,
        iterable $tools,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly ToolCallArgumentResolverInterface $argumentResolver = new ToolCallArgumentResolver(),
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
        $this->tools = $tools instanceof \Traversable ? iterator_to_array($tools) : $tools;
    }

    public static function create(object ...$tools): self
    {
        return new self(new ReflectionToolFactory(), $tools);
    }

    public function getTools(): array
    {
        if (isset($this->map)) {
            return $this->map;
        }

        $map = [];
        foreach ($this->tools as $tool) {
            foreach ($this->toolFactory->getTool($tool::class) as $metadata) {
                $map[] = $metadata;
            }
        }

        return $this->map = $map;
    }

    public function execute(ToolCall $toolCall): mixed
    {
        $metadata = $this->getMetadata($toolCall);
        $tool = $this->getExecutable($metadata);

        try {
            $this->logger->debug(\sprintf('Executing tool "%s".', $toolCall->name), $toolCall->arguments);

            $arguments = $this->argumentResolver->resolveArguments($tool, $metadata, $toolCall);
            $this->eventDispatcher?->dispatch(new ToolCallArgumentsResolved($tool, $metadata, $arguments));

            $result = $tool->{$metadata->reference->method}(...$arguments);
        } catch (\Throwable $e) {
            $this->logger->warning(\sprintf('Failed to execute tool "%s".', $toolCall->name), ['exception' => $e]);
            throw ToolExecutionException::executionFailed($toolCall, $e);
        }

        return $result;
    }

    private function getMetadata(ToolCall $toolCall): Tool
    {
        foreach ($this->getTools() as $metadata) {
            if ($metadata->name === $toolCall->name) {
                return $metadata;
            }
        }

        throw ToolNotFoundException::notFoundForToolCall($toolCall);
    }

    private function getExecutable(Tool $metadata): object
    {
        foreach ($this->tools as $tool) {
            if ($tool instanceof $metadata->reference->class) {
                return $tool;
            }
        }

        throw ToolNotFoundException::notFoundForReference($metadata->reference);
    }
}
