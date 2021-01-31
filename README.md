# Spinoza - генерация документации к сервису
`Ignorantia nоn est argumentum. Незнание — не аргумент. © Spinoza`

## Install
composer require loot/spinoza

## Генерация документации
```shell script
php artisan spinoza:generate
```
Если нужно обновить кэш файлов:
```shell script
php artisan spinoza:generate --force-update
```
## Регистрация роутов
@spinoza-register-route принимает json object. 
```php
/**
@spinoza-register-route {"method": "get", "route": "loyalty/v5/campaigns/by_filial/{filialId}", "usage": "для получения КБ в карточке локаций"}
*/
```
## Todo
- [ ] Сделать кеширование
- [ ] Рефакторинг
- [ ] Сделать ссылки на методы, для роутов
