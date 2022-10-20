<?php

namespace EasyApiBundle\Services\ApiDoc;

use EasyApiBundle\Annotation\GetFormFilterParameter;
use EasyApiBundle\Annotation\GetFormParameter;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Schema;
use Ramsey\Uuid\Uuid;
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
        return ['entityClass' => $annotation->entityClass, 'fields' => $annotation->fields, 'sortFields' => $annotation->sortFields];
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
            'parameter' => (string) Uuid::uuid1(),
            'required' => $field->getConfig()->getRequired(),
//            'schema' => new Schema(['type' => $type]),
            'description' => $description,
        ]);
    }
}
