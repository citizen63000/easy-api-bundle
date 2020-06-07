<?php

namespace EasyApiBundle\Util;

use Symfony\Component\HttpFoundation\Response;

/**
 * A wrapper for holding data to be used for a application/problem+json response.
 *
 * @see https://tools.ietf.org/html/draft-nottingham-http-problem-06
 */
class ApiProblem
{
    const PREFIX = 'core.error.';

    // region Error constants

    const UNEXPECTED_ERROR = 'something.went.wrong';
    const FORM_EXTRA_FIELDS_ERROR = 'form.extra_fields';

    const INVALID_FORMAT = 'invalid.format.message';
    const ROUTE_NOT_FOUND = 'route.not_found';
    const ENTITY_NOT_FOUND = '%s.not_found';
    const FORBIDDEN = 'forbidden';
    const MAILING_ERROR = 'mailer.error';

    const RESULT_ORDER_INCORRECT = 'order.incorrect_order';
    const RESULT_SORT_MALFORMED = 'sort.malformed';

    const PAGINATION_INCORRECT_PAGE_VALUE = 'pagination.incorrect_page_value';
    const PAGINATION_INCORRECT_RESULT_PER_PAGE_VALUE = 'pagination.incorrect_results_per_page_value';

    const ENTITY_FIELD_REQUIRED = '%s.%s.required';
    const ENTITY_FIELD_INVALID = '%s.%s.invalid';
    const ENTITY_FIELD_TOO_LONG = '%s.%s.too_long';

    const UPLOAD_UNABLE_TO_WRITE_DIRECTORY = 'upload.unable.to.write.directory';

    // endregion

    // region JWT

    const AUTHENTICATION_FAILURE = 'bad_credentials';
    const RESTRICTED_ACCESS = 'restricted_access';
    const JWT_INVALID = 'invalid_token';
    const JWT_NOT_FOUND = 'missing_token';
    const JWT_EXPIRED = 'token_expired';

    // endregion

    // region Users

    const USER_USERNAME_ANONYMOUS_NOT_ALLOWED = 'user.username.anonymous_not_allowed';
    const USER_USERNAME_ALREADY_EXISTS = 'user.username.already_exists';
    const USER_EMAIL_ALREADY_EXISTS = 'user.email.already_exists';
    const USER_EMAIL_MALFORMED = 'user.email.malformed';
    const USER_PROFILE_INVALID_CIVILITY = 'user.profile.ref_civility.invalid';
    const USER_USERNAME_INVALID = 'user.username.invalid';
    const USER_RESPONSE_TYPE_INVALID = 'user.response_type.invalid';
    const USER_CLIENT_INVALID = 'user.client.invalid';
    const USER_ALLOWED_OR_REDIRECT_INVALID = 'user.allowed_or_redirect.invalid';
    const USER_EMAIL_INVALID = 'user.email.invalid';
    const USER_TOKEN_INVALID = 'user.token.invalid';
    const USER_PASSWORD_INVALID = 'user.password.invalid';
    const USER_PASSWORD_SAVE_FAILED = 'user.password.save.failed';

    // endregion


    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $extraData = [];

    /**
     * ApiProblem constructor.
     *
     * @param int          $statusCode
     * @param string|array $errors
     * @param bool         $prefix
     */
    public function __construct(int $statusCode, $errors, bool $prefix = true)
    {
        $this->statusCode = $statusCode;
        if (!is_array($errors)) {
            $errors = [$errors];
        }

        $this->normalizeErrors($statusCode, $errors, $prefix);
    }

    /**
     * Array representation.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            $this->extraData,
            [
                'errors' => $this->errors,
            ]
        );
    }

    /**
     * Set some extra data.
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Normalize error message.
     *
     * @param int   $statusCode
     * @param array $errors
     * @param bool  $prefix
     *
     * @return string
     */
    private function normalizeErrors(int $statusCode, array $errors, bool $prefix)
    {
        foreach ($errors as $error) {
            $this->errors[] = $this->normalizeError($statusCode, $error, $prefix);
        }
    }

    /**
     * Normalize error message.
     *
     * @param int    $statusCode
     * @param string $type
     * @param bool   $prefix
     *
     * @return string
     */
    private function normalizeError(int $statusCode, string $type, bool $prefix)
    {
        // 400
        if (Response::HTTP_BAD_REQUEST === $statusCode) {
            if (preg_match('#^Invalid [a-zA-Z]+ message received$#', $type)) {
                $type = self::INVALID_FORMAT;
            } elseif (preg_match('#^Could not find any log entries under version#', $type)) {
                $type = self::INVALID_FORMAT;
            }
            // 401
        } elseif (Response::HTTP_UNAUTHORIZED === $statusCode) {
            if (preg_match('#^A Token was not found in the TokenStorage#', $type)) {
                $type = self::JWT_NOT_FOUND;
            }
            // 403
        } elseif (Response::HTTP_FORBIDDEN === $statusCode) {
            if (preg_match('#^Token does not have the required roles#', $type)
                || preg_match('#^Access Denied.$#', $type)) {
                $type = self::RESTRICTED_ACCESS;
            }
            // 404
        } elseif (Response::HTTP_NOT_FOUND === $statusCode) {
            // Unknown entity ?
            if (preg_match('#^(.*\\\Entity\\\(.*)) object not found .*$#', $type, $matches)) {
                $type = strtolower(
                    sprintf(self::ENTITY_NOT_FOUND, self::normalizeClassName($matches[2]))
                );
                // Unknown route or resource
            } elseif (preg_match('#^No route found for#', $type)) {
                $type = self::ROUTE_NOT_FOUND;
            }
            // 405
        } elseif (Response::HTTP_METHOD_NOT_ALLOWED === $statusCode) {
            $type = self::ROUTE_NOT_FOUND; // Generic message :)
        }

        $this->statusCode = $statusCode;

        return $prefix ? self::PREFIX.$type : $type;
    }

    /**
     * Normalize class name for JSON
     * Ex : normalizeClassName("One\Two\ThreeFour") => "one.two.three_four".
     *
     * @param string $className
     *
     * @return string
     */
    public static function normalizeClassName(string $className)
    {
        preg_match_all('#([A-Z\\\][A-Z0-9\\\]*(?=$|[A-Z\\\][a-z0-9\\\])|[A-Za-z\\\][a-z0-9\\\]+)#', $className, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match === strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return preg_replace('#\\\_#', '.', implode('_', $ret));
    }
}
