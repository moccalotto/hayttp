# Hayttp

[![Build Status](https://travis-ci.org/moccalotto/hayttp.svg)](https://travis-ci.org/moccalotto/hayttp)

Validate variables using a fluent syntax.

## Installation

To add this package as a local, per-project dependency to your project, simply add a dependency on
 `moccalotto/hayttp` to your project's `composer.json` file like so:

```json
{
    "require": {
        "moccalotto/hayttp": "~0.3"
    }
}
```

Alternatively execute the following command in your shell.

```bash
composer require moccalotto/hayttp
```

## Usage

```php

$response = Hayttp::get('example.org')->send();

```
