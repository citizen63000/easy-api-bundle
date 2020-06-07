<?php


namespace EasyApiBundle\Util\Tests;


class CommandOutput
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $data;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * Return lines into array
     *
     * @param bool $cleanEmptyLines delete empty lines
     *
     * @return array
     */
    public function getArrayData(bool $cleanEmptyLines = false)
    {
        $lines = explode("\n", $this->data);

        if($cleanEmptyLines) {
            foreach ($lines as $k => $lineContent) {
                if(empty(trim($lineContent))) {
                    unset($lines[$k]);
                }
            }
        }

        return $lines;
    }
}