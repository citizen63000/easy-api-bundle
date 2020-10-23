<?php

namespace EasyApiBundle\Util\Tests\crud\functions;

use EasyApiBundle\Util\Forms\FormSerializer;

trait crudFunctionsTestTrait
{
    protected static $createActionType = 'Create';
    protected static $updateActionType = 'Update';

    /**
     * @return false|string|string[]
     */
    protected function getCurrentDir()
    {
        try {
            $rc = new \ReflectionClass($this);
            return str_replace('/'.$rc->getShortName().'.php', '', $rc->getFilename());
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param string $filename
     * @param string $type Get|GetList|Create|Update
     * @param array $result result in json
     * @param bool $dateProtection
     * @return array
     */
    protected function getExpectedResponse(string $filename, string $type, array $result, bool $dateProtection = false): array
    {
        $dir = $this->getCurrentDir()."/Responses/{$type}";
        $filePath = "{$dir}/{$filename}";

        if(!file_exists($filePath)) {
            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            // created_at / updated_at fields
            if($dateProtection) {
                if(self::$createActionType === $type) {
                    if(array_key_exists('createdAt', $result)) {
                        $result['createdAt'] = '\assertDateTime()';
                    }
                }
                if(array_key_exists('updatedAt', $result)) {
                    $result['updatedAt'] = '\assertDateTime()';
                }
            }

            file_put_contents($filePath, self::generateJson($result));
        }

        return json_decode(file_get_contents($filePath), true);
    }

    /**
     * @param string $filename
     * @param string $type Create|Update
     * @param string|null $defaultContent
     * @return array
     * @throws \Exception
     */
    protected function getDataSent(string $filename, string $type, string $defaultContent = null): array
    {
        $dir = $this->getCurrentDir()."/DataSent/{$type}";
        $filePath = "{$dir}/{$filename}";

        if(!file_exists($filePath)) {
            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            if(null === $defaultContent) {
                $defaultContent = static::generateDataSentDefault($type);
            }

            file_put_contents($filePath, self::generateJson($defaultContent));
        }

        if($json = json_decode(file_get_contents($filePath), true)) {
            return $json;
        }

        throw new \Exception("Invalid json in file {$filename}");
    }

    /**
     * @param $content
     * @return false|string|string[]
     */
    protected static function generateJson($content)
    {
        $json = json_encode($content, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_FORCE_OBJECT|JSON_PRETTY_PRINT);

        return str_replace('{}', '[]', $json);
    }

    /**
     * @param string $type
     * @return array
     */
    protected static function generateDataSentDefault(string $type): array
    {
        $router = static::$container->get('router');

        if($type === self::$createActionType) {
            $route = $router->getRouteCollection()->get(self::getCreateRouteName());
            $controllerAction = $route->getDefault('_controller');
            $controllerClassName = explode('::', $controllerAction)[0];
            $formClass = constant("{$controllerClassName}::entityCreateTypeClass");
        } else {
            $route = $router->getRouteCollection()->get(self::getUpdateRouteName());
            $controllerAction = $route->getDefault('_controller');
            $controllerClassName = explode('::', $controllerAction)[0];
            $formClass = constant("{$controllerClassName}::entityUpdateTypeClass");
        }

        $describer = new FormSerializer(
            static::$container->get('form.factory'),
            static::$container->get('router'),
            static::$container->get('doctrine')
        );

        $normalizedForm = $describer->normalize(static::$container->get('form.factory')->create($formClass));

        $fields = [];
        foreach ($normalizedForm->getFields() as $field) {

            $defaultValue = '';

            switch ($field->getType()) {
                case 'string':
                    if('date' === $field->getFormat()) {
                        $defaultValue = (new \DateTime())->format('Y-m-d');
                    } elseif('date-time' === $field->getFormat()) {
                        $defaultValue = (new \DateTime())->format('Y-m-d h:i:s');
                    } else {
                        $defaultValue = 'string';
                    }
                    break;
                case 'text':
                    $defaultValue = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt';
                    break;
                case 'boolean':
                    $defaultValue = 1;
                    break;
                case 'integer':
                    $defaultValue = random_int(0, 5000);
                    break;
                case 'number':
                    $defaultValue = random_int(0, 5000);
                    break;
                case 'array':
                    $defaultValue = [];
                    break;
                case 'entity':
                    $defaultValue = 1;
                    break;
            }

            $fields[$field->getName()] = $defaultValue;
        }

        return $fields;
    }

    /**
     * @return string
     */
    protected static function getGetRouteName()
    {
        return static::baseRouteName.'_get';
    }

    /**
     * @return string
     */
    protected static function getGetListRouteName()
    {
        return static::baseRouteName.'_list';
    }

    /**
     * @return string
     */
    protected static function getCreateRouteName()
    {
        return static::baseRouteName.'_create';
    }

    /**
     * @return string
     */
    protected static function getUpdateRouteName()
    {
        return static::baseRouteName.'_update';
    }

    /**
     * @return string
     */
    protected static function getDeleteRouteName()
    {
        return static::baseRouteName.'_delete';
    }

    /**
     * @return string
     */
    protected static function getDescribeFormRouteName()
    {
        return static::baseRouteName.'_describe_form';
    }

    /**
     * @return string
     */
    protected static function getDataClassShortName()
    {
        return lcfirst(substr(static::entityClass, strrpos(static::entityClass, '\\') + 1));
    }
}
