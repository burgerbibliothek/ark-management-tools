# ark-management-tools
PHP Library for minting / validating Archival Resource Keys (ARK) and managing Electronic Resource Citation (ERC) Records.

## Examples
### Generate new ARK
```php
use Burgerbibliothek\ArkManagementTools\Ark

echo ARK::generate('12345', '0123456789bcdfghjkmnpqrstvwxz', 7, 'q1');
```
Expected possible output:
```
12345/q15fk5zszx
```

### Verify ARK with NOID Check digit algorithm
```php
use Burgerbibliothek\ArkManagementTools\Ncda

echo ARK::verify('12345/q15fk5zszx', '0123456789bcdfghjkmnpqrstvwxz');
```
expected Output:
```
true
```


