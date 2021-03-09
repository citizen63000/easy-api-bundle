<?php

namespace EasyApiBundle\Form\Type\MediaUploader;

use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use EasyApiBundle\Form\Type\AbstractApiType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://symfony.com/doc/3.4/form/dynamic_form_modification.html
 *
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
                    'required' => true,
                    'constraints' => $this->getConstraints('filename', $options),
                    'attr' => [
                        'widget' => 'filename',
                    ],
                ]
            )
            ->add(
                'file',
                TextType::class,
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
        // use event on entity populate instead
        $builder->get('file')
            ->addModelTransformer(new CallbackTransformer(
                function ($uploadedFile) {
                    return $uploadedFile;
                }
                , function ($base64) {
                    return self::convertBase64ToUploadedFile($base64);
                }
            ))
        ;
    }

    /**
     * @param string $base64
     * @return null|UploadedFile
     */
    private static function convertBase64ToUploadedFile(?string $base64): ?UploadedFile
    {
        // transform the base64 to uploadedFile
        if(!empty($base64)) {

            // Create a real file in temporary directory
            $fileName = md5(uniqid());
            $tmpFilePath = "/tmp/{$fileName}";
            (new Filesystem())->dumpFile($tmpFilePath, base64_decode($base64));
            // add extension
            $mimeType = mime_content_type($tmpFilePath);
            $extension = AbstractMedia::mimeToExtension($mimeType);
            $tmpFilePathWithExtension =  "{$tmpFilePath}.{$extension}";
            rename($tmpFilePath, $tmpFilePathWithExtension);

            // we create an uploaded file for form
            return new UploadedFile($tmpFilePathWithExtension, "{$fileName}.{$extension}", $mimeType, null, null, true);
        }

        return null;
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

        if(isset($groups['NotBlank'])) {
            $constraints[] =  new Assert\NotBlank(['groups' => $this->getGroupsForField($fieldName, $groups['NotBlank'])]);
        }

        if(isset($groups['Blank'])) {
            $constraints[] =  new Assert\Blank(['groups' => $this->getGroupsForField($fieldName, $groups['Blank'])]);
        }

        return count($constraints) ? $constraints : [ new Assert\NotBlank(['groups' => 'Default']) ];
    }


    /**
     * @param string $fieldName
     * @param array $groups
     * @return array|mixed|string
     * @throws \Exception
     */
    private function getGroupsForField(string $fieldName, array $groups)
    {
        if(count($groups)) {

            if(isset($groups[$fieldName])) {
                return $groups[$fieldName];
            }

            $fields = ['filename', 'file'];
            foreach ($groups as $key => $group) {
                if(!in_array($key, $fields, true) && !is_int($key)) {
                    throw new \Exception("{$this->getBlockPrefix()}.options.validation_groups.{$key}.invalid");
                }
            }

            return $groups;
        }

        return ['Default'];
    }
}
