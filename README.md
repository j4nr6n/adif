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

$data = (new Parser())->parse('example.adif'); // Or a string of ADIF encoded data
```

Write
=====

```php
use j4nr6n/ADIF/Writer;

$data = [
    // ...
];

(new Writer())->write('./example.adif', $data);

// Program ID and version can be passed to the constructor
// (new Writer('Example', '0.1.0'))->write('./example.adif', $data);
```
