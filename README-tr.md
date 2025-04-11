# Symfony Structured Mapper Bundle

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Author](https://img.shields.io/badge/author-emreuyguc-orange)

Bu Symfony bundle’ı, emreuyguc/structured-mapper paketinin Symfony uygulamalarında kolayca kullanılmasını sağlar. DTO ↔ Entity dönüşümlerini basit ve güçlü bir şekilde yönetmek için tasarlanmıştır.

## Özellikler

- **Attribute ile Mapping Tanımlama:** Dönüşüm kurallarını attribute kullanarak tanımlama imkanı.
- **İki yönlü Dönüşüm Tanımlama:** Dönüşüm kurallarını kaynak veya hedef sınıfın herhangi birinde belirleyebilme.
- **Property Bazlı Mapping:** Sınıf propertylerine dönüşüm tanımlamaları yapabilme.
- **Context Aktarımı ve Kullanımı:** Mapleme sırasında context bilgilerini aktarabilme.
- **Value Transformerlar:** Tür, tip ve veri dönüşümleri için value transformer yapısı.
- **Hazır Transformerlar:** Doctrine Entity, Enum, Array item gibi yaygın dönüşümler için paketle birlikte gelen value transformerlar.
- **Custom Mapper Tanımlamaları:** Özel yazılmış dönüşüm sınıflarını tanımlayabilme.(bknz: MapperRegistry ve MapperInterface)
- **Array Elemanı Dönüşümleri:** Array elemanlarını dönüştürme ve her elemanı value transformer ile işleyebilme imkanı.(bknz: ValueTransformer/ArrayItemTransform/ArrayItemTransformer.php)
- **Sub Object Dönüşüm Tanımlayabilme:** Child objelere dönüşüm tanımlamaları yapabilme. (bknz: ValueTransformer/ObjectTransform/WithMapper.php)

## Kurulum

Projenize Composer kullanarak Euu Structured Mapper'ı ekleyin:

```bash
composer require emreuyguc/structured-mapper-bundle
```

## Kullanım

### Mapper Servis Çağırma

Symfony'de otomatik servis enjeksiyonu ile `StructuredMapper`'ı doğrudan kullanabilirsiniz:
 
```php
public function __construct(private readonly StructuredMapper $mapper)
{
}
```

Buna ek olarak container içerisinde `structured_mapper` servisini çağırabilirsiniz

### Kullanım

```php
use Euu\Bundle\StructuredMapperBundle\StructuredMapper\StructuredMapper;

$mapper = $container->get('structured_mapper');

$inputDto = new InputDto();

$mapper->map(sourceObject: $source, targetClass: MyEntity::class, context [
    //object mapper context parameters..
]);
```

### Konfigrasyonlar

src/config/packages/structured_mapper.yaml
```yaml
structured_mapper:
    default_mapper_context:
        # see ObjectMapper settings
    cache:
        enabled: true
        ttl: 3600
        prefix: 'structured_mapper.'
        service: 'cache.app'
        preload:
            enabled: true
            readers:
                attribute_reader:
                    instance_of: ~ #example : 'App\Dto\BaseDto'
                    read_directory: ~ #currently not supported
```

### Populate

```
$inputDto = new InputDto();
$target = new Person(name:'Emre', age: 28);

$mapper->map(sourceObject: $source, targetClass: Person::class, context [
    ObjectMapper::TO_POPULATE => $target
]);
```

### Context Parameters

Varsayılan ObjectMapper için tanımlanmış context parametreleri aşağıdaki gibidir;
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

güncel parametrelerin listesini [buradan](https://github.com/emreuyguc/php-structured-mapper/blob/main/src/Mapper/ObjectMapper/ObjectMapper.php) bulabilirsiniz.

* skip_null_values : null valueler için eşleme yapmaz
* to_populate: mappingi var olan bir objeye yapar.
* allow_auto_mapping: Map tanımlamaları olmasa bile kaynak ve hedeflerde property taraması yaparak, eşleşen isimdekileri otomatik olarak dönüşümler.
* auto_sub_mapping: inaktif
* groups: inaktif
* type_enforcement: kaynak ve hedefteki tipleri kontrol ederek dönüşümler.
* array_add_method: Value transformer için rezerve
* array_clear_method: Value transformer için rezerve
* array_clear_expression: Value transformer için rezerve

### Sınıf Üzerinde Tanımlama

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


### Property Üzerinde Tanımlama

```php
  class StockDto
  {
      #[OnMapTo(StockEntity::class, targetPath: 'stock')]
      public ?int $stockCount = null;
  
      #[OnMapTo(StockEntity::class, targetPath: 'warehouse')]
      public ?string $stockWarehouse = null;
  }
```

### Alt Objelere erişim

Hedef sınıftaki alt obje için;
```php
    #[OnMapTo(ProductEntity::class, targetPath: 'owner.fullName')]
    public string $ownerName;
```

Kaynak sınıftaki alt obje için;
```php
  #[MapTo(
      ProductEntity::class,
      mappings: [
          new Mapping('stock.stockWarehouse', 'stockWarehouse')
      ])]
  class ProductDto
  {
```


#### Alt Obje Dönüşümü

```php
    #[OnMapTo(
        targetClass: ProductEntity::class,
        transformerMeta: new WithMapper(targetClass: StockEntity::class)
    )]
    public StockDto $stock;
```

#### Array Dönüşümü

```php
    #[OnMapTo(ProductEntity::class, transformerMeta: new ArrayItemTransform(
        itemTransformerMeta: new WithMapper(targetClass: SellerEntity::class)
    ))]
    public array $sellers;
```

### Value Transformer Kullanımı

#### ImplodeTransformer

+ Not: `ImplodeTransformer`, birden fazla kaynak property'yi birleştirerek tek bir hedef property’ye aktarır. Sadece `MapTo` üzerinden kullanılabilir; çünkü `OnMapTo` tek kaynak alan destekler.

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

Varsayılan parametrelerle id çözümlü kullanım;
```php
    #[OnMapTo(ProductEntity::class, targetPath: 'taxGroup', transformerMeta: new EntityResolve(TaxGroup::class))]
    public string $sellTaxGroupId;
```

Güncelleme destekleyen ve birden fazla olabilen entity dönüşümü için gelişmiş yapılandırma
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

EntityResolve sınıfı parametre açıklamaları şöyledir;

* ?string $repositoryMethod = 'find':custom repository methodu ayarlanabilir
* ?array $findArguments = null: repository methoduna gönderilecek ek argümanlar ayarlanabilir
* bool $nullable = false: bulunamadığında hata vermemesini sağlar


## Kısıtlamalar ve Düşünceler

- **Object Constructor Sorunu:** Constructor içeren objeler dönüşüm esnasında `init` edilemiyor. Bu alan geliştirilmeye açıktır. (Öncelik: Yüksek)
- **Ters Dönüşüm İmkanı:** `a -> b` dönüşümleri tanımlanmışsa, Aslında bunun ters dönüşümüde limitler dahilinde mümkündür (`b -> a`) fakat bu özellik şuan desteklenmemektedir.  (Öncelik: Orta)
- **Tip Dönüşüm Mekanizması:** Genel tip dönüşümleri için bir mekanizma bulunmamaktadır. Kullanıcı bunu manuel gerçekleştirmelidir veya basit dönüşümler için `type_enforcement` parametresi `false` yapılabilir.  (Öncelik: Düşük)
- **Property İsimlendirme:** Property isimleri mapping esnasında manuel tanımlanır.Bunun yerine Otomatik bir isim dönüştürme mekanizması eklenebilir.  (Öncelik: 0rta)
- **Cache Mekanizması:** Şu an için herhangi bir cache mekanizması mevcut değildir. Uygun bir cache yapısı eklenebilir.  (Öncelik: Yüksek)
- **Map kontrolü:** `canMap` gibi bir metod eklenerek kontrol yapılabilir.  (Öncelik: Yüksek)
- **Otomatik Alt Obje Dönüşümü:** Child objelerin tiplerine göre arama yaparak dönüşüm yapabilme özelliği eklenebilir.  (Öncelik: Orta)
- **Custom Mapper Tanımında Dönüşüm Tipi kontrolü:** Custom mapper tanımlamalarında geçerli Source ve Destination tanımlaması yapılabilir. Şuanda bu kontrol yapılmamaktadır.  (Öncelik: Yüksek)
- **Entity Find işlevinde Expression kullanımı:** Entity dönüşümlerinde custom parametreler için expression lang kullanılabilir  (Öncelik: Düşük)
- **Testler:** Testler yazılacak.  (Öncelik: Yüksek)
- **Ignore/Allow/Group Yapısı:** Mapping esnasında geçerli olması için Ignore/Allow/Group tanımlamaları eklenebilir.  (Öncelik: Düşük)
- **Kalıtım Alma:** Kalıtım alan alt objelerde geçerli olması için kalıtım alma mekanizması eklenebilir.  (Öncelik: Düşük)
- **Alt Obje Dönüşüm limiti:** Alt objelerde mapping esnasında sonsuz döngüye girmemesi için limit/hata kontrolü eklenebilir.  (Öncelik: Orta)

## Lisans

Bu paket [MIT Lisansı](LICENSE.md) ile lisanslanmıştır.
