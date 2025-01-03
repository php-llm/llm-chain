<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain;

use PhpLlm\LlmChain\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\LlmOverrideInputProcessor;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LlmOverrideInputProcessor::class)]
#[UsesClass(GPT::class)]
#[UsesClass(Claude::class)]
#[UsesClass(Input::class)]
#[UsesClass(MessageBag::class)]
#[Small]
final class LlmOverrideInputProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithValidLlmOption(): void
    {
        $gpt = new GPT();
        $claude = new Claude();
        $input = new Input($gpt, new MessageBag(), ['llm' => $claude]);

        $processor = new LlmOverrideInputProcessor();
        $processor->processInput($input);

        self::assertSame($claude, $input->llm);
    }

    #[Test]
    public function processInputWithoutLlmOption(): void
    {
        $gpt = new GPT();
        $input = new Input($gpt, new MessageBag(), []);

        $processor = new LlmOverrideInputProcessor();
        $processor->processInput($input);

        self::assertSame($gpt, $input->llm);
    }

    #[Test]
    public function processInputWithInvalidLlmOption(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Option "llm" must be an instance of PhpLlm\LlmChain\Model\LanguageModel.');

        $gpt = new GPT();
        $model = new Embeddings();
        $input = new Input($gpt, new MessageBag(), ['llm' => $model]);

        $processor = new LlmOverrideInputProcessor();
        $processor->processInput($input);
    }
}
