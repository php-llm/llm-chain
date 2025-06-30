<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Chat\MessageStore;

use PhpLlm\LlmChain\Chain\Chat\MessageStoreInterface;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class SessionStore implements MessageStoreInterface
{
    private SessionInterface $session;

    public function __construct(
        RequestStack $requestStack,
        private string $sessionKey = 'messages',
    ) {
        $this->session = $requestStack->getSession();
    }

    public function save(MessageBagInterface $messages): void
    {
        $this->session->set($this->sessionKey, $messages);
    }

    public function load(): MessageBagInterface
    {
        return $this->session->get($this->sessionKey, new MessageBag());
    }

    public function clear(): void
    {
        $this->session->remove($this->sessionKey);
    }
}
