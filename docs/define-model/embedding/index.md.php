<?php

use Maslosoft\Ilmatar\Widgets\MsWidget;
use Maslosoft\Mangan\Annotations\EmbeddedAnnotation;
use Maslosoft\Mangan\Document;
use Maslosoft\Mangan\EntityManager;
use Maslosoft\Mangan\Finder;
use Maslosoft\Zamm\ShortNamer;
?>
<?php
/* @var $this MsWidget */
ShortNamer::defaults()->md();
$embed = new ShortNamer(EmbeddedAnnotation::class);
$document = new ShortNamer(Document::class);
$finder = new ShortNamer(Finder::class);
$em = new ShortNamer(EntityManager::class);
?>
<title>4. Embedding Objects</title>

# Embedding Objects

<p class="alert alert-success">
	See <a href="https://github.com/MaslosoftGuides/mangan.embedding">example repository</a>
	for working example
</p>

As MongoDB comes with storage engine capable of storing nested structures,
Mangan has facility to store objects compositions. Most simple solution for
this is to embed sub object in the same document, just like it is created
at runtime.

To store sub object in database, use [`@Embedded`][annotation]
annotation on one of object property:

```
/**
* @Embedded(Address)
*/
public $address = null;
```

The [`@Embedded`][annotation] will instruct Mangan to treat property as an object when it is saved
or retrieved from MongoDB. Check also [repository for example][repo] for
this documentation page.

By default Mangan will ensure instance when transforming model. To
allow `null` as value, the `nullable` option can be set to true:

```
/**
* @Embedded(Address, 'nullable' = true)
*/
public $address = null;
```

Alternatively, the `@Nullable` annotation can be used:

```
/**
* @Embedded(Address)
* @Nullable
*/
public $address = null;
```

The property marked as [`@Embedded`][annotation] can store and
retrieve object, for example:

```
$address = new Address;
$address->city = 'Sao Paulo';
$address->street = 'New street';

$company = new Company;
$company->name = 'Maslosoft';
$company->address = $address;

$saved = $company->save();
```

To retrieve object to same state as it were before saving, use find method.
When using <?= $document; ?> class, this has convenient active document
method for finding it.

Example of finding any document in collection using active document approach:

```
$company = Company::model()->find();
echo $company->name; // Maslosoft
echo $company->address->city; // Sao Paulo
```

The same result might be achieved with <?= $em; ?> to save and <?= $finder; ?> to retrieve:

```
(new EntityManager($company))->save();
$found = (new Finder(new Company))->find() // Company instance;
```

The technique with <?= $em; ?> and <?= $finder; ?> is required when using
[plain PHP objects][plain] or optional when using [active documents][ad].

[annotation]: ../../annotations/embedded/
[ad]: ../active-document/
[repo]: https://github.com/MaslosoftGuides/mangan.embedding
[plain]: ../plain/