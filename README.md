Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Then run

```console
$ composer require j4nr6n/adif
```

Read
====

```php
use j4nr6n/ADIF/Parser;

$data = (new Parser())->parse($adifEncodedData);
```

Write
=====

```php
use j4nr6n/ADIF/Writer;

$data = [
    // ...
];

$adifEncodedData = (new Writer())->write($data);

// (new Writer('MyApp', '1.0'))->write($data);
```
