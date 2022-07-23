<?php

declare(strict_types=1);

namespace Guennichi\Psr7RequestFactory;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

interface RequestFactoryInterface
{
    public function fromServerRequest(UriInterface|string $uri, ServerRequestInterface $serverRequest): RequestInterface;
}
