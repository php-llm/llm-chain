<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\StructuredOutput\Data;

final class User
{
    public int $id;
    public string $name;
    public \DateTimeInterface $createdAt;
    public bool $isActive;
}
