<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Response;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Model\Response\Choice;
use PhpLlm\LlmChain\Model\Response\ChoiceResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChoiceResponse::class)]
#[UsesClass(Choice::class)]
#[Small]
final class ChoiceResponseTest extends TestCase
{
    #[Test]
    public function choiceResponseCreation(): void
    {
        $choice1 = new Choice('choice1');
        $choice2 = new Choice(null);
        $choice3 = new Choice('choice3');
        $response = new ChoiceResponse($choice1, $choice2, $choice3);

        self::assertCount(3, $response->getContent());
        self::assertSame('choice1', $response->getContent()[0]->getContent());
        self::assertNull($response->getContent()[1]->getContent());
        self::assertSame('choice3', $response->getContent()[2]->getContent());
    }

    #[Test]
    public function choiceResponseWithNoChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must have at least one choice.');

        new ChoiceResponse();
    }
}
