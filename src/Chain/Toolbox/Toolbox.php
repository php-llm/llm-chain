<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolExecutionException;
use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Chain\Toolbox\MetadataFactory\ReflectionFactory;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Toolbox implements ToolboxInterface
{
    /**
     * @var list<mixed>
     */
    private readonly array $tools;

    /**
     * @var Metadata[]
     */
    private array $map;

    /**
     * @param iterable<mixed> $tools
     */
    public function __construct(
        private readonly MetadataFactory $metadataFactory,
        iterable $tools,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
        $this->tools = $tools instanceof \Traversable ? iterator_to_array($tools) : $tools;
    }

    public static function create(object ...$tools): self
    {
        return new self(new ReflectionFactory(), $tools);
    }

    public function getMap(): array
    {
        if (isset($this->map)) {
            return $this->map;
        }

        $map = [];
        foreach ($this->tools as $tool) {
            foreach ($this->metadataFactory->getMetadata($tool::class) as $metadata) {
                $map[] = $metadata;
            }
        }

        return $this->map = $map;
    }

    public function execute(ToolCall $toolCall): mixed
    {
        $metadata = $this->getMetadata($toolCall);
        $tool = $this->getTool($metadata);

        try {
            $this->logger->debug(sprintf('Executing tool "%s".', $toolCall->name), $toolCall->arguments);
            $result = $tool->{$metadata->reference->method}(...$toolCall->arguments);
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Failed to execute tool "%s".', $toolCall->name), ['exception' => $e]);
            throw ToolExecutionException::executionFailed($toolCall, $e);
        }

        return $result;
    }

    private function getMetadata(ToolCall $toolCall): Metadata
    {
        foreach ($this->getMap() as $metadata) {
            if ($metadata->name === $toolCall->name) {
                return $metadata;
            }
        }

        throw ToolNotFoundException::notFoundForToolCall($toolCall);
    }

    private function getTool(Metadata $metadata): object
    {
        foreach ($this->tools as $tool) {
            if ($tool instanceof $metadata->reference->class) {
                return $tool;
            }
        }

        throw ToolNotFoundException::notFoundForReference($metadata->reference);
    }
}
