<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Message\Content\Text;

final readonly class UserMessage implements MessageInterface
{
    /**
     * @var list<ContentInterface>
     */
    public array $content;

    public function __construct(
        ContentInterface ...$content,
    ) {
        $this->content = $content;
    }

    public function getRole(): Role
    {
        return Role::User;
    }

    /**
     * @return array{
     *     role: Role::User,
     *     content: string|list<ContentInterface>
     * }
     */
    public function jsonSerialize(): array
    {
        $array = ['role' => Role::User];
        if (1 === count($this->content) && $this->content[0] instanceof Text) {
            $array['content'] = $this->content[0]->text;

            return $array;
        }

        $array['content'] = $this->content;

        return $array;
    }
}
