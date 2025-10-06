<?php

namespace EasyApiBundle\Form\Type\MediaUploader;

use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use EasyApiBundle\Form\Type\AbstractApiType;
use EasyApiCore\Util\File\MimeUtil;
use EasyApiCore\Util\String\CaseConverter;
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
                    'attr' => ['widget' => 'filename'],
                ]
            )
            ->add(
                'file',
                FileType::class,
                [
                    'label' => false,
                    'required' => false,
                    'constraints' => $this->getConstraints('file', $options),
                    'attr' => static::getFileAttr()
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

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var AbstractMedia $data */
            $data = $event->getData();
            if(null !== $data) {
                $data->setOriginalFilename($data->getFilename());
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
        $fileData = base64_decode(preg_replace('/^data:[a-zA-Z0-9\-\/\.\+]+;base64,/', '', $base64));
        (new Filesystem())->dumpFile($tmpFilePath, $fileData);
        // add extension (mime_content_type doesn't work with docx)
        $mimeType = finfo_buffer(finfo_open(), $fileData, FILEINFO_MIME_TYPE);

        // we create an uploaded file for form
        return new UploadedFile($tmpFilePath, $originalFileName, $mimeType, null, true);
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
        } elseif ('file' === $fieldName) {
            $assertOptions = static::getFileValidationOptions();
            if (!empty($assertOptions)) {
                $constraints[] = static::$dataClass::isImage() ? new Assert\Image($assertOptions) : new Assert\File($assertOptions);
            }
        }

        return $constraints;
    }

    /**
     * @return array
     */
    protected static function getFileValidationOptions(): array
    {
        $assertOptions = [];

        $mimeTypes = static::$dataClass::getMimeTypes();
        if (!empty($mimeTypes)) {
            $assertOptions['mimeTypes'] = $mimeTypes;
        }
        if ($maxSize = static::$dataClass::getMaxSize()) {
            $assertOptions['maxSize'] = $maxSize;
        }

        if (static::$dataClass::isImage()) {
            $optionNames = ['minWidth', 'maxWidth', 'minHeight', 'maxHeight', 'minRatio', 'maxRatio'];
            foreach ($optionNames as $optionName) {
                $method = 'get'.ucfirst($optionName);
                $value = static::$dataClass::$method();
                if (null !== $value) {
                    $assertOptions[$optionName] = $value;
                }
            }
        }

        return $assertOptions;
    }

    /**
     * @return array
     */
    protected static function getFileAttr(): array
    {
        $assertOptions = static::getFileValidationOptions();
        $attr = ['widget' => 'file'];

        foreach ($assertOptions as $key => $value) {
            $attr[CaseConverter::convertCamelCaseToSnakeCase($key)] = $value;
            if ('mimeTypes' === $key) {
                $attr['extensions'] = MimeUtil::getMimesExtentions($value);
            }
        }

        return $attr;
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
