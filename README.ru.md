# Componenta HTTP Pagination

HTTP-помощники для постраничных ресурсов. Пакет связывает `componenta/paginator` с PSR-7 URI запросов и массивами ответов.

Используйте его, когда HTTP API уже получил `PaginatorInterface` и ему нужны стабильные ссылки `prev` / `next` и сериализуемая оболочка пагинации.

## Граница пакета

Пакет не рассчитывает пагинацию самостоятельно и не обращается к хранилищу. Математика страниц и `PaginatorInterface` находятся в `componenta/paginator`. Этот пакет только адаптирует готовый пагинатор к HTTP-ссылкам и массивам ответа.

## Установка

```bash
composer require componenta/http-pagination
```

## Зависимости

| Пакет | Назначение |
|---|---|
| `componenta/paginator` | Дает `PaginatorInterface` и метаданные пагинации. |
| `componenta/arrayable` | Дает контракт `Arrayable`, который использует `ResourcePaginator`. |
| `psr/http-message` | Дает PSR-7 интерфейсы запроса и URI. |

## Основной API

`PaginatorUrlBuilder` строит URL страниц с сохранением текущей строки запроса:

```php
use Componenta\Http\PaginatorUrlBuilder;

// Текущий запрос: /posts?tag=php&limit=20&offset=20
$builder = PaginatorUrlBuilder::fromRequest($request, defaultLimit: 20);

$next = $builder->nextUrl($paginator);
$prev = $builder->prevUrl($paginator);
$page = $builder->pageUrl(3, 20);
```

`PaginatorUrlBuilder::fromRequest()` использует URI запроса и стандартные имена параметров `offset` и `limit`. Конструктор позволяет задать другие имена параметров:

```php
$builder = new PaginatorUrlBuilder($uri, offsetParam: 'skip', limitParam: 'take');
```

`ResourcePaginator` оборачивает пагинатор и сгенерированные ссылки в структуру, которую можно превратить в массив для API-ответа:

```php
use Componenta\Http\ResourcePaginator;

$resource = new ResourcePaginator($paginator, $request->getUri());

return $resource->toArray();
```

Поля по умолчанию: `count`, `prev`, `next`, `range`, `page` и `results`. Передайте список полей в конструктор, чтобы ограничить вывод:

```php
$resource = new ResourcePaginator($paginator, $request->getUri(), ['count', 'next', 'results']);
```

Неизвестные имена полей попадут в результат со значением `null`, потому что `ResourcePaginator` сохраняет запрошенный список полей.

## Ошибки

`PaginatorUrlBuilder` выбрасывает `InvalidArgumentException`, если имя параметра смещения или лимита пустое либо если оба имени совпадают. Значения страницы и лимита, переданные в `pageUrl()`, нормализуются минимум до `1`.

## Связанные пакеты

- [`componenta/paginator`](../paginator/README.ru.md) дает объект значения для пагинации.
- [`componenta/arrayable`](../arrayable/README.ru.md) дает контракт `Arrayable`.
