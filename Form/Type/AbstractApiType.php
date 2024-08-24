<?php

namespace EasyApiBundle\Form\Type;

use EasyApiBundle\Form\Type\MediaUploader\AbstractMediaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use \EasyApiCore\Form\Type\AbstractApiType as AbstractApiCoreType;

abstract class AbstractApiType extends AbstractApiCoreType
{
    private $valuesToSetNull = [];


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'managePreSubmitAbstractMediaFiles']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'manageAbstractMediaFiles']);
    }

    /**
     * Set empty AbstractMedia entity to null in media container entity to delete it
     * Ex : myFile: { filename: null, file: null} => myFile: null
     * @param FormEvent $event
     */
    public function managePreSubmitAbstractMediaFiles(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (!$data) {
            return;
        }

        foreach ($form as $name => $child) {
            $config = $child->getConfig();
            $type = $config->getType();
            if ($type->getInnerType() instanceof AbstractMediaType) {
                if (array_key_exists($name, $data)) {
                    if (is_array($data[$name])) {
                        $filename = $data[$name]['filename'] ?? null;
                        $file = $data[$name]['file'] ?? null;
                        if (null === $filename && null === $file) {
                            $data[$name] = null;
                            $this->valuesToSetNull[] = $name;
                        }
                    } elseif (null === $data[$name]) {
                        $this->valuesToSetNull[] = $name;
                    }
                }
            }
        }

        $event->setData($data);
    }

    /**
     * Set null data fields to null in entity
     * Ex : myFile: { filename: null, file: null} => myFile: null
     * @param FormEvent $event
     */
    public function manageAbstractMediaFiles(FormEvent $event): void
    {
        $data = $event->getData();

        // set media entity to null on container entity
        foreach ($this->valuesToSetNull as $fieldName) {
            $data->{'set'.ucfirst($fieldName)}(null);
            $event->setData($data);
        }

        // clean for the next form
        $this->valuesToSetNull = [];
    }
}
