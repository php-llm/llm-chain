<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\Platform;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class PlatformTestHandler implements ModelClient, ResponseConverter
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

    public function supports(Model $model, object|array|string $input): bool
    {
        return true;
    }

    public function request(Model $model, object|array|string $input, array $options = []): HttpResponse
    {
        ++$this->createCalls;

        return new MockResponse();
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        return $this->create ?? new VectorResponse(new Vector([1, 2, 3]));
    }
}
