<?php

declare(strict_types=1);

namespace Guennichi\Psr7RequestCreator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

interface RequestCreatorInterface
{
    public function fromServerRequest(UriInterface|string $uri, ServerRequestInterface $serverRequest): RequestInterface;
}
