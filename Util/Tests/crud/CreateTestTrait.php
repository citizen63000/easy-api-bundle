<?php

namespace EasyApiBundle\Util\Tests\crud;

use EasyApiBundle\Util\Tests\crud\functions\CreateTestFunctionsTrait;
use Symfony\Component\HttpFoundation\Response;

trait CreateTestTrait
{
    use CreateTestFunctionsTrait;

    /**
     * POST - create with all fields.
     */
    public function testCreateWithAllFields(): void
    {
        $this->doTestCreate('createWithAllFields.json');
    }

    /**
     * POST - create full fields.
     */
    public function testCreateWithOnlyRequiredFields(): void
    {
        $this->doTestCreate('createWithOnlyRequiredFields.json');
    }

    /**
     * POST - create full fields.
     */
    public function testCreateWithoutRequiredFields(): void
    {
        $requiredFields = static::getRequiredFields();

        if(count($requiredFields) > 0) {
            foreach ($requiredFields as $requiredField) {

                $data = $this->getDataSent('createWithAllFields.json', 'Create');
                unset($data[$requiredField]);

                $apiOutput = self::httpPost(['name' => static::getCreateRouteName()], $data);

                $result = $apiOutput->getData();
                static::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $apiOutput->getStatusCode());
                static::assertEquals(['errors' => ['core.error.'.static::getDataClassShortName().".{$requiredField}.required"]], $result);
            }
        } else {
            self::markTestSkipped('Cannot be tested : no required fields defined, please set static var requiredFields with required fields if necessary.');
        }
    }

    /**
     * POST - Error case - 401 - Without authentication.
     */
    public function testCreateWithoutAuthentication(): void
    {
        $this->doTestCreateWithoutAuthentication();
    }

    /**
     * POST - Error case - 403 - Missing create right.
     */
    public function testCreateWithoutRightC403(): void
    {
        $this->doTestCreateWithoutRight();
    }
}
