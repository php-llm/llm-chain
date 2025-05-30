<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface;
use PhpLlm\LlmChain\Platform\Platform;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class PlatformTestHandler implements ModelClientInterface, ResponseConverterInterface
{
    public int $createCalls = 0;

    public function __construct(
        private readonly ?ResponseInterface $create = null,
    ) {
    }

    public static function createPlatform(?ResponseInterface $create = null): Platform
    {
        $handler = new self($create);

        return new Platform([$handler], [$handler]);
    }

    public function supports(Model $model): bool
    {
        return true;
    }

    public function request(Model $model, array|string|object $payload, array $options = []): HttpResponse
    {
        ++$this->createCalls;

        return new MockResponse();
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        return $this->create ?? new VectorResponse(new Vector([1, 2, 3]));
    }
}
