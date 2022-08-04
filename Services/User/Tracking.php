<?php

namespace EasyApiBundle\Services\User;

use EasyApiBundle\Entity\User\AbstractConnectionHistory;
use EasyApiBundle\Entity\User\AbstractConnectionHistory as ConnectionHistory;
use EasyApiBundle\Services\AbstractService;
use Namshi\JOSE\JWS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class Tracking extends AbstractService
{
    const CONNECTION_HISTORY_CLASS_PARAMETER = 'easy_api.user_tracking.connection_history_class';
    const TRACKING_ENABLE_PARAMETER = 'easy_api.user_tracking.enable';

    /**
     * @param UserInterface $user
     * @param Request $request
     * @param string $token
     * @param bool $isSSO
     *
     * @return ConnectionHistory
     *
     * @throws \Exception
     */
    public function logConnection(UserInterface $user, Request $request, string $token, bool $isSSO = false): ConnectionHistory
    {
        $connectionHistoryClass = $this->getParameter(self::CONNECTION_HISTORY_CLASS_PARAMETER);

        if (null === $connectionHistoryClass) {
            throw new \Exception(self::CONNECTION_HISTORY_CLASS_PARAMETER.' must be defined to log connection');
        }

        /** @var AbstractConnectionHistory $connectionHistory */
        $connectionHistory = new $connectionHistoryClass();
        $connectionHistory->setUser($user);
        $connectionHistory->setIsSSO($isSSO);
        $connectionHistory->setIp($request->getClientIp());
        $connectionHistory->setUserAgent($request->headers->get('User-Agent'));
        $connectionHistory->setTokenValue($token);
        $connectionHistory->setLoginDate(new \DateTime());
        $connectionHistory->setLastActionDate($connectionHistory->getLoginDate());

        $this->PersistAndFlush($connectionHistory);

        return $connectionHistory;
    }

    /**
     * Update date when user execute action.
     *
     * @param UserInterface $user
     * @param Request $request
     * @param string $token
     *
     * @throws \Exception
     */
    public function updateLastAction(UserInterface $user, Request $request, string $token)
    {
        $connectionHistoryClass = $this->getParameter(self::CONNECTION_HISTORY_CLASS_PARAMETER);

        if (null === $connectionHistoryClass) {
            throw new \Exception(self::CONNECTION_HISTORY_CLASS_PARAMETER.' must be defined to log connection');
        }

        $tokenId = $this->getTokenIdentifier($token);
        /** @var ConnectionHistory $connectionHistory */
        $connectionHistory = $this->getRepository($connectionHistoryClass)->findOneBy(['user' => $user->getId(), 'tokenValue' => $tokenId]);

        if (null == $connectionHistory) {
            $this->logConnection($user, $request, $tokenId);
        } else {
            $currentDate = new \DateTime();
            if ($currentDate->format('Y-m-d H:i:s') !== $connectionHistory->getLastActionDate()->format('Y-m-d H:i:s')) {
                $connectionHistory->setLastActionDate($currentDate);
                $this->persistAndFlush($connectionHistory);
            }
        }
    }

    /**
     * take JTI (token identifier) if exist in payload, if not exists use payload as identifier
     * @param string $token
     * @return mixed|string
     */
    public function getTokenIdentifier(string $token)
    {
        $payload = JWS::load($token)->getPayload();
        if(isset($payload['session_state'])) {
            return $payload['session_state'];
        }

        return explode('.', $token)[1];
    }
}
