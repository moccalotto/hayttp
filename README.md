# Hayttp
[![Build Status](https://travis-ci.org/moccalotto/hayttp.svg)](https://travis-ci.org/moccalotto/hayttp)

HTTP request made easy!

* Lightweight, fast and small footprint.
* Syntacticly sweet, easy and intuitive.
* Short-hands to the 7 RESTful HTTP methods.
* Real file (and blob) uploads.
* Basic Auth.
* Immutable.
* Awesome advanced options:
  * Choose between CURL and php native http streams.
  * Create your own http transport engine (for instance a guzzle wrapper).
  * Choose ssl/tls scheme and version.
  * Create custom payloads.

## Installation

To add this package as a local, per-project dependency to your project, simply add a dependency on
 `moccalotto/hayttp` to your project's `composer.json` file like so:

```json
{
    "require": {
        "moccalotto/hayttp": "~0.8"
    }
}
```

Alternatively execute the following command in your shell.

```bash
composer require moccalotto/hayttp
```

## Usage

```php
use Moccalotto\Hayttp\Hayttp;

$response = Hayttp::get($url)->send();
```

### REST Methods
Hayttp is essentially a factory that can create and initialize `Request` objects.
It has methods for each of the 7 RESTful HTTP methods.

Making GET Requests:

```php
$response = Hayttp::get($url)->send();
```

A more interesting POST example.

```php
$response = Hayttp::post($url)
    ->expectsJson()
    ->sendsJson([
        'this' => 'associative',
        'array' => 'will',
        'be' => 'converted',
        'converted' => 'to',
        'a' => 'json',
        'object' => 'weee!',
    ])
    ->send();
```

A DELETE request that expects an XML body in the response.

```php
$response = Hayttp::delete($url)
    ->expectsXml()
    ->send();
```


### Decode responses

You can parse/unserialize the response payloads into native php data structures.
Hayttp currently supports json, xml and rfc3986.

Below is an example of how parse a response as json.
Json objects are converted to `stdClass` objects, and json arrays are converted to php arrays:

```php
$stdClass = Hayttp::get($url)
    ->expectsJson()
    ->send()
    ->jsonDecoded();
```

Here is an example of a response decoded into a `SimpleXmlElement`:

```php
$simpleXmlElement = Hayttp::get($url)
    ->expectsXml()
    ->send()
    ->xmlDecoded();
```

Decode a url-encoded string into an associative array:

```php
$array = Hayttp::get($url)
    ->send()
    ->urlDecoded();
```

Decode the respose, inferring the data type from the Content-Type header:

```php
$variable = Hayttp::get($url)->send()->decoded();
```
