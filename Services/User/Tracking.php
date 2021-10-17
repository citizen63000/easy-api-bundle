<?php

namespace EasyApiBundle\Services\User;

use EasyApiBundle\Entity\User\AbstractConnectionHistory;
use EasyApiBundle\Entity\User\AbstractConnectionHistory as ConnectionHistory;
use EasyApiBundle\Services\AbstractService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Model\UserAgent;
use UserAgentParser\Provider\WhichBrowser;

class Tracking extends AbstractService
{
    const CONNECTION_HISTORY_CLASS_PARAMETER = 'easy_api.user_tracking.connection_history_class';
    const TRACKING_ENABLE_PARAMETER = 'easy_api.user_tracking.enable';

    /**
     * @return array
     */
    protected function getProviders(): array
    {
        return [new WhichBrowser()];
    }

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

        $this->updateConnectionFromUserAgent($connectionHistory);

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
        return explode('.', $token)[1];
    }

    /**
     * @param ConnectionHistory $connectionHistory
     *
     * @return ConnectionHistory
     */
    public function updateConnectionFromUserAgent(ConnectionHistory $connectionHistory): ConnectionHistory
    {
        $results = $this->parseUserAgent($connectionHistory->getUserAgent());

        foreach ($results as $result) {
            $this->updateBrowser($result, $connectionHistory);
            $this->updateOperatingSystem($result, $connectionHistory);
            $this->updateDevice($result, $connectionHistory);
        }

        return $connectionHistory;
    }

    /**
     * @param string $userAgent
     *
     * @return array
     */
    protected function parseUserAgent(string $userAgent): array
    {
        $results = [];

        if ($userAgent) {
            $providers = $this->getProviders();
            foreach ($providers as $provider) {
                try {
                    $results[] = $provider->parse($userAgent);
                } catch (NoResultFoundException $ex) {
                    // nothing found
                }
            }
        }

        return $results;
    }

    /**
     * @param UserAgent $result
     * @param ConnectionHistory $connectionHistory
     *
     * @return ConnectionHistory
     */
    protected function updateBrowser(UserAgent $result, ConnectionHistory $connectionHistory): ConnectionHistory
    {
        $browser = $result->getBrowser();
        $browserEngine = $result->getRenderingEngine();

        if (empty($connectionHistory->getBrowserName())) {
            $connectionHistory->setBrowserName($browser->getName());
        }
        if (empty($connectionHistory->getBrowserVersion())) {
            $connectionHistory->setBrowserVersion($browser->getVersion()->getComplete());
        }
        if (empty($connectionHistory->getBrowserEngineName())) {
            $connectionHistory->setBrowserEngineName($browserEngine->getName());
        }
        if (empty($connectionHistory->getBrowserEngineVersion())) {
            $connectionHistory->setBrowserEngineVersion($browserEngine->getVersion()->getComplete());
        }

        return $connectionHistory;
    }

    /**
     * @param UserAgent         $result
     * @param ConnectionHistory $connectionHistory
     */
    protected function updateOperatingSystem(UserAgent $result, ConnectionHistory $connectionHistory)
    {
        if (empty($connectionHistory->getOperatingSystemName())) {
            $connectionHistory->setOperatingSystemName($result->getOperatingSystem()->getName());
        }
        if (empty($connectionHistory->getOperatingSystemVersion())) {
            $connectionHistory->setOperatingSystemVersion($result->getOperatingSystem()->getVersion()->getComplete());
        }
    }

    /**
     * @param UserAgent         $result
     * @param ConnectionHistory $connectionHistory
     */
    protected function updateDevice(UserAgent $result, ConnectionHistory $connectionHistory)
    {
        if (empty($connectionHistory->getDeviceModel())) {
            $connectionHistory->setDeviceModel($result->getDevice()->getModel());
        }
        if (empty($connectionHistory->getDeviceBrand())) {
            $connectionHistory->setDeviceBrand($result->getDevice()->getBrand());
        }
        if (empty($connectionHistory->getDeviceType())) {
            $connectionHistory->setDeviceType($result->getDevice()->getType());
        }
        if (empty($connectionHistory->isMobile())) {
            $connectionHistory->setIsMobile($result->getDevice()->getIsMobile());
        }
        if (empty($connectionHistory->isTouch())) {
            $connectionHistory->setIsTouch($result->getDevice()->getIsTouch());
        }
    }
}
