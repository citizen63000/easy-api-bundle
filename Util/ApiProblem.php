<?php

namespace EasyApiBundle\Util;

use \EasyApiCore\Util\ApiProblem as ApiProblemCore;
/**
 * A wrapper for holding data to be used for an application/problem+json response.
 *
 * @see https://tools.ietf.org/html/draft-nottingham-http-problem-06
 */
class ApiProblem extends ApiProblemCore
{
}
