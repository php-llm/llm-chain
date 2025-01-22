<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\StructuredOutput;

final class User
{
    public int $id;
    /**
     * @var string The name of the user in lowercase
     */
    public string $name;
    public \DateTimeInterface $createdAt;
    public bool $isActive;
    public ?int $age = null;
}
