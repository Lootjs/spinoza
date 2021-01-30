# Spinoza - генерация документации к сервису
`Ignorantia nоn est argumentum. Незнание — не аргумент. © Spinoza`

## Регистрация роутов
@spinoza-register-route принимает json object. 
```php
/**
@spinoza-register-route {"method": "get", "route": "loyalty/v5/campaigns/by_filial/{filialId}", "usage": "для получения КБ в карточке локаций"}
*/

```
## Регистрация событий
@spinoza-register-event принимает json object. 
```php
/**
@spinoza-register-event {"name": "ParstoreLocationAddressUpdated", "exchange": "parstore", "routing_key": "location.address.updated"}
*/
```
