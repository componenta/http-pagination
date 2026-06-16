<?php

declare(strict_types=1);

namespace Componenta\Http;

use Componenta\Arrayable\Arrayable;
use Componenta\Stdlib\PaginatorInterface;
use Psr\Http\Message\UriInterface;

/**
 * Universal paginator resource for API responses.
 * Generates prev/next URLs and builds an array representation of the paginator.
 */
class ResourcePaginator implements Arrayable
{
    /** @var string[] Fields to include in the output array */
    protected array $fields;

    /** @var PaginatorUrlBuilder URL builder for generating pagination links */
    protected PaginatorUrlBuilder $urlBuilder;

    /**
     * @param PaginatorInterface $paginator The paginator instance containing results and metadata
     * @param UriInterface $baseUri The base URI for generating prev/next URLs
     * @param string[] $fields Fields to include in the output. Defaults to standard pagination fields
     */
    public function __construct(
        protected PaginatorInterface $paginator,
        UriInterface $baseUri,
        array $fields = ['count', 'prev', 'next', 'range', 'page', 'results']
    ) {
        $this->fields = $fields;
        $this->urlBuilder = new PaginatorUrlBuilder($baseUri, defaultLimit: $paginator->limit);
    }

    /**
     * Converts the paginator and URL information into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->fields as $field) {
            $data[$field] = match ($field) {
                'count'   => $this->paginator->totalCount,
                'prev'    => $this->urlBuilder->prevUrl($this->paginator),
                'next'    => $this->urlBuilder->nextUrl($this->paginator),
                'range'   => $this->paginator->range,
                'page'    => $this->paginator->currentPage,
                'results' => $this->paginator->results,
                default   => null,
            };
        }

        return $data;
    }
}
