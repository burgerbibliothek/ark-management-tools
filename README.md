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
Output:
```
true
```

### Split ARK into components
```php
use Burgerbibliothek\ArkManagementTools\Ark

var_dump(Ark::splitIntoComponents('https://ark.example.tld/ark:/99999/a1b2c3d4e5f6g/suffix?info'));
```
Output
```php
array(7) {
  ["resolverService"]=> string(24) "https://ark.example.tld/"
  ["naan"]=> string(5) "99999"
  ["baseName"]=> string(13) "a1b2c3d4e5f6g"
  ["baseCompactName"]=> string(23) "ark:99999/a1b2c3d4e5f6g"
  ["checkZone"]=> string(19) "99999/a1b2c3d4e5f6g"
  ["suffixes"]=> string(11) "suffix?info"
  ["inflection"]=> string(5) "?info"
}
```

