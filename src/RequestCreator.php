<?php

declare(strict_types=1);

namespace Guennichi\Psr7RequestCreator;

use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

final class RequestCreator implements RequestCreatorInterface
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly MultipartStreamBuilder $multipartStreamBuilder,
    ) {
    }

    public function fromServerRequest(UriInterface|string $uri, ServerRequestInterface $serverRequest): RequestInterface
    {
        $request = $this->requestFactory->createRequest($serverRequest->getMethod(), $uri);

        foreach ($serverRequest->getHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $serverRequestBody = $serverRequest->getBody();
        if ($serverRequestBody->getSize()) {
            return $request->withBody($serverRequestBody);
        }

        $postParams = \is_array($parsedBody = $serverRequest->getParsedBody()) ? Utils::flatten($parsedBody) : [];
        if (str_starts_with($serverRequest->getHeaderLine('content-type'), 'multipart/form-data')) {
            // Reset and clear all previous stored data
            $this->multipartStreamBuilder->reset();

            foreach ([...$postParams, ...Utils::flatten($serverRequest->getUploadedFiles())] as $name => $value) {
                if ($value instanceof UploadedFileInterface) {
                    $this->multipartStreamBuilder->addResource($name, $value->getStream(), ['filename' => $value->getClientFilename()]);
                } else {
                    $this->multipartStreamBuilder->addResource($name, match (true) {
                        \is_resource($value) => $this->streamFactory->createStreamFromResource($value),
                        \is_string($value) || $value instanceof StreamInterface => $value,
                        default => throw new \RuntimeException(sprintf('The parameter "%s" should be "%s", "%s" given.', $name, implode('|', ['resource', 'string']), get_debug_type($value)))
                    });
                }
            }

            return $request->withBody($this->multipartStreamBuilder->build())
                // New multipart boundary generated, we need to override the default content-type header
                ->withHeader('Content-Type', 'multipart/form-data; boundary="' . $this->multipartStreamBuilder->getBoundary() . '"');
        }

        return $request->withBody($this->streamFactory->createStream(http_build_query($postParams)));
    }
}
