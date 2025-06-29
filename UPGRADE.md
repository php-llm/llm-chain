# Upgrade Guide

## Breaking Changes

### Capability Constant Rename

The capability constant `Capability::OUTPUT_STRUCTURED` has been renamed to `Capability::STRUCTURED_OUTPUT` to follow a more consistent naming pattern.

Additionally, the constant value has been changed from `'output-structured'` to `'structured-output'`.

#### Before
```php
use PhpLlm\LlmChain\Platform\Capability;

// Constant name
Capability::OUTPUT_STRUCTURED

// Constant value
'output-structured'
```

#### After
```php
use PhpLlm\LlmChain\Platform\Capability;

// Constant name
Capability::STRUCTURED_OUTPUT

// Constant value
'structured-output'
```

#### Migration

Update all references in your code from `Capability::OUTPUT_STRUCTURED` to `Capability::STRUCTURED_OUTPUT`.

If you're storing or comparing capability strings directly, also update from `'output-structured'` to `'structured-output'`.