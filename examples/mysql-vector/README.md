# MySQL 9 Vector Store Integration for LLM Chain

This directory contains example files for using MySQL 9's native vector capabilities with LLM Chain.

## Overview

MySQL 9 introduces built-in vector support through the `VECTOR` data type and functions like `VECTOR_COSINE_DISTANCE` for similarity operations. This allows using MySQL as a vector store for RAG (Retrieval-Augmented Generation) applications without requiring a separate vector database service.

## Requirements

- PHP 8.2 or higher
- MySQL 9.0.0 or higher
- PDO PHP extension with MySQL driver
- llm-chain library

## Quick Start with Docker

1. Ensure you have Docker and Docker Compose installed
2. Start MySQL 9:
   ```bash
   docker-compose up -d
   ```
3. Wait for the MySQL server to be ready (check with `docker-compose logs -f`)
4. Configure your `.env` file:
   ```
   MYSQL_DSN=mysql:host=localhost;port=3306;dbname=llm_chain;charset=utf8mb4
   MYSQL_USERNAME=root
   MYSQL_PASSWORD=password
   OPENAI_API_KEY=sk-your-openai-key
   ```
5. Run the example:
   ```bash
   php ../store-mysql-similarity-search.php
   ```

## Manual Setup

If you're not using Docker, you'll need to:

1. Install MySQL 9
2. Create a database:
   ```sql
   CREATE DATABASE llm_chain;
   ```
3. The example will automatically create the necessary table with a VECTOR column

## How It Works

The MySQL 9 Store implementation:

1. Automatically creates a table with the necessary structure when first used
2. Converts vector data from JSON to MySQL's native VECTOR type during storage
3. Uses MySQL's `VECTOR_COSINE_DISTANCE` function for similarity search
4. Converts distance scores to similarity scores (1 - distance) for compatibility with other vector stores

## Vector Table Schema

The automatically created table has the following structure:

```sql
CREATE TABLE vector_documents (
    id VARCHAR(36) PRIMARY KEY,
    vector_data JSON NOT NULL,
    metadata JSON,
    VECTOR USING vector_data(1536) -- dimensions is configurable
);
```

## Advanced Configuration

You can customize the Store behavior through constructor parameters:

```php
$store = new Store(
    $pdo,                   // PDO connection
    'custom_table_name',    // Custom table name (default: vector_documents)
    'embedding_vector',     // Custom vector column name (default: vector_data)
    'document_metadata',    // Custom metadata column name (default: metadata)
    [],                     // Additional options
    768,                    // Vector dimensions (default: 1536 for OpenAI)
    5                       // Default query result limit (default: 3)
);
```

## Performance Considerations

For production use:
- Consider adding indexes based on your specific query patterns
- Monitor memory usage, especially with large vector collections
- Adjust MySQL server configuration for vector operations

## Further Reading

- [MySQL 9 Vector Documentation](https://dev.mysql.com/doc/refman/9.0/en/vector.html)
- [LLM Chain Documentation](https://github.com/php-llm/llm-chain)