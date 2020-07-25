<?php


namespace EasyApiBundle\Util\Maker;


use EasyApiBundle\Model\Maker\EntityConfiguration;
use EasyApiBundle\Model\Maker\EntityField;

class FormGenerator extends AbstractGenerator
{
    /**
     * @param $bundle string
     * @param $context string
     * @param $entityName string
     * @param $parent string
     * @param $dumpExistingFiles boolean
     *
     * @return string
     */
    public function generate(string $bundle, string $context, string $entityName, string $parent = null, bool $dumpExistingFiles = false)
    {
        $this->config = $this->loadEntityConfig($entityName, $bundle, $context);
        $destinationDir = "src/{$this->config->getBundleName()}/Form/Type/".($this->config->getContextName() ? $this->config->getContextName().'/' : '');
        $filename = "{$this->config->getEntityName()}Type.php";

        // generate file
        $fileContent = $this->getContainer()->get('templating')->render(
            '@EasyApiBundle/Resources/skeleton/doctrine/form.php.twig',
            $this->generateContent($parent)
        );

        return "{$this->container->getParameter('kernel.project_dir')}/".$this->writeFile($destinationDir, $filename, $fileContent, $dumpExistingFiles);
    }

    /**
     * @param string|null $parent
     * @return array
     */
    protected function generateContent(?string $parent)
    {
        $bundle = $this->config->getBundleName();
        $context = str_replace('/', '\\', $this->config->getContextName());
        $content = ['fields' => [], 'uses' => [], '__construct' => ['fields' => []]];

        if (null === $parent && $parentConfig = $this->getConfig()->getParentEntity()) {
            $content['uses'][] = "{$this->config->getBundleName()}\Form\Type\\".$parentConfig->getContextName().'\\'.$parentConfig->getEntityName().'Type';
            $content['parent'] = $parentConfig->getEntityName();
        } elseif(null !== $parent) {
            $content['uses'][] = $parent;
            $content['parent'] = $parent;
        } else {
            $content['uses'][] = 'EasyApiBundle\Form\Type\AbstractApiType';
            $content['parent'] = EntityConfiguration::getEntityNameFromNamespace('EasyApiBundle\Form\Type\AbstractApiType');
        }

        $content['namespace'] = "{$bundle}\\Form\\Type".(!empty($context) ? "\\{$context}" : '');
        $content['entityNamespace'] = $this->config->getNamespace();//"{$bundle}\\Entity".(!empty($context) ? "\\{$context}" : '');
        $content['classname'] = $this->config->getEntityName();
        $content['extend'] = $this->config->getEntityType();
        $content['block_prefix'] = strtolower($content['classname']);
        $content['error_context'] = strtolower("{$context}.{$content['classname']}");

        $fields = $this->config->getFields();
        foreach ($fields as $field) {
            if ('createdAt' !== $field->getName() && 'updatedAt' !== $field->getName() && !$field->isPrimary()) {
                $fieldDescription = [
                    'name' => $field->getName(),
                    'required' => $field->isRequired(),
                    'isReferential' => $field->isReferential(),
                    'originalType' => $field->getType(),
                    'type' => self::getFormTypeFromDoctrineType($field),
                    'relationType' => $field->getRelationType(),
                    'class' => !$field->isNativeType() ? $field->getEntityClassName() : null,
                ];

                // uses
                if ('EntityType' === $fieldDescription['type']) {
                    // Type for form
                    $content['uses'][$fieldDescription['type']] = 'Symfony\Bridge\Doctrine\Form\Type\EntityType';
                    // entity type
                    if ($field->getEntityNamespace()) {
                        $content['uses'][$field->getEntityType()] = $field->getEntityType();
                    } else {
                        $content['uses'][$field->getEntityType()] = $content['entityNamespace'].'\\'.$field->getEntityClassName();
                    }
                } else {
                    $content['uses'][$fieldDescription['type']] = 'Symfony\Component\Form\Extension\Core\Type\\'.$fieldDescription['type'];
                }

                $content['fields'][] = $fieldDescription;

                if($field->isReferential()) {
                    $content['uses'][] = 'Doctrine\ORM\EntityRepository';
                }
            }
        }

        return $content;
    }

    /**
     * @param EntityField $field
     *
     * @return string
     */
    private static function getFormTypeFromDoctrineType(EntityField $field)
    {
        if ($field->isNativeType()) {
            $type = $field->getType();

            $conversion = [
                'string' => 'TextType',
                'date' => 'DateType',
                'datetime' => 'DateType',
                'integer' => 'IntegerType',
                'float' => 'NumberType',
                'boolean' => 'ChoiceType',
            ];

            return $conversion[$type] ?? 'TextType';
        }

        $type = $field->getRelationType();

        $conversion = [
            'manyToOne' => 'EntityType',
            'oneToOne' => 'EntityType',
            'manyToMany' => 'EntityType',
            'oneToMany' => 'EntityType',
        ];

        return $conversion[$type] ?? 'EntityType';
    }
}