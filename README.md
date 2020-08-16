# easy-api-bundle
Symfony bundle to easily make api, inspired by work of [DarwinOnLine](https://github.com/DarwinOnLine)

### CRUD controllers :
```php
/**
 * @SWG\Tag(name="MyEntity")
 */
class MyEntityCrudController extends AbstractApiController
{
    public const entityClass = MyEntity::class;
    public const entityCreateTypeClass = MyEntityType::class;
    public const entityUpdateTypeClass = MyEntityType::class;
    public const serializationGroups = ['my_entity_full'];

    use CrudControllerTrait;
}
```

### Configuration (everything is optionnal) :

```yaml
easy_api:
  authentication: true
  user_class: AppBundle\Entity\User\User
  user_tracking :
    enable: false
    connection_history_class: AppBundle\Entity\User\ConnectionHistory
  inheritance:
    entity: 'CoreBundle\Entity\AbstractEntity'
    entityReferential: 'CoreBundle\Entity\AbstractReferential'
    form: 'CoreBundle\Form\Type\AbstractCoreType'
    repository: 'CoreBundle\Form\Type\AbstractRepository'
    controller: 'CoreBundle\Controller\AbstractApiController'
    serialized_form: 'CoreBundle\Form\Model\SerializedForm'
```
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
