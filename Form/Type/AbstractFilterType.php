<?php

namespace EasyApiBundle\Form\Type;

use EasyApiBundle\Form\Model\FilterModel;
use EasyApiBundle\Util\ApiProblem;
use EasyApiBundle\Util\Maker\EntityConfigLoader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractFilterType extends AbstractApiType
{
    /**
     * @var string
     */
    protected static $dataClass = FilterModel::class;

    /**
     * string[]
     */
    protected const excluded = ['sort', 'page', 'limit'];

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * string[]
     */
    protected $fields = [];

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \ReflectionException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if(null !== $options['entityClass'] && null !== $options['fields']) {
            static::addFilterFields($builder, $options);
        }

        $builder
            ->add('sort', TextType::class,
                [
                    'required' => false,
                    'constraints' => new Assert\Length([
                        'max' => 255,
                        'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'filter', 'sort'),
                    ]),
                ]
            )
            ->add('page', IntegerType::class,
                [
                    'required' => false,
                    'constraints' => new Assert\Length([
                        'max' => 7,
                        'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'filter', 'page'),
                    ]),
                ]
            )
            ->add('limit', IntegerType::class,
                [
                    'required' => false,
                    'constraints' => new Assert\Length([
                        'max' => 7,
                        'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'filter', 'limit'),
                    ]),
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('entityClass', $this->entityClass);
        $resolver->setDefault('fields', $this->fields);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \ReflectionException
     */
    protected function addFilterFields(FormBuilderInterface $builder, array $options): void
    {
        $entityConfiguration = EntityConfigLoader::createEntityConfigFromEntityFullName($options['entityClass']);
        foreach ($options['fields'] as $fieldName) {
            if(!in_array($fieldName, self::excluded)) {
                if($entityConfiguration->hasField($fieldName)){
                    var_dump($fieldName);echo "<br >";
                    $field = $entityConfiguration->getField($fieldName);
                    if($field->isNativeType()) {
                        $method = self::convertEntityNativeTypeToFormFieldMethod($field->getType());
                        static::$method($builder, $fieldName);
                    } else {
                        static::addEntityFilter($builder, $fieldName, $field->getEntityType());
                    }
                } else {
                    static::addTextFilter($builder, $fieldName);
                }
            }
        }
    }

    /**
     * @param $type
     * @return string
     */
    protected static function convertEntityNativeTypeToFormFieldMethod($type)
    {
        var_dump($type);
        switch ($type) {
            case 'integer':
                return 'addIntegerFilter';
            case 'float':
                return 'addNumberFilter';
            case 'date':
                return 'addDateFilter';
            case 'datetime':
                return 'addDateTimeFilter';
            default:
                return 'addTextFilter';
        }
    }

    protected static function addTextFilter(FormBuilderInterface $builder, string $name)
    {
        $builder ->add($name, TextType::class,
            [
                'required' => false,
                'constraints' => new Assert\Length([
                    'max' => 255,
                    'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, static::getDataClassShortName(), $name),
                ]),
            ]
        );

        return $builder;
    }

    protected static function addIntegerFilter(FormBuilderInterface $builder, string $name)
    {
        $builder->add($name, IntegerType::class, ['required' => false,]);
        $builder->add("{$name}_min", IntegerType::class, ['required' => false,]);
        $builder->add("{$name}_max", IntegerType::class, ['required' => false,]);

        return $builder;
    }

    protected static function addNumberFilter(FormBuilderInterface $builder, string $name)
    {
        $builder->add($name, NumberType::class, ['required' => false,]);
        $builder->add("{$name}_min", NumberType::class, ['required' => false,]);
        $builder->add("{$name}_max", NumberType::class, ['required' => false,]);

        return $builder;
    }

    protected static function addDateFilter(FormBuilderInterface $builder, string $name)
    {
        $builder->add($name, DateType::class, ['required' => false,]);
        $builder->add("{$name}_min", DateType::class, ['required' => false,]);
        $builder->add("{$name}_max", DateType::class, ['required' => false,]);

        return $builder;
    }

    protected static function addDateTimeFilter(FormBuilderInterface $builder, string $name)
    {
        $builder->add($name, DateTimeType::class, ['required' => false,]);
        $builder->add("{$name}_min", DateTimeType::class, ['required' => false,]);
        $builder->add("{$name}_max", DateTimeType::class, ['required' => false,]);

        return $builder;
    }

    protected static function addEntityFilter(FormBuilderInterface $builder, string $name, $fieldEntityClass)
    {
        $builder ->add($name, EntityType::class,
            [
                'required' => false,
                'class' => $fieldEntityClass,
            ]
        );

        return $builder;
    }
}