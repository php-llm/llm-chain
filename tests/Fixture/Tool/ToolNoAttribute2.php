<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

final class ToolNoAttribute2
{
    /**
     * @param int $id     the ID of the product
     * @param int $amount the number of products
     */
    public function buy(int $id, int $amount): string
    {
        return \sprintf('You bought %d of product %d.', $amount, $id);
    }

    /**
     * @param string $orderId the ID of the order
     */
    public function cancel(string $orderId): string
    {
        return \sprintf('You canceled order %s.', $orderId);
    }
}
