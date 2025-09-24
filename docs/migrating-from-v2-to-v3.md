# Migrating from V2 to V3

## Overview

Version 3 introduces several important changes:

- Stricter column and indexes definitions
- Enhanced query methods available through extending `StellarWP\Schema\Tables\Contracts\Table`

## Migration Steps

### 1. Update Method Visibility

For classes extending `StellarWP\Schema\Tables\Contracts\Table`:

- Convert the `get_definition` method from `protected` to `public`

If in your implementation you chose to directly implement the renamed interface `StellarWP\Schema\Tables\Contracts\Schema_Interface`, you will need to implement the new interface `StellarWP\Schema\Tables\Contracts\Table_Interface` instead.

We strongly recommend extending the provided abstract instead.

### 2. Implement Schema History

In your table class:

- Add the static method `get_schema_history`
- Optionally keep or remove the `get_definition` method

### 3. Define Schema History

The `get_schema_history` method must:

- Return an array of callables
- Each callable should return a `StellarWP\Schema\Tables\Contracts\Table_Schema_Interface` object
- Include at least one entry for your current schema version
