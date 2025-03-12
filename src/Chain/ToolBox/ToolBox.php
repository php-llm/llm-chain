<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolExecutionException;
use PhpLlm\LlmChain\Chain\ToolBox\Exception\ToolNotFoundException;
use PhpLlm\LlmChain\Chain\ToolBox\MetadataFactory\ReflectionFactory;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ToolBox implements ToolBoxInterface
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
        foreach ($this->tools as $tool) {
            foreach ($this->metadataFactory->getMetadata($tool) as $metadata) {
                if ($metadata->name !== $toolCall->name) {
                    continue;
                }

                try {
                    $this->logger->debug(sprintf('Executing tool "%s".', $metadata->name), $toolCall->arguments);
                    $result = $tool->{$metadata->method}(...$toolCall->arguments);
                } catch (\Throwable $e) {
                    $this->logger->warning(sprintf('Failed to execute tool "%s".', $metadata->name), ['exception' => $e]);
                    throw ToolExecutionException::executionFailed($toolCall, $e);
                }

                return $result;
            }
        }

        throw ToolNotFoundException::notFoundForToolCall($toolCall);
    }
}
