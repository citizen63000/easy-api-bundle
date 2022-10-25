<?php

namespace EasyApiBundle\Services\ApiDoc;

use EasyApiBundle\Annotation\GetFormFilterParameter;
use EasyApiBundle\Annotation\GetFormParameter;
use OpenApi\Annotations\Parameter;
use Symfony\Component\Form\FormInterface;

class GetFormFilterParameterRouteDescriber extends GetFormParameterRouteDescriber
{
    protected const annotationClass = GetFormFilterParameter::class;

    protected static function getFormOptions(string $controllerName, GetFormParameter $annotation): array
    {
        return ['entityClass' => $annotation->entityClass, 'fields' => $annotation->fields, 'sortFields' => $annotation->sortFields];
    }

    protected function createParameter(FormInterface $field, $description = ''): Parameter
    {
        $type = $this->convertFormTypeToParameterType($field->getConfig());

        if ('sort' === $field->getName()) {
            $description = 'field1:asc,field2:desc';
        } elseif ('dateTime' === $type) {
            $description = 'yyyy-mm-dd h:i:s';
        } elseif ('date' === $type) {
            $description = 'yyyy-mm-dd';
        }

        parent::createParameter($field, $description);
    }
}
