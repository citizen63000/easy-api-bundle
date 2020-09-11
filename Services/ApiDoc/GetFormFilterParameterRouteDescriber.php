<?php

namespace EasyApiBundle\Services\ApiDoc;

use EasyApiBundle\Annotation\GetFormFilterParameter;
use EasyApiBundle\Annotation\GetFormParameter;

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
        if(0 === strpos($annotation->type, 'static::')) {
            $entityClass = static::getConstValue($controllerName, $annotation->entityClass);
        } else {
            $entityClass = $annotation->entityClass;
        }

        if(0 === strpos($annotation->type, 'static::')) {
            $fields = static::getConstValue($controllerName, $annotation->fields);
        } else {
            $fields = $annotation->fields;
        }
        return ['entityClass' => $entityClass, 'fields' => $fields];
    }
}