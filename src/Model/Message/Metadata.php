<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

/**
 * @template-extends \ArrayObject<string, mixed>
 */
class Metadata extends \ArrayObject
{
    public function __construct(
        array $array = [],
        Uuid $id = new UuidV4(),
        \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        if (!isset($array['created_at'])) {
            $array['created_at'] = $createdAt;
        }

        if (!isset($array['id'])) {
            $array['id'] = $id;
        }

        parent::__construct($array);
    }
}
