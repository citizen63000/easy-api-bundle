<?php

namespace EasyApiBundle\Util\StringUtils;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class CaseConverter
{
    /**
     * @example my_variable_name => MyVariableName
     * @param string $str
     * @return string
     */
    public static function convertSnakeCaseToPascalCase(string $str): string
    {
        return static::convertDelimitedTextToPascalCase($str, '_');
    }

    /**
     * @example my-variable-name => MyVariableName
     * @param string $str
     * @return string
     */
    public static function convertSpinalCaseToPascalCase(string $str): string
    {
        return static::convertDelimitedTextToPascalCase($str, '-');
    }

    /**
     * @param string $str
     * @param string $delimiter
     * @return string
     */
    public static function convertDelimitedTextToPascalCase(string $str, string $delimiter): string
    {
        $words = explode($delimiter, $str);

        foreach ($words as $key => $word) {
            $words[$key] = ucfirst($word);
        }

        return implode('', $words);
    }


    /**
     * @example my_variable_name => myVariableName
     * @param string $str
     * @return string
     */
    public static function convertSnakeCaseToCamelCase(string $str): string
    {
        return lcfirst(static::convertSnakeCaseToPascalCase($str));
    }

    /**
     * @example my-variable-name
     * @param string $str
     *
     * @return string
     */
    public static function convertToSpinalCase(string $str)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1-$2', $str));
    }

    /**
     * @example MyVariableName => my-variable-name
     *
     * @param string $str
     *
     * @return string
     */
    public static function convertPascalCaseToSpinalCase(string $str)
    {
        return strtolower(preg_replace('/([a-z\d])([A-Z])/', '$1-$2', $str));
    }

    /**
     * @example my_variable_name
     *
     * @param string $str
     *
     * @return string
     */
    public static function convertToSnakeCase(string $str)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $str));
    }

    /**
     * @example myVariableName => my_variable_name
     * @param string $str
     * @return string
     */
    public static function convertCamelCaseToSnakeCase(string $str)
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($str);
    }
}