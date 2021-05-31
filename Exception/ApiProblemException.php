<?php

namespace EasyApiBundle\Exception;

use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiProblemException extends HttpException
{
    /**
     * @var ApiProblem
     */
    private $apiProblem;

    /**
     * ApiProblemException constructor.
     *
     * @param ApiProblem      $apiProblem
     * @param \Exception|null $previous
     * @param array           $headers
     * @param int             $code
     */
    public function __construct(ApiProblem $apiProblem, \Exception $previous = null, array $headers = [], $code = 0)
    {
        $this->apiProblem = $apiProblem;
        parent::__construct($apiProblem->getStatusCode(), 'API Problem', $previous, $headers, $code);
    }

    /**
     * @return ApiProblem
     */
    public function getApiProblem(): ApiProblem
    {
        return $this->apiProblem;
    }
}