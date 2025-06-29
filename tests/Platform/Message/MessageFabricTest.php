<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Message;

use PhpLlm\LlmChain\Platform\Fabric\Exception\PatternNotFoundException;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
#[Small]
final class MessageFabricTest extends TestCase
{
    private string $testPatternsPath;

    protected function setUp(): void
    {
        $this->testPatternsPath = sys_get_temp_dir().'/fabric-test-'.uniqid();
        mkdir($this->testPatternsPath.'/test_pattern', 0777, true);
        file_put_contents(
            $this->testPatternsPath.'/test_pattern/system.md',
            '# Test Fabric Pattern'
        );
    }

    protected function tearDown(): void
    {
        unlink($this->testPatternsPath.'/test_pattern/system.md');
        rmdir($this->testPatternsPath.'/test_pattern');
        rmdir($this->testPatternsPath);
    }

    #[Test]
    public function fabricMethodCreatesSystemMessage(): void
    {
        $message = Message::fabric('test_pattern', $this->testPatternsPath);

        self::assertInstanceOf(SystemMessage::class, $message);
        self::assertSame('# Test Fabric Pattern', $message->content);
    }

    #[Test]
    public function fabricMethodThrowsExceptionForNonExistingPattern(): void
    {
        $this->expectException(PatternNotFoundException::class);
        $this->expectExceptionMessage('Pattern "non_existing" not found');

        Message::fabric('non_existing', $this->testPatternsPath);
    }
}
