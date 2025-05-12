<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\StructuredOutput;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\StructuredOutput\ChainProcessor;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\Capability;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\Choice;
use PhpLlm\LlmChain\Model\Response\StructuredResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Tests\Double\ConfigurableResponseFormatFactory;
use PhpLlm\LlmChain\Tests\Fixture\SomeStructure;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\MathReasoning;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\Step;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(ChainProcessor::class)]
#[UsesClass(Input::class)]
#[UsesClass(Output::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(Choice::class)]
#[UsesClass(MissingModelSupport::class)]
#[UsesClass(TextResponse::class)]
#[UsesClass(StructuredResponse::class)]
final class ChainProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithOutputStructure(): void
    {
        $chainProcessor = new ChainProcessor(new ConfigurableResponseFormatFactory(['some' => 'format']));

        $model = new Model('gpt-4', [Capability::OUTPUT_STRUCTURED]);
        $input = new Input($model, new MessageBag(), ['output_structure' => 'SomeStructure']);

        $chainProcessor->processInput($input);

        self::assertSame(['response_format' => ['some' => 'format']], $input->getOptions());
    }

    #[Test]
    public function processInputWithoutOutputStructure(): void
    {
        $chainProcessor = new ChainProcessor(new ConfigurableResponseFormatFactory());

        $model = new Model('gpt-4', [Capability::OUTPUT_STRUCTURED]);
        $input = new Input($model, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame([], $input->getOptions());
    }

    #[Test]
    public function processInputThrowsExceptionWhenLlmDoesNotSupportStructuredOutput(): void
    {
        self::expectException(MissingModelSupport::class);

        $chainProcessor = new ChainProcessor(new ConfigurableResponseFormatFactory());

        $model = new Model('gpt-3');
        $input = new Input($model, new MessageBag(), ['output_structure' => 'SomeStructure']);

        $chainProcessor->processInput($input);
    }

    #[Test]
    public function processOutputWithResponseFormat(): void
    {
        $chainProcessor = new ChainProcessor(new ConfigurableResponseFormatFactory(['some' => 'format']));

        $model = new Model('gpt-4', [Capability::OUTPUT_STRUCTURED]);
        $options = ['output_structure' => SomeStructure::class];
        $input = new Input($model, new MessageBag(), $options);
        $chainProcessor->processInput($input);

        $response = new TextResponse('{"some": "data"}');

        $output = new Output($model, $response, new MessageBag(), $input->getOptions());

        $chainProcessor->processOutput($output);

        self::assertInstanceOf(StructuredResponse::class, $output->response);
        self::assertInstanceOf(SomeStructure::class, $output->response->getContent());
        self::assertSame('data', $output->response->getContent()->some);
    }

    #[Test]
    public function processOutputWithComplexResponseFormat(): void
    {
        $chainProcessor = new ChainProcessor(new ConfigurableResponseFormatFactory(['some' => 'format']));

        $model = new Model('gpt-4', [Capability::OUTPUT_STRUCTURED]);
        $options = ['output_structure' => MathReasoning::class];
        $input = new Input($model, new MessageBag(), $options);
        $chainProcessor->processInput($input);

        $response = new TextResponse(<<<JSON
            {
                "steps": [
                    {
                        "explanation": "We want to isolate the term with x. First, let's subtract 7 from both sides of the equation.",
                        "output": "8x + 7 - 7 = -23 - 7"
                    },
                    {
                        "explanation": "This simplifies to 8x = -30.",
                        "output": "8x = -30"
                    },
                    {
                        "explanation": "Next, to solve for x, we need to divide both sides of the equation by 8.",
                        "output": "x = -30 / 8"
                    },
                    {
                        "explanation": "Now we simplify -30 / 8 to its simplest form.",
                        "output": "x = -15 / 4"
                    },
                    {
                        "explanation": "Dividing both the numerator and the denominator by their greatest common divisor, we finalize our solution.",
                        "output": "x = -3.75"
                    }
                ],
                "finalAnswer": "x = -3.75"
            }
            JSON);

        $output = new Output($model, $response, new MessageBag(), $input->getOptions());

        $chainProcessor->processOutput($output);

        self::assertInstanceOf(StructuredResponse::class, $output->response);
        self::assertInstanceOf(MathReasoning::class, $structure = $output->response->getContent());
        self::assertCount(5, $structure->steps);
        self::assertInstanceOf(Step::class, $structure->steps[0]);
        self::assertInstanceOf(Step::class, $structure->steps[1]);
        self::assertInstanceOf(Step::class, $structure->steps[2]);
        self::assertInstanceOf(Step::class, $structure->steps[3]);
        self::assertInstanceOf(Step::class, $structure->steps[4]);
        self::assertSame('x = -3.75', $structure->finalAnswer);
    }

    #[Test]
    public function processOutputWithoutResponseFormat(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory();
        $serializer = self::createMock(SerializerInterface::class);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $model = self::createMock(Model::class);
        $response = new TextResponse('');

        $output = new Output($model, $response, new MessageBag(), []);

        $chainProcessor->processOutput($output);

        self::assertSame($response, $output->response);
    }
}
