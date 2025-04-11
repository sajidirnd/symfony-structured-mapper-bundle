# Symfony Structured Mapper Bundle

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Author](https://img.shields.io/badge/author-emreuyguc-orange)

This Symfony bundle enables seamless usage of the `emreuyguc/structured-mapper` package within Symfony applications.  
It is designed to manage DTO ↔ Entity transformations in a simple and powerful way.

## Features

- **Mapping Definition via Attributes:** Ability to define transformation rules using attributes.
- **Bidirectional Mapping Definition:** Ability to define transformation rules in either the source or target class.
- **Property-Based Mapping:** Ability to define transformations directly on class properties.
- **Context Passing and Usage:** Ability to pass context data during mapping.
- **Value Transformers:** A structure for type and data transformations using value transformers.
- **Built-in Transformers:** Comes with built-in value transformers for common cases such as Doctrine Entity, Enum, Array item, etc.
- **Custom Mapper Definitions:** Ability to define custom transformation classes. (see: MapperRegistry and MapperInterface)
- **Array Item Transformation:** Ability to transform array items and process each element with a value transformer. (see: ValueTransformer/ArrayItemTransform/ArrayItemTransformer.php)
- **Sub Object Transformation Definition:** Ability to define transformations for child objects. (see: ValueTransformer/ObjectTransform/WithMapper.php)

## Installation

Add the Euu Structured Mapper to your project using Composer:

```bash
composer require emreuyguc/structured-mapper-bundle
```

## Usage

### Accessing the Mapper Service

You can use Symfony autowiring as follows:

```php
public function __construct(private readonly StructuredMapper $mapper)
{
}
```

Alternatively, you can call the `structured_mapper` service from the container.

### Usage

```php
use Euu\Bundle\StructuredMapperBundle\StructuredMapper\StructuredMapper;

$mapper = $container->get(StructuredMapper::class);

$inputDto = new InputDto();

$mapper->map(sourceObject: $source, targetClass: MyEntity::class, context: [
    //object mapper context parameters..
]);
```

### Populate

```
$inputDto = new InputDto();
$target = new Person(name:'Emre', age: 28);

$mapper->map(sourceObject: $source, targetClass: Person::class, context: [
    ObjectMapper::TO_POPULATE => $target
]);
```

### Context Parameters

The default context parameters defined for the ObjectMapper are as follows;
```
    public const SKIP_NULL_VALUES = 'skip_null_values';
    public const TO_POPULATE = 'to_populate';
    public const ALLOW_AUTO_MAPPING = 'allow_auto_mapping';
    public const GROUPS = 'groups';

    public const AUTO_SUB_MAPPING = 'auto_sub_mapping';

    public const TYPE_ENFORCEMENT = 'type_enforcement';
    public const ARRAY_ADD_METHOD = 'array_add_method';
    public const ARRAY_CLEAR_METHOD = 'array_clear_method';
    public const ARRAY_CLEAR_EXPRESSION = 'array_clear_expression';
```

You can find the latest list of parameters [here](https://github.com/emreuyguc/php-structured-mapper/blob/main/src/Mapper/ObjectMapper/ObjectMapper.php).

* skip_null_values: skips mapping for null values
* to_populate: maps into an existing object
* allow_auto_mapping: maps matching property names automatically even if mappings are not explicitly defined
* auto_sub_mapping: inactive
* groups: inactive
* type_enforcement: enforces type validation between source and target
* array_add_method: reserved for value transformer
* array_clear_method: reserved for value transformer
* array_clear_expression: reserved for value transformer

### Class-Level Mapping

```php
  #[MapTo(
      ProductEntity::class,
      mappings: [
          new Mapping('productCode', 'productNumber'),
          new Mapping('barcode', 'barcodeNumber')
      ],
      mapperContext: [
          ObjectMapper::SKIP_NULL_VALUES => true,
          ObjectMapper::ALLOW_AUTO_MAPPING => false,
          ObjectMapper::TYPE_ENFORCEMENT => false
      ])]
  class ProductDto{
```

### Property-Level Mapping

```php
  class StockDto
  {
      #[OnMapTo(StockEntity::class, targetPath: 'stock')]
      public ?int $stockCount = null;
  
      #[OnMapTo(StockEntity::class, targetPath: 'warehouse')]
      public ?string $stockWarehouse = null;
  }
```

### Accessing Sub Objects

For a nested target property:

```php
    #[OnMapTo(ProductEntity::class, targetPath: 'owner.fullName')]
    public string $ownerName;
```

For a nested source property:

```php
  #[MapTo(
      ProductEntity::class,
      mappings: [
          new Mapping('stock.stockWarehouse', 'stockWarehouse')
      ])]
  class ProductDto
  {
```

#### Sub Object Transformation

```php
    #[OnMapTo(
        targetClass: ProductEntity::class,
        transformerMeta: new WithMapper(targetClass: StockEntity::class)
    )]
    public StockDto $stock;
```

#### Array Transformation

```php
    #[OnMapTo(ProductEntity::class, transformerMeta: new ArrayItemTransform(
        itemTransformerMeta: new WithMapper(targetClass: SellerEntity::class)
    ))]
    public array $sellers;
```

### Using Value Transformers

#### ImplodeTransformer

Note: `ImplodeTransformer` merges multiple source properties into one target property. Can only be used with `MapTo` as `OnMapTo` only supports single source.

```php
  #[MapTo(
      ProductEntity::class,
      mappings: [
          new Mapping(['sku', 'code'], 'productCode', new ImplodeTransform('-')),
     ])]
```

#### ExplodeTransformer

```php
    #[OnMapTo(ProductEntity::class, targetPath: 'model', transformerMeta: new ExplodeTransform('-', 1, 2))]
    #[OnMapTo(ProductEntity::class, targetPath: 'brand', transformerMeta: new ExplodeTransform('-', 0, 2))]
    public string $brandModel;
```

#### EnumTransformer

```php
    #[OnMapTo(ProductEntity::class, targetPath: 'unit', transformerMeta: new EnumTransform(UnitType::class))]
    public string $unit;
```

#### EntityResolve Transformer

Basic usage with default ID resolver:

```php
    #[OnMapTo(ProductEntity::class, targetPath: 'taxGroup', transformerMeta: new EntityResolve(TaxGroup::class))]
    public string $sellTaxGroupId;
```

Advanced usage for updatable collections:

```php
    #[OnMapTo(
        ProductEntity::class,
        targetPath: 'subCategories',
        transformerMeta: new ArrayItemTransform(
            itemTransformerMeta: new EntityResolve(SubCategoryEntity::class),
            transformerContext: [
                ArrayItemTransformer::USE_ADD_METHOD => 'addSubCategory',
                ArrayItemTransformer::CLEAR_METHOD => 'subCategories.clear()',
                ArrayItemTransformer::CLEAR_EXPRESSION => "'update' in context['groups']"
            ]
        )
    )]
    public array $subCategoryIds;
```

EntityResolve class parameter descriptions:

* ?string $repositoryMethod = 'find': set custom repository method
* ?array $findArguments = null: extra arguments to be passed to the method
* bool $nullable = false: suppresses error if entity is not found

## Limitations and Considerations

- **Object Constructor Issue:** Objects with constructors cannot be instantiated during mapping. (Priority: High)
- **Reverse Mapping Support:** If `a -> b` mappings are defined, reverse mappings (`b -> a`) are theoretically possible, but currently unsupported. (Priority: Medium)
- **Type Conversion Mechanism:** No general type conversion mechanism is available. Users must handle it manually or set `type_enforcement` to false for simple cases. (Priority: Low)
- **Property Naming:** Properties must be mapped manually. An automatic naming converter could be added. (Priority: Medium)
- **Cache Mechanism:** No caching mechanism exists yet. A suitable structure can be added. (Priority: High)
- **Map Control:** A `canMap` method could be added for verification. (Priority: High)
- **Auto Sub-Object Mapping:** Auto-mapping of child objects based on type hints can be added. (Priority: Medium)
- **Transformation Type Check for Custom Mappers:** Source and target types are not currently checked in custom mappers. (Priority: High)
- **Expression Language Support for Entity Resolving:** Expression-based custom parameters can be introduced. (Priority: Low)
- **Tests:** Tests will be implemented. (Priority: High)
- **Ignore/Allow/Group Structure:** Group-based inclusion/exclusion rules can be added. (Priority: Low)
- **Inheritance Support:** Mapping rules could be inherited by subclasses. (Priority: Low)
- **Sub Object Loop Limitation:** Prevent infinite loops or add error handling during recursive sub-object mapping. (Priority: Medium)

## License

This package is licensed under the [MIT License](LICENSE.md).
