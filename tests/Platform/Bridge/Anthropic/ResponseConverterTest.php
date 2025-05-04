<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Anthropic;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\ResponseConverter;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Response\ToolCallResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(ResponseConverter::class)]
#[Small]
#[UsesClass(ToolCall::class)]
#[UsesClass(ToolCallResponse::class)]
final class ResponseConverterTest extends TestCase
{
    public function testConvertThrowsExceptionWhenContentIsToolUseAndLacksText(): void
    {
        $httpClient = new MockHttpClient(new JsonMockResponse([
            'content' => [
                [
                    'type' => 'tool_use',
                    'id' => 'toolu_01UM4PcTjC1UDiorSXVHSVFM',
                    'name' => 'xxx_tool',
                    'input' => ['action' => 'get_data'],
                ],
            ],
        ]));
        $httpResponse = $httpClient->request('POST', 'https://api.anthropic.com/v1/messages');
        $handler = new ResponseConverter();

        $response = $handler->convert($httpResponse);
        self::assertInstanceOf(ToolCallResponse::class, $response);
        self::assertCount(1, $response->getContent());
        self::assertSame('toolu_01UM4PcTjC1UDiorSXVHSVFM', $response->getContent()[0]->id);
        self::assertSame('xxx_tool', $response->getContent()[0]->name);
        self::assertSame(['action' => 'get_data'], $response->getContent()[0]->arguments);
    }
}
