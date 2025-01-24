<?php

declare(strict_types=1);

namespace Fixture\StructuredOutput;

final class UserWithAtParamAnnotation
{
    public function __construct(
        public int $id,
        /**
         * @param string The name of the user in lowercase
         */
        public string $name,
        public \DateTimeInterface $createdAt,
        public bool $isActive,
        public ?int $age = null,
    ) {
    }
}
