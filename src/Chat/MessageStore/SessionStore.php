<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chat\MessageStore;

use PhpLlm\LlmChain\Chat\MessageStoreInterface;
use PhpLlm\LlmChain\Model\Message\MessageBag;
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

    public function save(MessageBag $messages): void
    {
        $this->session->set($this->sessionKey, $messages);
    }

    public function load(): MessageBag
    {
        return $this->session->get($this->sessionKey, new MessageBag());
    }

    public function clear(): void
    {
        $this->session->remove($this->sessionKey);
    }
}
