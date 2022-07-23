<?php

declare(strict_types=1);

namespace Tests\Guennichi\Psr7RequestFactory;

use Guennichi\Psr7RequestFactory\RequestFactory;
use Guennichi\Psr7RequestFactory\RequestFactoryInterface;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class RequestFactoryTest extends TestCase
{
    private Psr17Factory $psr17Factory;
    private RequestFactoryInterface $requestFactory;

    protected function setUp(): void
    {
        $this->psr17Factory = new Psr17Factory();

        $this->requestFactory = new RequestFactory(
            $this->psr17Factory,
            $this->psr17Factory,
            new MultipartStreamBuilder($this->psr17Factory),
        );
    }

    public function testRequestCreatedWithGivenUri(): void
    {
        $serverRequest = $this->psr17Factory->createServerRequest('GET', '/test');
        $request = $this->requestFactory->fromServerRequest('/custom', $serverRequest);

        $this->assertSame('/custom', $request->getUri()->getPath());
    }

    public function testRequestMethodUsesSameServerMethod(): void
    {
        $serverRequest = $this->psr17Factory->createServerRequest('PUT', '/test');
        $request = $this->requestFactory->fromServerRequest('/custom', $serverRequest);

        $this->assertSame('PUT', $request->getMethod());
    }

    public function testServerHeadersArePassedToRequest(): void
    {
        $serverRequest = $this->psr17Factory->createServerRequest('GET', '/test')
            ->withHeader('header-1', 'value-1')
            ->withHeader('header-2', 'value-2');

        $request = $this->requestFactory->fromServerRequest('/test', $serverRequest);

        $this->assertSame(['header-1' => ['value-1'], 'header-2' => ['value-2']], $request->getHeaders());
    }

    public function testRequestBodySameAsServerBodyIfExist(): void
    {
        $serverRequest = $this->psr17Factory->createServerRequest('POST', '/test')
            ->withBody($this->psr17Factory->createStream('{"message": "test"}'))
            ->withParsedBody(['foo' => 'bar']);

        $request = $this->requestFactory->fromServerRequest('/test', $serverRequest);

        $this->assertSame('{"message": "test"}', (string) $request->getBody());
    }

    public function testRequestPostParamsSameAsServerPostParamsWhenBodyIsEmpty(): void
    {
        $serverRequest = $this->psr17Factory->createServerRequest('POST', '/test')
            ->withBody($this->psr17Factory->createStream(''))
            ->withParsedBody(['foo' => 'bar']);

        $request = $this->requestFactory->fromServerRequest('/test', $serverRequest);

        $this->assertSame('foo=bar', (string) $request->getBody());
    }

    public function testRequestPostParamsAreFlattenServerPostParams(): void
    {
        $serverRequest = $this->psr17Factory->createServerRequest('POST', '/test')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withParsedBody(['a' => 'b', 'c' => ['d' => 'e']]);

        $request = $this->requestFactory->fromServerRequest('/test', $serverRequest);

        $this->assertSame('a=b&c%5Bd%5D=e', (string) $request->getBody());
    }

    public function testRequestMultipartFormDataIsSameAsServerWhenMultipartHeaderExists(): void
    {
        $serverRequest = $this->psr17Factory->createServerRequest('POST', '/test')
            ->withHeader('Content-Type', 'multipart/form-data')
            ->withParsedBody(['example' => 'testvalue']);

        $request = $this->requestFactory->fromServerRequest('/test', $serverRequest);

        $this->assertStringStartsWith('multipart/form-data', $request->getHeaderLine('Content-Type'));

        $requestBody = (string) $request->getBody();
        $this->assertIsInt(strpos($requestBody, 'Content-Disposition: form-data'));
        $this->assertIsInt(strpos($requestBody, 'name="example"'));
        $this->assertIsInt(strpos($requestBody, 'Content-Length: 9'));
        $this->assertIsInt(strpos($requestBody, 'testvalue'));
    }

    public function testRequestMultipartFormFileIsSameAsServerWhenMultipartHeaderExists(): void
    {
        $serverRequest = $this->psr17Factory->createServerRequest('POST', '/test')
            ->withHeader('Content-Type', 'multipart/form-data')
            ->withUploadedFiles([
                'images' => ['httplug' => $this->psr17Factory->createStreamFromFile(__DIR__ . '/Resources/httplug.png')],
            ]);

        $request = $this->requestFactory->fromServerRequest('/test', $serverRequest);

        $this->assertStringStartsWith('multipart/form-data', $request->getHeaderLine('Content-Type'));

        $requestBody = (string) $request->getBody();
        $this->assertIsInt(strpos($requestBody, 'Content-Disposition: form-data; name="images[httplug]"; filename="httplug.png"'));
        $this->assertIsInt(strpos($requestBody, 'Content-Type: image/png'));
    }
}
