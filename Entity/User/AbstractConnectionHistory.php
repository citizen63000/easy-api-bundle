<?php

namespace EasyApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use \DateTime;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractConnectionHistory
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @var UserInterface|null
     * @ORM\ManyToOne(targetEntity="EasyApiBundle\Entity\User\User", cascade={})
     */
    protected ?UserInterface $user = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $isSso = false;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $ip = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $userAgent = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $tokenValue = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $browserName = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $browserVersion = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $browserEngineName = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $browserEngineVersion = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $operatingSystemName = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $operatingSystemVersion = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $deviceModel = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $deviceVersion = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $deviceBrand = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    protected ?string $deviceType = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $isMobile = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $isTouch = false;

    /**
     * @var \DateTime|null
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected ?DateTime $loginDate = null;

    /**
     * @var \DateTime|null
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected ?DateTime $lastActionDate = null;

    /**
     * Get id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get user.
     *
     * @return UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isSso(): bool
    {
        return $this->isSso;
    }

    /**
     * @param bool $isSso
     */
    public function setIsSso(bool $isSso)
    {
        $this->isSso = $isSso;
    }

    /**
     * @return string
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @param string|null $userAgent
     */
    public function setUserAgent(?string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getTokenValue() :?string
    {
        return $this->tokenValue;
    }

    /**
     * @param string|null $tokenValue
     */
    public function setTokenValue(?string $tokenValue)
    {
        $this->tokenValue = $tokenValue;
    }

    /**
     * @return string|null
     */
    public function getIp() :?string
    {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     */
    public function setIp(?string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string|null
     */
    public function getBrowserName() :?string
    {
        return $this->browserName;
    }

    /**
     * @param string|null $browserName
     */
    public function setBrowserName(?string $browserName)
    {
        $this->browserName = $browserName;
    }

    /**
     * @return string
     */
    public function getBrowserVersion() :?string
    {
        return $this->browserVersion;
    }

    /**
     * @param string|null $browserVersion
     */
    public function setBrowserVersion(?string $browserVersion)
    {
        $this->browserVersion = $browserVersion;
    }

    /**
     * @return string
     */
    public function getBrowserEngineName() :?string
    {
        return $this->browserEngineName;
    }

    /**
     * @param string|null $browserEngineName
     */
    public function setBrowserEngineName(?string $browserEngineName)
    {
        $this->browserEngineName = $browserEngineName;
    }

    /**
     * @return string
     */
    public function getBrowserEngineVersion() :?string
    {
        return $this->browserEngineVersion;
    }

    /**
     * @param string|null $browserEngineVersion
     */
    public function setBrowserEngineVersion(?string $browserEngineVersion)
    {
        $this->browserEngineVersion = $browserEngineVersion;
    }

    /**
     * @return string
     */
    public function getOperatingSystemName() :?string
    {
        return $this->operatingSystemName;
    }

    /**
     * @param string|null $operatingSystemName
     */
    public function setOperatingSystemName(?string $operatingSystemName)
    {
        $this->operatingSystemName = $operatingSystemName;
    }

    /**
     * @return string
     */
    public function getOperatingSystemVersion() :?string
    {
        return $this->operatingSystemVersion;
    }

    /**
     * @param string|null $operatingSystemVersion
     */
    public function setOperatingSystemVersion(?string $operatingSystemVersion)
    {
        $this->operatingSystemVersion = $operatingSystemVersion;
    }

    /**
     * @return string
     */
    public function getDeviceModel() :?string
    {
        return $this->deviceModel;
    }

    /**
     * @param string|null $deviceModel
     */
    public function setDeviceModel(?string $deviceModel)
    {
        $this->deviceModel = $deviceModel;
    }

    /**
     * @return string
     */
    public function getDeviceVersion() :?string
    {
        return $this->deviceVersion;
    }

    /**
     * @param string|null $deviceVersion
     */
    public function setDeviceVersion(?string $deviceVersion)
    {
        $this->deviceVersion = $deviceVersion;
    }

    /**
     * @return string
     */
    public function getDeviceBrand() :?string
    {
        return $this->deviceBrand;
    }

    /**
     * @param string|null $deviceBrand
     */
    public function setDeviceBrand(?string $deviceBrand)
    {
        $this->deviceBrand = $deviceBrand;
    }

    /**
     * @return string
     */
    public function getDeviceType() :?string
    {
        return $this->deviceType;
    }

    /**
     * @param string|null $deviceType
     */
    public function setDeviceType(?string $deviceType)
    {
        $this->deviceType = $deviceType;
    }

    /**
     * @return bool
     */
    public function isMobile() :?bool
    {
        return $this->isMobile;
    }

    /**
     * @param bool $isMobile
     */
    public function setIsMobile(?bool $isMobile)
    {
        $this->isMobile = $isMobile;
    }

    /**
     * @return bool
     */
    public function isTouch() :?bool
    {
        return $this->isTouch;
    }

    /**
     * @param bool $isTouch
     */
    public function setIsTouch(?bool $isTouch)
    {
        $this->isTouch = $isTouch;
    }

    /**
     * @return \DateTime
     */
    public function getLoginDate() :DateTime
    {
        return $this->loginDate;
    }

    /**
     * @param DateTime $loginDate
     */
    public function setLoginDate(DateTime $loginDate)
    {
        $this->loginDate = $loginDate;
    }

    /**
     * @return DateTime
     */
    public function getLastActionDate() :DateTime
    {
        return $this->lastActionDate;
    }

    /**
     * @param DateTime $lastActionDate
     */
    public function setLastActionDate(DateTime $lastActionDate)
    {
        $this->lastActionDate = $lastActionDate;
    }
}
