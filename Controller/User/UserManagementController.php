<?php

namespace EasyApiBundle\Controller\User;

use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Form\Model\User\ResetPassword;
use EasyApiBundle\Form\Type\User\ResetPasswordType;
use EasyApiBundle\Util\ApiProblem;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
//use GuzzleHttp\Client;
//use GuzzleHttp\RequestOptions;
use Nelmio\ApiDocBundle\Annotation\Model;
use EasyApiBundle\Util\Controller\AbstractApiController;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use \DateTime;

class UserManagementController extends AbstractApiController
{
    /**
     * Get me
     *
     * @SWG\Tag(name="User")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(ref=@Model(type=User::class, groups={"default"}))
     * ),
     *
     * @SWG\Response(response="401", ref="#/definitions/401"),
     * @SWG\Response(response="403", ref="#/definitions/403"),
     * @SWG\Response(response="415", ref="#/definitions/415"),
     * @SWG\Response(response="422", ref="#/definitions/422")
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getMeAction()
    {
        return $this->getUser();
    }

    /**
     * POST - User registration.
     * <div class='api-right'><strong>Required rights</strong> : <ul><li><em>R_API_USER_C</em></li></ul></div>
     * This method creates an user, from request payloads.
     *
     * @SWG\Tag(name="Authentication")
     *
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     description="Creation data.",
     *     required=true,
     *     @SWG\Schema(ref=@Model(type=UserSignInType::class))
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(ref=@Model(type=User::class, groups={"user_profile"}))
     * ),
     * @SWG\Response(response="401", ref="#/definitions/401"),
     * @SWG\Response(response="403", ref="#/definitions/403"),
     * @SWG\Response(response="415", ref="#/definitions/415"),
     * @SWG\Response(response="422", ref="#/definitions/422")
     *
     * @Rest\View(serializerGroups={"user_profile"})
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function registrationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userModel = new User();

        $form = $this->createForm(UserSignInType::class, $userModel);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $fosUser = $this->get('fos_user.util.user_manipulator')->create(
                $userModel->getEmail(),
                $userModel->getPlainPassword(),
                $userModel->getEmail(),
                true, false
            );
            $user = $em->getRepository(User::class)->find($fosUser);

            $profile = $userModel->getProfile();
            $profile->setCoordinate();
            $user->setProfile($profile);

            $em->persist($user);
            $em->flush();

            $this->setDefaultUserRights($user);

            $anonymousAuthorization = $request->headers->get('authorization');
            if (null !== $anonymousAuthorization) {
                list($bearer, $token) = preg_split('# #', $anonymousAuthorization);
                $this->get('app.anonymous_data_manager')->retrieveDataFromAnonymousSession(
                    $user,
                    $token
                );
            }

            return $this->view($user, Response::HTTP_CREATED);
        }

        throw new ApiProblemException(
            new ApiProblem(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->get('app.form_errors_serializer')->serializeFormErrors($form, true, true)
            )
        );
    }

    /**
     * POST - User reset password.
     *
     * @SWG\Tag(name="Authentication")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successful operation"
     * ),
     * @SWG\Parameter(
     * name="data",
     *  in="body",
     *  description="User reset password data.",
     *  required=true,
     *  @SWG\Schema(ref=@Model(type=ResetPasswordType::class))
     *   ),
     * @SWG\Response(response="401", ref="#/definitions/401"),
     * @SWG\Response(response="403", ref="#/definitions/403"),
     * @SWG\Response(response="415", ref="#/definitions/415"),
     * @SWG\Response(response="422", ref="#/definitions/422")
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     */
    public function resetPasswordAction(Request $request)
    {
        $reset = new ResetPassword();
        $form = $this->createForm(ResetPasswordType::class, $reset);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            // Default status
            $user = $this->getRepository(User::class)->findOneByUsername($reset->getUsername());
            if (null === $user) {
                $user = $this->getRepository(User::class)->findOneByEmail($reset->getUsername());
            }

            if (null === $user) {
                throw new ApiProblemException(
                    new ApiProblem(Response::HTTP_NOT_FOUND, ApiProblem::USER_USERNAME_INVALID, 'user')
                );
            }

            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $token = $tokenGenerator->generateToken();

            $user->setPasswordRequestedAt(new DateTime());
            $user->setConfirmationToken($token);
            $this->persistAndFlush($user);
            $this->get('fos_user.user_manager')->updateUser($user);
            $this->sendResetPasswordEmail($user, $token);

            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        $this->throwUnprocessableEntity($form);
    }

