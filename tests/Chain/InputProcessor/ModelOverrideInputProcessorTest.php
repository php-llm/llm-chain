<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\InputProcessor;

use PhpLlm\LlmChain\Chain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor\ModelOverrideInputProcessor;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ModelOverrideInputProcessor::class)]
#[UsesClass(GPT::class)]
#[UsesClass(Claude::class)]
#[UsesClass(Input::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(Embeddings::class)]
#[Small]
final class ModelOverrideInputProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithValidModelOption(): void
    {
        $gpt = new GPT();
        $claude = new Claude();
        $input = new Input($gpt, new MessageBag(), ['model' => $claude]);

        $processor = new ModelOverrideInputProcessor();
        $processor->processInput($input);

        self::assertSame($claude, $input->model);
    }

    #[Test]
    public function processInputWithoutModelOption(): void
    {
        $gpt = new GPT();
        $input = new Input($gpt, new MessageBag(), []);

        $processor = new ModelOverrideInputProcessor();
        $processor->processInput($input);

        self::assertSame($gpt, $input->model);
    }

    #[Test]
    public function processInputWithInvalidModelOption(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Option "model" must be an instance of PhpLlm\LlmChain\Platform\Model.');

        $gpt = new GPT();
        $model = new MessageBag();
        $input = new Input($gpt, new MessageBag(), ['model' => $model]);

        $processor = new ModelOverrideInputProcessor();
        $processor->processInput($input);
    }
}
