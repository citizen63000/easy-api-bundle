<?php

namespace EasyApiBundle\Util\StringUtils;

use Symfony\Component\String\Inflector\EnglishInflector;

class Inflector
{
    public static function pluralize(string $word): string
    {
        $inflector = new EnglishInflector();
        $results = $inflector->pluralize($word);

        return $results[0] ?? $word;
    }
}
