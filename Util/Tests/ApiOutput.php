<?php

namespace EasyApiBundle\Util\Tests;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * API output response.
 *
 * Inherited methods (from $response attribute)
 *
 * @method string               getProtocolVersion()                    Retrieves the HTTP protocol version as a string.
 * @method ResponseInterface    withProtocolVersion($version)           Return an instance with the specified HTTP protocol version.
 * @method bool                 hasHeader($name)                        Checks if a header exists by the given case-insensitive name.
 * @method string[]             getHeader($name)                        Retrieves a message header value by the given case-insensitive name.
 * @method ResponseInterface    withHeader($name, $value)               Return an instance with the provided value replacing the specified header.
 * @method ResponseInterface    withAddedHeader($name, $value)          Return an instance with the specified header appended with the given value.
 * @method ResponseInterface    withoutHeader($name)                    Return an instance without the specified header.
 * @method StreamInterface      getBody()                               Gets the body of the message.
 * @method ResponseInterface    withBody(StreamInterface $body)         Return an instance with the specified message body.
 * @method int                  getStatusCode()                         Gets the response status code.
 * @method ResponseInterface    withStatus($code, $reasonPhrase = '')   Return an instance with the specified status code and, optionally, reason phrase.
 * @method string               getReasonPhrase()                       Gets the response reason phrase associated with the status code.
 */
class ApiOutput
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var Profile|null
     */
    private $profile;

    /**
     * @var string
     */
    private $stringData;

    /**
     * @var mixed
     */
    private $data;

    /**
     * ApiOutput constructor.
     *
     * @param Response $response
     * @param string $format
     * @param Profiler|null $profiler
     */
    public function __construct(Response $response, $format = null, ?Profile $profiler = null)
    {
        $this->response = $response;
        $this->profile = $profiler;

        if ($format) {
            $this->stringData = $response->getContent();
            try {
                $this->data = Format::readData($this->stringData, $format);
            } catch (UnexpectedValueException $e) {
                $this->data = $e->getMessage();
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param $format
     * @return ApiOutput
     */
    public static function createFromResponseInterface(ResponseInterface $response, $format)
    {
        $newResponse = new Response($response->getBody()->getContents(), $response->getStatusCode(), $response->getHeaders());

        return new ApiOutput($newResponse ,$format);
    }

    /**
     * Magic call to access to response elements.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (method_exists($this->response, $method)) {
            return call_user_func_array(array($this->response, $method), $arguments);
        }
    }

    /**
     * Get decoded data.
     *
     * @param bool $asString
     *
     * @return array|mixed|string
     */
    public function getData($asString = false)
    {
        return $asString ? $this->stringData : $this->data;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return Profile|null
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * @deprecated use getMailerMessages() instead
     * Returns the messages of a mailer.
     *
     * @return \Swift_Message[] the messages
     */
    public function getMessages()
    {
        return $this->getMailerMessages();
    }

    /**
     * Returns the messages of a mailer.
     *
     * @return \Swift_Message[] the messages
     */
    public function getMailerMessages()
    {
        return $this->getProfile()->getCollector('swiftmailer')->getMessages();
    }

    /**
     * Retrieves all message header values.
     * @return null|\Symfony\Component\HttpFoundation\ResponseHeaderBag
     */
    public function getHeaders()
    {
        return null !== $this->response ? $this->response->headers : null;
    }

    /**
     * @param $key
     * @return null|string|string[]
     */
    public function getHeaderLine($key)
    {
        $headers = $this->getHeaders();
        return $headers ? $headers->get($key) : null;
    }
}
