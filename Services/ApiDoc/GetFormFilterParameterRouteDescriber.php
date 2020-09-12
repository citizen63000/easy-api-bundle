<?php

namespace EasyApiBundle\Services\ApiDoc;

use EasyApiBundle\Annotation\GetFormFilterParameter;
use EasyApiBundle\Annotation\GetFormParameter;
use EXSyst\Component\Swagger\Parameter;
use Symfony\Component\Form\FormInterface;

class GetFormFilterParameterRouteDescriber extends GetFormParameterRouteDescriber
{
    protected const annotationClass = GetFormFilterParameter::class;

    /**
     * @param string $controllerName
     * @param GetFormFilterParameter $annotation
     * @return array
     */
    protected static function getFormOptions(string $controllerName, GetFormParameter $annotation)
    {
        if(0 === strpos($annotation->entityClass, 'static::')) {
            $entityClass = static::getConstValue($controllerName, $annotation->entityClass);
        } else {
            $entityClass = $annotation->entityClass;
        }

        if(count($annotation->fields) === 1 && 0 === strpos($annotation->fields[0], 'static::')) {
            $fields = static::getConstValue($controllerName, $annotation->fields);
        } else {
            $fields = $annotation->fields;
        }

        if(count($annotation->sortFields) === 1 && 0 === strpos($annotation->sortFields[0], 'static::')) {
            $sortFields = static::getConstValue($controllerName, $annotation->fields);
        } else {
            $sortFields = $annotation->sortFields;
        }

        return ['entityClass' => $entityClass, 'fields' => $fields, 'sortFields' => $sortFields];
    }
    /**
     * @param FormInterface $field
     * @return Parameter
     */
    protected function createParameter(FormInterface $field)
    {
        $description = '';
        $type = $this->convertFormTypeToParameterType($field->getConfig());

        if($field->getName() === 'sort') {
            $description = 'field1:asc,field2:desc';
        } elseif ($type === 'dateTime') {
            $description = 'yyyy-mm-dd h:i:s';
        } elseif ($type === 'date') {
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