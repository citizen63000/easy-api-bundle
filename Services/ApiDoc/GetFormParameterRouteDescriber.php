<?php

namespace EasyApiBundle\Services\ApiDoc;

use Doctrine\Common\Annotations\Reader;
use EasyApiBundle\Annotation\GetFormParameter;
use EasyApiBundle\Util\Entity\EntityConfigLoader;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use Ramsey\Uuid\Uuid;
use ReflectionMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Route;

class GetFormParameterRouteDescriber
{
    use RouteDescriberTrait;

    private Reader $annotationReader;

    protected FormFactoryInterface $formFactory;

    protected const annotationClass = GetFormParameter::class;

    public function __construct(Reader $annotationReader, FormFactoryInterface $formFactory)
    {
        $this->annotationReader = $annotationReader;
        $this->formFactory = $formFactory;
    }

    public function describe(OpenApi $api, Route $route, ReflectionMethod $reflectionMethod): void
    {
        $annotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);

        foreach ($annotations as $annotation) {
            if (static::annotationClass === get_class($annotation)) {
                $this->addParameters($api, $route, $annotation);
            }
        }
    }

    private function addParameters(OpenApi $api, Route $route, GetFormParameter $annotation): void
    {
        $controllerName = $route->getDefault('_controller');
        $filterForm = $this->formFactory->create($annotation->type, null, static::getFormOptions($controllerName, $annotation));

        foreach ($this->getOperations($api, $route) as $operation) {
            $operation->parameters = (Generator::UNDEFINED == $operation->parameters) ? [] : $operation->parameters;
            foreach ($filterForm->all() as $field) {
                $operation->parameters[] = $this->createParameter($field);
            }
        }
    }

    protected function createParameter(FormInterface $field, $description = ''): Parameter
    {
        return new Parameter([
            'in' => 'query',
            'name' => $field->getName(),
            'parameter' => (string) Uuid::uuid1(),
            'required' => $field->getConfig()->getRequired(),
//            'schema' => new Schema(['type' => $this->convertFormTypeToParameterType($field->getConfig())]),
            'description' => $description,
        ]);
    }

    protected static function getFormOptions(string $controllerName, GetFormParameter $annotation): array
    {
        return [];
    }

    protected function convertFormTypeToParameterType(FormConfigBuilderInterface $config): string
    {
        $formType = $config->getType()->getInnerType();

        if ($formType instanceof IntegerType) {
            return 'integer';
        }

        if ($formType instanceof NumberType) {
            return 'number';
        }

        if ($formType instanceof CheckboxType) {
            return 'boolean';
        }

        if ($formType instanceof DateType) {
            return 'date';
        }

        if ($formType instanceof DateTimeType) {
            return 'dateTime';
        }

        if ($formType instanceof EntityType) {
            $classConfig = EntityConfigLoader::createEntityConfigFromEntityFullName($config->getOption('class'));
            foreach ($classConfig->getFields() as $field) {
                if ($field->isPrimary()) {
                    return $field->getType();
                }
            }
        }

        return 'string';
    }
}
