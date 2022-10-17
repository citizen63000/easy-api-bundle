<?php

namespace EasyApiBundle\Services\ApiDoc;

use EasyApiBundle\Annotation\GetFormFilterParameter;
use EasyApiBundle\Annotation\GetFormParameter;
use OpenApi\Annotations\Parameter;
use Symfony\Component\Form\FormInterface;

class GetFormFilterParameterRouteDescriber extends GetFormParameterRouteDescriber
{
    protected const annotationClass = GetFormFilterParameter::class;

    /**
     * @param GetFormFilterParameter $annotation
     *
     * @return array
     */
    protected static function getFormOptions(string $controllerName, GetFormParameter $annotation)
    {
        if (0 === strpos($annotation->entityClass, 'static::')) {
            $entityClass = static::getConstValue($controllerName, $annotation->entityClass);
        } else {
            $entityClass = $annotation->entityClass;
        }

        if (1 === count($annotation->fields) && 0 === strpos($annotation->fields[0], 'static::')) {
            $fields = static::getConstValue($controllerName, $annotation->fields);
        } else {
            $fields = $annotation->fields;
        }

        if (1 === count($annotation->sortFields) && 0 === strpos($annotation->sortFields[0], 'static::')) {
            $sortFields = static::getConstValue($controllerName, $annotation->fields);
        } else {
            $sortFields = $annotation->sortFields;
        }

        return ['entityClass' => $entityClass, 'fields' => $fields, 'sortFields' => $sortFields];
    }

    /**
     * @return Parameter
     */
    protected function createParameter(FormInterface $field)
    {
        $description = '';
        $type = $this->convertFormTypeToParameterType($field->getConfig());

        if ('sort' === $field->getName()) {
            $description = 'field1:asc,field2:desc';
        } elseif ('dateTime' === $type) {
            $description = 'yyyy-mm-dd h:i:s';
        } elseif ('date' === $type) {
            $description = 'yyyy-mm-dd';
        }

        return new Parameter([
            'in' => 'query',
            'name' => $field->getName(),
            'required' => $field->getConfig()->getRequired(),
            'type' => $type,
            'description' => $description,
        ]);
    }
}
