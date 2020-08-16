<?php

namespace EasyApiBundle\Services\ApiDoc;

use ReflectionMethod;
use RuntimeException;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Routing\Route;

use EasyApiBundle\Annotation\GetFormParameter;

class GetFormParameterRouteDescriber
{
    use RouteDescriberTrait;

    /** @var Reader */
    private $annotationReader;

    /** @var FormFactoryInterface */
    private $formFactory;

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
            if ($annotation instanceof GetFormParameter) {
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
        $controller = $route->getDefault('_controller');

        if(0 === strpos($annotation->type, 'static::')) {
            $type = constant(str_replace('static', explode('::', $controller)[0], $annotation->type));
        } else {
            $type = $annotation->type;
        }

        $filterForm = $this->formFactory->create($type);
        foreach ($this->getOperations($api, $route) as $operation) {

            foreach ($filterForm->all() as $field) {

                if ($field->count() === 0) {
                    $parameter = new Parameter([
                        'in' => 'query',
                        'name' => $field->getName(),
                        'required' => $field->getConfig()->getRequired(),
                        'type' => $this->convertFormTypeToParameterType($field->getConfig()->getType()->getInnerType()),
                    ]);

                    $operation->getParameters()->add($parameter);
                } else {
                    // @TODO recursive parameters
                }
            }
        }
    }

    /**
     * @param FormTypeInterface $formType
     * @return string
     */
    protected function convertFormTypeToParameterType(FormTypeInterface $formType): string
    {
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

        return 'string';
    }
}