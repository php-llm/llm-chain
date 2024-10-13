<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

final readonly class ToolCall implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $arguments = [],
    ) {
    }

    /**
     * @return array{
     *     id: string,
     *     type: 'function',
     *     function: array{
     *         name: string,
     *         arguments: string
     *     }
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => 'function',
            'function' => [
                'name' => $this->name,
                'arguments' => json_encode($this->arguments),
            ],
        ];
    }
}
