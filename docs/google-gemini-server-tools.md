# Google Gemini Server Tools

Server tools are built-in capabilities provided by Google Gemini that allow the model to perform specific actions without requiring custom tool implementations. These tools run on Google's servers and provide access to external data and execution environments.

## Overview

Google Gemini provides several server-side tools that can be enabled when calling the model:

- **URL Context** - Fetches and analyzes content from URLs
- **Google Search** - Performs web searches using Google
- **Code Execution** - Executes code in a sandboxed environment

## Available Server Tools

### URL Context

The URL Context tool allows Gemini to fetch and analyze content from web pages. This is useful for:

- Analyzing current web content
- Extracting information from specific pages
- Understanding context from external sources

```php
<?php

$llm = new Gemini('gemini-2.5-pro-preview-03-25', [
    'server_tools' => [
        'url_context' => true
    ]
]);

$messages = new MessageBag(
    Message::ofUser('What was the 12 month Euribor rate a week ago based on https://www.euribor-rates.eu/en/current-euribor-rates/4/euribor-rate-12-months/')
);

$response = $chain->call($messages);
```

### Google Search

The Google Search tool enables the model to search the web and incorporate search results into its responses:

```php
<?php

$llm = new Gemini('gemini-2.5-pro-preview-03-25', [
    'server_tools' => [
        'google_search' => true
    ]
]);

$messages = new MessageBag(
    Message::ofUser('What are the latest developments in quantum computing?')
);

$response = $chain->call($messages);
```

### Code Execution

The Code Execution tool provides a sandboxed environment for running code:

```php
<?php

$llm = new Gemini('gemini-2.5-pro-preview-03-25', [
    'server_tools' => [
        'code_execution' => true
    ]
]);

$messages = new MessageBag(
    Message::ofUser('Calculate the factorial of 20 and show me the code')
);

$response = $chain->call($messages);
```

## Using Multiple Server Tools

You can enable multiple server tools simultaneously:

```php
<?php

$llm = new Gemini('gemini-2.5-pro-preview-03-25', [
    'server_tools' => [
        'url_context' => true,
        'google_search' => true,
        'code_execution' => true
    ]
]);
```

## Advanced Configuration

### Server Tools with Parameters

For server tools that accept parameters, you can pass an array instead of `true`:

```php
<?php

$llm = new Gemini('gemini-2.5-pro-preview-03-25', [
    'server_tools' => [
        'url_context' => [
            // Future parameters can be added here
        ]
    ]
]);
```

### Combining with Custom Tools

Server tools can be used alongside custom tools from the toolbox:

```php
<?php

use PhpLlm\LlmChain\Chain\Toolbox\Tool\Clock;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;

$toolbox = Toolbox::create(new Clock());
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $llm);

$llm = new Gemini('gemini-2.5-pro-preview-03-25', [
    'server_tools' => [
        'url_context' => true
    ]
]);

// Both server tools and custom tools will be available
```

## Implementation Details

The server tools implementation works by:

1. Converting server tool configurations into the format expected by Google's API
2. For boolean `true` values, an empty `ArrayObject` is sent as required by the API
3. Server tools are added to the `tools` array in the API request
4. The `server_tools` option is separate from regular `tools` to prevent toolbox tools from being overwritten

## Best Practices

1. **Enable only needed tools** - Each enabled tool increases latency and token usage
2. **Consider rate limits** - Server tools may have usage limits
3. **Combine wisely** - Use server tools for external data and custom tools for application logic
4. **Handle failures gracefully** - Server tools may fail due to network issues or API limits

## Complete Example

```php
<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Clock;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Bridge\Google\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

// Initialize platform
$platform = PlatformFactory::create($_ENV['GOOGLE_API_KEY']);

// Configure model with server tools
$llm = new Gemini('gemini-2.5-pro-preview-03-25', [
    'server_tools' => [
        'url_context' => true,
        'google_search' => true
    ],
    'temperature' => 0.7
]);

// Optional: Add custom tools
$toolbox = Toolbox::create(new Clock());
$processor = new ChainProcessor($toolbox);

// Create chain
$chain = new Chain($platform, $llm);

// Use with URL context
$messages = new MessageBag(
    Message::ofUser(
        'Compare the current EUR/USD exchange rate from https://www.xe.com with historical rates. 
         What has been the trend over the past month?'
    )
);

$response = $chain->call($messages);
echo $response->getContent() . PHP_EOL;
```

## Limitations

- Server tools are only available for Google Gemini models
- API key must have appropriate permissions
- Server tools may have usage quotas
- Response times may vary based on the complexity of server tool operations
- Not all Gemini model versions support all server tools