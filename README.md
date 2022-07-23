# PSR-7 Request Creator

Factory class to create simple request objects from PSR-7 server requests.

## Installation

```bash
composer require guennichi/psr7-request-creator
```

## Usage

### Create request object

This library can be used with [`Nyholm/psr7`](https://github.com/Nyholm/psr7) / [`Nyholm/psr7-server`](https://github.com/Nyholm/psr7-server)
or any other PSR-7 implementation to transform server requests:

```php
$psr17Factory = new Nyholm\Psr7\Factory\Psr17Factory();

$serverRequestCreator = new Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory, // StreamFactory
);

// Psr\Http\Message\ServerRequestInterface instance
$serverRequest = $serverRequestCreator->fromGlobals();

$requestCreator = new Guennichi\Psr7RequestFactory\RequestCreator(
    $psr17Factory, // RequestFactory
    $psr17Factory, // StreamFactory
    new Http\Message\MultipartStream\MultipartStreamBuilder($psr17Factory),
);

// Psr\Http\Message\RequestInterface instance
$request = $requestCreator->fromServerRequest('/example', $serverRequest);
```
