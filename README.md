# easy-api-bundle
Symfony bundle to easily make api, inspired by work of [DarwinOnLine](https://github.com/DarwinOnLine)

### CRUD controllers :
Exemple :
```php
/**
 * @Route("/my-path", name="my_path")
 * @SWG\Tag(name="MyEntity")
 */
class MyEntityCrudController extends AbstractApiController
{
    public const entityClass = MyEntity::class;
    public const entityCreateTypeClass = MyEntityType::class;
    public const entityUpdateTypeClass = MyEntityType::class;
    public const serializationGroups = ['my_entity_full'];
    public const listSerializationGroups = ['my_entity_light'];
    public const filterFields = [];
    public const filterSortFields = [];
    
    use CrudControllerTrait;
}
```
### CRUD routing :
Example:
```yaml
api_my_path:
  resource: "@AppBundle/Controller/MyContext/MyController.php"
  type: annotation
```

### Configuration (everything is optionnal) :

```yaml
easy_api:
  authentication: true
  user_class: AppBundle\Entity\User\User
  normalization:
    datetime_format: 'Y-m-d H:i:s' # DateTimeInterface::ATOM format by default
  user_tracking :
    enable: false
    connection_history_class: AppBundle\Entity\User\ConnectionHistory
  inheritance:
    entity: 'AppBundle\Entity\AbstractEntity'
    entityReferential: 'AppBundle\Entity\AbstractReferential'
    form: 'AppBundle\Form\Type\AbstractCoreType'
    repository: 'AppBundle\Form\Type\AbstractRepository'
    controller: 'AppBundle\Controller\AbstractApiController'
    serialized_form: 'AppBundle\Form\Model\SerializedForm'
```

### Form options for frontend apps :
You can specify some options in attr field of form fields:
* group : to group fields 
* discriminator
* other options you want to pass

### API-DOC Annotations
You can use all annotations of the nelmio api bundle.
The DoctrineAnnorationReader cannot read self and static constants but with this bundle you can use it as strings in two cases as long as the issue isn't closed :

In Nelmio model annotation:
```php
    /**
     * Get entity.
     *
     * @Swagger\Annotations\Response(
     *     response=200,
     *     description="Successful operation",
     *     @Swagger\Annotations\Schema(
     *          ref=@Nelmio\ApiDocBundle\Annotation\Model(
     *              type="static::entityClass",
     *              groups={"static::serializationGroups"}
     *          )
     *      )
     * ),
     * @param Request $request
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        ...
    }
```
And in an annotation named "GetFormParameter" which allows you to generate api-doc of form fields as get parameters :
```php
     /**
      * List entities.
      *
      * @EasyApiBundle\Annotation\GetFormParameter(type="static::entitySearchTypeClass")
      * ...
      */
    public function listAction(Request $request)
    {
        ...       
    }
```
## Test framework
### User assertion:
Define it in ```php static::$additionalAssessableFunctions ``` and implement it, with good parameters :
```php
static::$additionalAssessableFunctions = ['assertMyAssertion'];
...
/**
* @param $key The key in response
* @param $parameter The parameter passed (optionnal)
* @param $value The value in response
*/
protected static function assertMyAssertion($key, $parameter, $value): void
{
    $parameter = $parameter ?? 'John';
    $expected = "hello world $parameter";
    $errorMessage = "Invalid value for {$key} field: expected {$expected}, get {$value}";
    static::assertTrue($expected === $value, $errorMessage);
}
```
## Performance optimization
* Implement the Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface on normalizers (see https://symfony.com/doc/current/serializer/custom_normalizer.html)
* see https://symfony.com/doc/4.4/performance.html