<?php

namespace PhpLlm\LlmChain\Chain\Toolbox\Security\Attribute;

use PhpLlm\LlmChain\Platform\Tool\Tool;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Checks if user has permission to access to some tool resource using security roles and voters.
 *
 * @see https://symfony.com/doc/current/security.html#roles
 *
 * @author Valtteri R <valtzu@gmail.com>
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class IsGrantedTool
{
    /**
     * @param string|Expression                                                             $attribute     The attribute that will be checked against a given authentication token and optional subject
     * @param array<mixed>|string|Expression|\Closure(array<string,mixed>, Tool):mixed|null $subject       An optional subject - e.g. the current object being voted on
     * @param string|null                                                                   $message       A custom message when access is not granted
     * @param int|null                                                                      $exceptionCode If set, will add the exception code to thrown exception
     */
    public function __construct(
        public string|Expression $attribute,
        public array|string|Expression|\Closure|null $subject = null,
        public ?string $message = null,
        public ?int $exceptionCode = null,
    ) {
    }
}
