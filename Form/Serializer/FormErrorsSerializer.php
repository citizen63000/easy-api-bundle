<?php

namespace EasyApiBundle\Form\Serializer;

use EasyApiBundle\Util\ApiProblem;
use Monolog\Logger;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class FormErrorsSerializer.
 */
class FormErrorsSerializer
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * FormErrorsSerializer constructor.
     *
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param FormInterface $form
     * @param bool          $flatArray
     * @param bool          $addFormName
     * @param string        $glueKeys
     *
     * @return array
     */
    public function serializeFormErrors(FormInterface $form, $flatArray = false, $addFormName = false, $glueKeys = '_')
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $cause = $error->getCause();

            if ($cause instanceof ConstraintViolation) {
                $causeEx = $cause->getCause();
                $message = (null !== $causeEx && !is_array($causeEx)) ? $causeEx->getMessage() : $cause->getMessage();

                // path adaptation to code error format
                $path = preg_replace('/children\[(\w+)\]/i', '${1}', $cause->getPropertyPath());

                // Replacement of "data." prefix in case of type inheritance by the name of the form
                $path = preg_replace('/^data./i', "{$form->getName()}.", $path);

                if (!empty($path)) {
                    $errors[] = "{$path}.invalid : {$message}";
                } else {
                    $invalidFields = $cause->getInvalidValue();
                    if (!empty($invalidFields)) {
                        foreach ($invalidFields as $fieldName => $value) {
                            $errors[] = "{$message} : {$fieldName}";
                        }
                    } else {
                        $errors[] = $message;
                    }
                }

                if ($causeEx instanceof \Exception) {
                    $this->logger->addWarning(
                        sprintf('%s at line %d : %s', $causeEx->getFile(), $causeEx->getLine(), $causeEx->getMessage()),
                        ['form' => $form->getName()]
                    );
                }
            } else {
                $errors[] = $error->getMessage();
            }
        }

        $errors = array_merge($errors, $this->serialize($form));

        if ($flatArray) {
            $errors = $this->arrayFlatten(
                $errors,
                $glueKeys,
                ($addFormName ? $form->getName() : '')
            );
        }

        return $errors;
    }

    /**
     * @param Form $form
     *
     * @return array
     */
    private function serialize(Form $form)
    {
        $localErrors = [];
        foreach ($form->getIterator() as $key => $child) {
            foreach ($child->getErrors() as $error) {
                $formName = static::getCompletePathFormName($form);

                if ('This value should not be blank.' === $error->getMessage()) {
                    $localErrors[$key] = sprintf(ApiProblem::ENTITY_FIELD_REQUIRED, $formName, $key);
                } elseif ('This value should not be null.' === $error->getMessage()) {
                    $localErrors[$key] = sprintf(ApiProblem::ENTITY_FIELD_REQUIRED, $formName, $key);
                } else {
                    if (false === strpos($error->getMessage(), 'already_exists')
                        && false === strpos($error->getMessage(), 'too_long')
                        && false === strpos($error->getMessage(), 'malformed')) {
                        $localErrors[$key] = sprintf(ApiProblem::ENTITY_FIELD_INVALID, $formName, $key);
                    } else {
                        $localErrors[$key] = sprintf($error->getMessage(), $formName, $key);
                    }
                }
            }

            if (($child instanceof Form) && (count($child->getIterator()) > 0)) {
                $localErrors[$key] = $this->serialize($child);
            }
        }

        return $localErrors;
    }

    /**
     * @param Form $form
     *
     * @return string
     */
    private static function getCompletePathFormName(Form $form)
    {
        $formName = $form->getName();

        while (null !== $form->getParent()) {
            $formName = "{$form->getParent()->getName()}.$formName";
            $form = $form->getParent();
        }

        return $formName;
    }

    /**
     * @param $array
     * @param string $separator
     * @param string $flattenedKey
     *
     * @return array
     */
    private function arrayFlatten($array, $separator = '_', $flattenedKey = '')
    {
        $flattenedArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattenedArray = array_merge(
                    $flattenedArray,
                    $this->arrayFlatten(
                        $value,
                        $separator,
                        (strlen($flattenedKey) > 0 ? $flattenedKey.$separator : '').$key
                    )
                );
            } else {
                $flattenedArray[(strlen($flattenedKey) > 0 ? $flattenedKey.$separator : '').$key] = $value;
            }
        }

        return $flattenedArray;
    }
}
