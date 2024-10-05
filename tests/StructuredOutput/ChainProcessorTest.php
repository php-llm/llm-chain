<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\StructuredOutput;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\StructuredResponse;
use PhpLlm\LlmChain\Response\TextResponse;
use PhpLlm\LlmChain\StructuredOutput\ChainProcessor;
use PhpLlm\LlmChain\Tests\Double\ConfigurableResponseFormatFactory;
use PhpLlm\LlmChain\Tests\Fixture\SomeStructure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(ChainProcessor::class)]
#[UsesClass(Input::class)]
#[UsesClass(Output::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(Choice::class)]
#[UsesClass(MissingModelSupport::class)]
final class ChainProcessorTest extends TestCase
{
    #[Test]
    public function processInputWithOutputStructure(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory(['some' => 'format']);
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsStructuredOutput')->willReturn(true);

        $input = new Input($llm, new MessageBag(), ['output_structure' => 'SomeStructure']);

        $chainProcessor->processInput($input);

        self::assertSame(['response_format' => ['some' => 'format']], $input->getOptions());
    }

    #[Test]
    public function processInputWithoutOutputStructure(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory();
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $input = new Input($llm, new MessageBag(), []);

        $chainProcessor->processInput($input);

        self::assertSame([], $input->getOptions());
    }

    #[Test]
    public function processInputThrowsExceptionWhenLlmDoesNotSupportStructuredOutput(): void
    {
        $this->expectException(MissingModelSupport::class);

        $responseFormatFactory = new ConfigurableResponseFormatFactory();
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsStructuredOutput')->willReturn(false);

        $input = new Input($llm, new MessageBag(), ['output_structure' => 'SomeStructure']);

        $chainProcessor->processInput($input);
    }

    #[Test]
    public function processOutputWithResponseFormat(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory(['some' => 'format']);
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $llm->method('supportsStructuredOutput')->willReturn(true);

        $options = ['output_structure' => SomeStructure::class];
        $input = new Input($llm, new MessageBag(), $options);
        $chainProcessor->processInput($input);

        $response = new TextResponse('{"some": "data"}');

        $output = new Output($llm, $response, new MessageBag(), $input->getOptions());

        $chainProcessor->processOutput($output);

        self::assertInstanceOf(StructuredResponse::class, $output->response);
        self::assertInstanceOf(SomeStructure::class, $output->response->getContent());
        self::assertSame('data', $output->response->getContent()->some);
    }

    #[Test]
    public function processOutputWithoutResponseFormat(): void
    {
        $responseFormatFactory = new ConfigurableResponseFormatFactory();
        $serializer = $this->createMock(SerializerInterface::class);
        $chainProcessor = new ChainProcessor($responseFormatFactory, $serializer);

        $llm = $this->createMock(LanguageModel::class);
        $response = new TextResponse('');

        $output = new Output($llm, $response, new MessageBag(), []);

        $chainProcessor->processOutput($output);

        self::assertSame($response, $output->response);
    }
}
