<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Event;

use PhpLlm\LlmChain\Model\Message\MessageBag;

final class ChainIteractionsFinished
{
    public function __construct(public MessageBag $messageBag)
    {
    }
}
