<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Tool;

use PhpLlm\LlmChain\ChainInterface;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Response\TextResponse;

final readonly class Chain
{
    public function __construct(
        private ChainInterface $chain,
    ) {
    }

    /**
     * @param string $message the message to pass to the chain
     */
    public function __invoke(string $message): string
    {
        $response = $this->chain->call(new MessageBag(Message::ofUser($message)));

        assert($response instanceof TextResponse);

        return $response->getContent();
    }
}