    /**
     * POST - User update password with token.
     *
     * @SWG\Tag(name="Authentication")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successful operation"
     * ),
     * @SWG\Parameter(ref="#/parameters/user_username"),
     * @SWG\Parameter(ref="#/parameters/user_token"),
     * @SWG\Parameter(ref="#/parameters/user_password"),
     *
     * @SWG\Response(response="401", ref="#/definitions/401"),
     * @SWG\Response(response="403", ref="#/definitions/403"),
     * @SWG\Response(response="415", ref="#/definitions/415"),
     * @SWG\Response(response="422", ref="#/definitions/422")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function updatePasswordAction(Request $request)
    {
        $token = $request->get('resetToken');
        $username = $request->get('username');
        $password = $request->get('newPassword');

        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_NOT_FOUND, ApiProblem::USER_TOKEN_INVALID, 'user')
            );
        }

        if ($user->getEmail() !== $username && $user->getUsername() !== $username) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_NOT_FOUND, ApiProblem::USER_EMAIL_INVALID, 'user')
            );
        }

        $user->setPlainPassword($password);
        $userManager->updatePassword($user);
        // Clear confirmation token
        $user->setConfirmationToken(null);
        $this->persistAndFlush($user);

        return $this->view(null, Response::HTTP_OK);
    }

    /**
     * POST - User update password with old password parameter.
     *
     * @SWG\Tag(name="Authentication")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successful operation"
     * ),
     * @SWG\Parameter(name="currentPassword", in="query", description="Old password of user", required=true, type="string"),
     * @SWG\Parameter(name="newPassword", in="query", description="New password of user", required=true, type="string"),
     *
     * @SWG\Response(response="401", ref="#/definitions/401"),
     * @SWG\Response(response="403", ref="#/definitions/403"),
     * @SWG\Response(response="415", ref="#/definitions/415"),
     * @SWG\Response(response="422", ref="#/definitions/422")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function amendPasswordAction(Request $request)
    {
        $currentPassword = $request->get('currentPassword');
        $newPassword = $request->get('newPassword');

        $user = $this->getUser();
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $validPassword = $encoder->isPasswordValid($user->getPassword(), $currentPassword, $user->getSalt());

        if (!$validPassword) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_UNPROCESSABLE_ENTITY, ApiProblem::USER_PASSWORD_INVALID, 'user')
            );
        }

        $user->setPassword($encoder->encodePassword($newPassword, $user->getSalt()));
        $this->persistAndFlush($user);

        return $this->view(null, Response::HTTP_OK);
    }

    /**
     * Send mail for resetting password.
     *
     * @param User   $user
     * @param string $token
     *
     * @return mixed
     *
     * @throws \Exception
     */
    private function sendResetPasswordEmail(User $user, string $token)
    {
        $mailer = $this->getMailer();
        $date = new DateTime();
        $object = $this->get('translator.default')->trans('forgotten_password.object', [], 'authentication', 'fr_FR');
        $params = [
            'token' => $token,
            'date' => $date->format('d/m/Y H:i'),
            'frontBaseUrl' => $this->getParameter('front_base_url'),
            'frontNewpwdUrl' => $this->getParameter('lost_password_url'),
        ];

        return $mailer->sendToUser('@EasyApi/mail/lost_password_mail.html.twig', $object, $user, $params);
    }
}
