<?php

namespace EasyApiBundle\Services\ApiDoc;

use EasyApiBundle\Util\Maker\EntityConfigLoader;
use ReflectionMethod;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;

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

use EasyApiBundle\Annotation\GetFormParameter;

class GetFormParameterRouteDescriber
{
    use RouteDescriberTrait;

    /** @var Reader */
    private $annotationReader;

    /** @var FormFactoryInterface */
    protected $formFactory;

    protected const annotationClass = GetFormParameter::class;

    /**
     * GetFormParameterRouteDescriber constructor.
     * @param Reader $annotationReader
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(Reader $annotationReader, FormFactoryInterface $formFactory)
    {
        $this->annotationReader = $annotationReader;
        $this->formFactory      = $formFactory;
    }

    /**
     * @param Swagger $api
     * @param Route $route
     * @param ReflectionMethod $reflectionMethod
     */
    public function describe(Swagger $api, Route $route, ReflectionMethod $reflectionMethod): void
    {
        $annotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);

        foreach ($annotations as $annotation) {
            $annotationClass = static::annotationClass;
            if ($annotation instanceof $annotationClass) {
                $this->addParameters($api, $route, $annotation);
            }
        }
    }

    /**
     * @param Swagger $api
     * @param Route $route
     * @param GetFormParameter $annotation
     */
    private function addParameters(Swagger $api, Route $route, GetFormParameter $annotation): void
    {
        $controllerName = $route->getDefault('_controller');

        $filterForm = $this->createForm($controllerName, $annotation);
        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($filterForm->all() as $field) {
                $operation->getParameters()->add($this->createParameter($field));
            }
        }
    }

    /**
     * @param FormInterface $field
     * @return Parameter
     */
    protected function createParameter(FormInterface $field)
    {
        return new Parameter([
            'in' => 'query',
            'name' => $field->getName(),
            'required' => $field->getConfig()->getRequired(),
            'type' => $this->convertFormTypeToParameterType($field->getConfig()),
        ]);
    }

    /**
     * @param string $controllerName
     * @param GetFormParameter $annotation
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createForm(string $controllerName, GetFormParameter $annotation)
    {
        if(0 === strpos($annotation->type, 'static::')) {
            $type = static::getConstValue($controllerName, $annotation->type);
        } else {
            $type = $annotation->type;
        }

        return $this->formFactory->create($type, null, static::getFormOptions($controllerName, $annotation));
    }

    /**
     * @param string $controllerName
     * @param GetFormParameter $annotation
     * @return array
     */
    protected static function getFormOptions(string $controllerName, GetFormParameter $annotation)
    {
        return [];
    }

    /**
     * @param string $controllerName
     * @param string|array $varName
     * @return mixed
     */
    protected static function getConstValue(string $controllerName, $varName)
    {
        $varName = is_array($varName) && count($varName) ? $varName[0] : $varName;

        return constant(str_replace('static', explode('::', $controllerName)[0], $varName));
    }

    /**
     * @param FormConfigBuilderInterface $config
     * @return string
     */
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
                if($field->isPrimary()) {
                    return $field->getType();
                }
            }
        }

        return 'string';
    }
}