# Componenta HTTP Pagination

HTTP helpers for paginated resources. The package connects `componenta/paginator` with PSR-7 request URIs and response arrays.

Use this package when an HTTP API already has a `PaginatorInterface` and needs stable `prev` / `next` URLs and a serializable pagination envelope.

## Boundary

This package does not calculate pagination itself and does not query storage. Page math and `PaginatorInterface` are provided by `componenta/paginator`. This package only adapts that paginator to HTTP URLs and array responses.

## Installation

```bash
composer require componenta/http-pagination
```

## Dependencies

| Package | Purpose |
|---|---|
| `componenta/paginator` | Provides `PaginatorInterface` and pagination metadata. |
| `componenta/arrayable` | Provides the `Arrayable` contract used by `ResourcePaginator`. |
| `psr/http-message` | Provides PSR-7 request and URI interfaces. |

## Main API

`PaginatorUrlBuilder` builds page URLs while preserving the current query string:

```php
use Componenta\Http\PaginatorUrlBuilder;

// Current request: /posts?tag=php&limit=20&offset=20
$builder = PaginatorUrlBuilder::fromRequest($request, defaultLimit: 20);

$next = $builder->nextUrl($paginator);
$prev = $builder->prevUrl($paginator);
$page = $builder->pageUrl(3, 20);
```

`PaginatorUrlBuilder::fromRequest()` uses the request URI and the default parameter names `offset` and `limit`. The constructor allows custom parameter names:

```php
$builder = new PaginatorUrlBuilder($uri, offsetParam: 'skip', limitParam: 'take');
```

`ResourcePaginator` wraps a paginator and generated links into an arrayable structure for API responses:

```php
use Componenta\Http\ResourcePaginator;

$resource = new ResourcePaginator($paginator, $request->getUri());

return $resource->toArray();
```

The default output fields are `count`, `prev`, `next`, `range`, `page`, and `results`. Pass a field list to the constructor to limit the output:

```php
$resource = new ResourcePaginator($paginator, $request->getUri(), ['count', 'next', 'results']);
```

Unknown field names are included with `null` values because `ResourcePaginator` preserves the requested field list.

## Errors

`PaginatorUrlBuilder` throws `InvalidArgumentException` when the offset or limit parameter name is empty, or when both parameter names are the same. Page and limit values passed to `pageUrl()` are normalized to at least `1`.

## Related Packages

- [`componenta/paginator`](../paginator/README.md) provides the paginator value object.
- [`componenta/arrayable`](../arrayable/README.md) provides the `Arrayable` contract.
