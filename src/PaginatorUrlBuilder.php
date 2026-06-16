<?php

declare(strict_types=1);

namespace Componenta\Http;

use Componenta\Stdlib\PaginatorInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PaginatorUrlBuilder
{
    private int $limit;
    private string $offsetParam;
    private string $limitParam;

    public function __construct(
        private UriInterface $baseUri,
        string $offsetParam = 'offset',
        string $limitParam = 'limit',
        int $defaultLimit = 10,
    ) {
        if ($offsetParam === '' || $limitParam === '') {
            throw new \InvalidArgumentException(
                'Parameter names cannot be empty'
            );
        }

        if ($offsetParam === $limitParam) {
            throw new \InvalidArgumentException(
                'Offset and limit parameter names must be different'
            );
        }

        $this->offsetParam  = $offsetParam;
        $this->limitParam   = $limitParam;
        $this->limit = max(1, $defaultLimit);
    }

    public static function fromRequest(
        ServerRequestInterface $request,
        int                    $defaultLimit = 10,
    ): self {
        return new self($request->getUri(), defaultLimit: $defaultLimit);
    }

    public function nextUrl(PaginatorInterface $paginator): ?string
    {
        $offset = $paginator->nextOffset;

        return $offset !== null
            ? $this->buildUrl($offset, $paginator->limit)
            : null;
    }

    public function prevUrl(PaginatorInterface $paginator): ?string
    {
        $offset = $paginator->prevOffset;

        return $offset !== null
            ? $this->buildUrl($offset, $paginator->limit)
            : null;
    }

    public function pageUrl(int $page, int $limit): string
    {
        $page  = max(1, $page);
        $limit = max(1, $limit);

        return $this->buildUrl(($page - 1) * $limit, $limit);
    }

    public function withBaseUri(UriInterface $uri): self
    {
        return new self(
            $uri,
            $this->offsetParam,
            $this->limitParam,
            $this->limit,
        );
    }

    private function buildUrl(int $offset, int $limit): string
    {
        parse_str($this->baseUri->getQuery(), $params);

        if ($offset === 0) unset($params[$this->offsetParam]);
        else $params[$this->offsetParam] = $offset;

        $params[$this->limitParam] = $limit;

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return (string) $this->baseUri->withQuery($query);
    }
}
