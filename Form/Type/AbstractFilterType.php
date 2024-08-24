<?php

namespace EasyApiBundle\Form\Type;

use Doctrine\ORM\EntityNotFoundException;
use EasyApiBundle\Form\Model\FilterModel;
use EasyApiCore\Model\EntityField;
use EasyApiBundle\Util\ApiProblem;
use EasyApiCore\Util\Entity\EntityConfigLoader;
use EasyApiBundle\Validator\Filter\SortConstraint;
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
    protected static ?string $dataClass = FilterModel::class;

    /**
     * string[]
     */
    public const excluded = ['sort', 'page', 'limit'];

    protected ?string $entityClass = null;

    /**
     * string[]
     */
    protected array $fields = [];

    /**
     * string[]
     */
    protected array $sortFields = [];

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \ReflectionException
     * @throws EntityNotFoundException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        /** @var FilterModel $model */
        $entityFilterModel = $builder->getData();

        if (null !== $entityFilterModel) {
            $entityFilterModel->setFields($options['fields']);
            $entityFilterModel->setSortFields($options['sortFields']);
            $entityFilterModel->setEntityClass($options['entityClass']);
        }

        static::addFilterFields($builder, $options);

        $builder
            ->add(
                'sort',
                TextType::class,
                [
                    'required' => false,
                    'constraints' => new Assert\Length([
                        'max' => 255,
                        'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'filter', 'sort'),
                    ]),
                ]
            )
            ->add(
                'page',
                IntegerType::class,
                [
                    'required' => false,
                    'constraints' => new Assert\Length([
                        'max' => 7,
                        'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'filter', 'page'),
                    ]),
                ]
            )
            ->add(
                'limit',
                IntegerType::class,
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
        $resolver->setDefault('sortFields', $this->sortFields);

        $resolver->setDefault('constraints', new SortConstraint());
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'filter';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \ReflectionException|EntityNotFoundException
     */
    protected function addFilterFields(FormBuilderInterface $builder, array $options): void
    {
        if (null !== $options['entityClass']) {
            $entityConfiguration = EntityConfigLoader::createEntityConfigFromEntityFullName($options['entityClass']);
            foreach ($options['fields'] as $fieldName) {
                if (!in_array($fieldName, self::excluded)) {
                    if ($entityConfiguration->hasField($fieldName)) {
                        $this->addFilterField($builder, $entityConfiguration->getField($fieldName), $fieldName);
                    // linked entity
                    } elseif (strpos($fieldName, '.')) {
                        $nodes = explode('.', $fieldName);
                        $field = $entityConfiguration->getField($nodes[0]);
                        if (null !== $field) {
                            $nbNodes = count($nodes);
                            for ($i = 1; $i < $nbNodes; ++$i) {
                                $subEntityConfiguration = EntityConfigLoader::createEntityConfigFromEntityFullName($field->getEntityType());
                                if ($subEntityConfiguration->hasField($nodes[$i])) {
                                    $field = $subEntityConfiguration->getField($nodes[$i]);
                                } else {
                                    throw new EntityNotFoundException("Field {$nodes[$i]} not found on {$subEntityConfiguration} entity");
                                }
                            }
                            $fieldName = implode('_', $nodes);
                            $this->addFilterField($builder, $field, $fieldName);
                        } else {
                            throw new EntityNotFoundException("Field {$nodes[0]} not found on {$entityConfiguration} entity");
                        }
                    } else {
                        $this->addTextFilter($builder, $fieldName);
                    }
                }
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param EntityField $field
     * @param string $fieldName
     */
    protected function addFilterField(FormBuilderInterface $builder, EntityField $field, string $fieldName): void
    {
        if ($field->isNativeType()) {
            $method = self::convertEntityNativeTypeToFormFieldMethod($field->getType());
            $this->$method($builder, $fieldName);
        } else {
            $this->addEntityFilter($builder, $fieldName, $field->getEntityType());
        }
    }

    /**
     * @param $type
     * @return string
     */
    protected static function convertEntityNativeTypeToFormFieldMethod($type): string
    {
        switch (strtolower($type)) {
            case 'integer':
                return 'addIntegerFilter';
            case 'bool':
            case 'boolean':
                return 'addBoolFilter';
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

    /**
     * @param FormBuilderInterface $builder
     * @param string $name
     * @return FormBuilderInterface
     */
    protected function addTextFilter(FormBuilderInterface $builder, string $name): FormBuilderInterface
    {
        $builder->add(
            $name,
            TextType::class,
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

    /**
     * @param FormBuilderInterface $builder
     * @param string $name
     * @return FormBuilderInterface
     */
    protected function addIntegerFilter(FormBuilderInterface $builder, string $name): FormBuilderInterface
    {
        $builder->add($name, IntegerType::class, ['required' => false,]);
        $builder->add("{$name}__min", IntegerType::class, ['required' => false]);
        $builder->add("{$name}__max", IntegerType::class, ['required' => false]);

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $name
     * @return FormBuilderInterface
     */
    protected function addBoolFilter(FormBuilderInterface $builder, string $name): FormBuilderInterface
    {
        $builder->add($name, IntegerType::class, ['required' => false, 'constraints' => [new Assert\Choice([0,1])]]);

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $name
     * @return FormBuilderInterface
     */
    protected function addNumberFilter(FormBuilderInterface $builder, string $name): FormBuilderInterface
    {
        $builder->add($name, NumberType::class, ['required' => false,]);
        $builder->add("{$name}__min", NumberType::class, ['required' => false,]);
        $builder->add("{$name}__max", NumberType::class, ['required' => false,]);

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $name
     * @return FormBuilderInterface
     */
    protected function addDateFilter(FormBuilderInterface $builder, string $name): FormBuilderInterface
    {
        $builder->add($name, DateType::class, ['required' => false,]);
        $builder->add("{$name}__min", DateType::class, ['required' => false, 'widget' => 'single_text', 'format' => 'yyyy-MM-DD']);
        $builder->add("{$name}__max", DateType::class, ['required' => false, 'widget' => 'single_text', 'format' => 'yyyy-MM-DD']);

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $name
     * @return FormBuilderInterface
     */
    protected function addDateTimeFilter(FormBuilderInterface $builder, string $name): FormBuilderInterface
    {
        $builder->add($name, DateTimeType::class, ['required' => false,]);
        $builder->add("{$name}__min", DateTimeType::class, ['required' => false, 'widget' => 'single_text']);
        $builder->add("{$name}__max", DateTimeType::class, ['required' => false, 'widget' => 'single_text']);

        return $builder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $name
     * @param $fieldEntityClass
     * @return FormBuilderInterface
     */
    protected function addEntityFilter(FormBuilderInterface $builder, string $name, $fieldEntityClass): FormBuilderInterface
    {
        $builder->add(
            $name,
            EntityType::class,
            [
                'required' => false,
                'class' => $fieldEntityClass,
            ]
        );

        return $builder;
    }
}
