<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Response;

use PhpLlm\LlmChain\Response\StreamResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamResponse::class)]
#[Small]
final class StreamResponseTest extends TestCase
{


    #[Test]
    public function getContent(): void
    {
        $generator = (function () {
            yield 'data1';
            yield 'data2';
        })();

        $response = new StreamResponse($generator);
        $this->assertInstanceOf(\Generator::class, $response->getContent());

        $content = iterator_to_array($response->getContent());

        $this->assertCount(2, $content);
        $this->assertSame('data1', $content[0]);
        $this->assertSame('data2', $content[1]);
    }
}
