# PSR-7 Request Factory

Factory class to create simple request objects from PSR-7 server requests.

## Installation

```bash
composer require guennichi/psr7-request-factory
```

## Usage

### Create request object

This library can be used with [`Nyholm/psr7`](https://github.com/Nyholm/psr7) / [`Nyholm/psr7-server`](https://github.com/Nyholm/psr7-server)
or any other PSR-7 implementation to transform server requests:

```php
$psr17Factory = new Nyholm\Psr7\Factory\Psr17Factory();

$serverRequestFactory = new Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory, // StreamFactory
);

// Psr\Http\Message\ServerRequestInterface instance
$serverRequest = $serverRequestFactory->fromGlobals();

$requestFactory = new Guennichi\Psr7RequestFactory\RequestFactory(
    $psr17Factory,
    $psr17Factory,
    new Http\Message\MultipartStream\MultipartStreamBuilder($psr17Factory),
);

// Psr\Http\Message\RequestInterface instance
$request = $requestFactory->fromServerRequest('/example', $serverRequest);
```
