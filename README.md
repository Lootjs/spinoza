# Spinoza - генерация документации к сервису
`Ignorantia nоn est argumentum. Незнание — не аргумент. © Spinoza`

## Install
```
composer require --dev loot/spinoza
```
## Генерация документации
```
php artisan larabase:generate-docs
```
## Регистрация роутов
@spinoza-register-route принимает json object. 
```php
/**
@spinoza-register-route {"method": "get", "route": "loyalty/v5/campaigns/by_filial/{filialId}", "usage": "для получения КБ в карточке локаций"}
*/
```
## Todo
- [x] Сделать кеширование
- [x] Рефакторинг
- [ ] Сделать ссылки на методы, для роутов
