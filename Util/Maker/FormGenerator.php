<?php

namespace EasyApiBundle\Util\Maker;

use EasyApiBundle\Model\Maker\EntityConfiguration;
use EasyApiBundle\Model\Maker\EntityField;

class FormGenerator extends AbstractGenerator
{
    protected static $templatesDirectory = '/doctrine';
    /**
     * @param string $bundle
     * @param string $context
     * @param string $entityName
     * @param string|null $parent
     * @param bool $dumpExistingFiles
     * @return string
     */
    public function generate(string $bundle, string $context, string $entityName, string $parent = null, bool $dumpExistingFiles = false)
    {
        $this->config = $this->loadEntityConfig($entityName, $bundle, $context);
        $destinationDir = $this->getFormDirectoryPath();
        $filename = "{$this->config->getEntityName()}Type.php";

        // generate file
        $fileContent = $this->getContainer()->get('templating')->render(
            $this->getTemplatePath('form.php.twig'),
            $this->generateContent($parent)
        );

        return $this->writeFile($destinationDir, $filename, $fileContent, $dumpExistingFiles, true);
    }

    /**
     * @return string
     */
    protected function getFormDirectoryPath()
    {
        $context = str_replace('\\', '/', $this->config->getContextName());

        return 'src/'.$this->config->getBundleName()."/Form/Type/{$context}";
    }

    /**
     * @param string|null $parent
     * @return array
     */
    protected function generateContent(?string $parent)
    {
        $bundle = $this->config->getBundleName();
        $context = str_replace('/', '\\', $this->config->getContextName());
        $content = ['fields' => [], 'uses' => [$this->config->getFullName() => $this->config->getFullName()], '__construct' => ['fields' => []]];
        $parentConfig = $this->getConfig()->getParentEntity();

        if (null === $parent && $parentConfig && $parentConfig->getEntityName() !== 'AbstractBaseEntity') {
            $content['uses'][] = "{$this->config->getBundleName()}\Form\Type\\".$parentConfig->getContextName().'\\'.$parentConfig->getEntityName().'Type';
            $content['parent'] = $parentConfig->getEntityName();
        } elseif(null !== $parent) {
            $content['uses'][] = $parent;
            $content['parent'] = $parent;
        } else {
            $content['uses'][] = $this->container->getParameter('easy_api.inheritance.form');
            $content['parent'] = EntityConfiguration::getEntityNameFromNamespace($this->container->getParameter('easy_api.inheritance.form'));
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

                if($field->isReferential() && !in_array('Doctrine\ORM\EntityRepository', $content['uses'])) {
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
