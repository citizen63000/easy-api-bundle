<?php

namespace EasyApiBundle\Form\Type\MediaUploader;

use EasyApiBundle\Form\Type\AbstractApiType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://symfony.com/doc/3.4/form/dynamic_form_modification.html
 */
abstract class AbstractMediaType extends AbstractApiType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'filename',
                TextType::class,
                [
                    'label' => false,
                    'required' => $options['required'],
                    'constraints' => $this->getConstraints('filename', $options),
                    'attr' => [
                        'widget' => 'filename',
                    ],
                ]
            )
            ->add(
                'file',
                FileType::class,
                [
                    'label' => false,
                    'required' => false,
                    'constraints' => $this->getConstraints('file', $options),
                    'attr' => [
                        'widget' => 'file',
                    ],
                ]
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (null !== $data && isset($data['file']) && isset($data['filename'])) {
                $data['file'] = self::convertBase64ToUploadedFile($data['file'], $data['filename']);
                $event->setData($data);
            }
        });
    }

    /**
     * transform the base64 to uploadedFile
     *
     * @param string $base64
     * @param string $originalFileName
     * @return UploadedFile
     */
    private static function convertBase64ToUploadedFile(string $base64, string $originalFileName): UploadedFile
    {
        // Create a real file in temporary directory
        $tmpFilePath = '/tmp/'.md5(uniqid());
        $fileData = base64_decode(preg_replace('/^data:[a-zA-Z0-9\-\/\.]+;base64,/', '', $base64));
        (new Filesystem())->dumpFile($tmpFilePath, $fileData);
        // add extension (mime_content_type doesn't work with docx)
        $mimeType = finfo_buffer(finfo_open(), $fileData, FILEINFO_MIME_TYPE);

        // we create an uploaded file for form
        return new UploadedFile($tmpFilePath, $originalFileName, $mimeType, null, null, true);
    }

    /**
     * Return field constraints
     * @param $fieldName
     * @param array $options
     * @return array
     * @throws \Exception
     */
    private function getConstraints($fieldName, array $options): array
    {
        $groups = is_array($options['validation_groups']) ? $options['validation_groups'] : [];
        $constraints = [];

        if (isset($groups['NotBlank'])) {
            $constraints[] =  new Assert\NotBlank(['groups' => $this->getGroupsForField($fieldName, $groups['NotBlank'])]);
        }

        if (isset($groups['Blank'])) {
            $constraints[] =  new Assert\Blank(['groups' => $this->getGroupsForField($fieldName, $groups['Blank'])]);
        }

        // form constraints passed in parent form
        if ('filename' === $fieldName && is_array($options['constraints'])) {
            foreach ($options['constraints'] as $constraint) {
                if (is_string($constraint)) {
                    $constraints[] = new $constraint();
                } elseif (is_object($constraint)) {
                    $constraints[] = $constraint;
                }
            }
        }

        return $constraints;
    }


    /**
     * @param string $fieldName
     * @param array $groups
     * @return array|mixed|string
     * @throws \Exception
     */
    private function getGroupsForField(string $fieldName, array $groups)
    {
        if (count($groups)) {
            if (isset($groups[$fieldName])) {
                return $groups[$fieldName];
            }

            $fields = ['filename', 'file'];
            foreach ($groups as $key => $group) {
                if (!in_array($key, $fields, true) && !is_int($key)) {
                    throw new \Exception("{$this->getBlockPrefix()}.options.validation_groups.{$key}.invalid");
                }
            }

            return $groups;
        }

        return ['Default'];
    }
}
