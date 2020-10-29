<?php

namespace EasyApiBundle\Util\StringUtils;

use Doctrine\Common\Inflector\Inflector as SfInflector;

class Inflector
{
    /**
     * @param string $word
     */
    public static function pluralize(string $word)
    {
        return SfInflector::pluralize($word);
    }
}