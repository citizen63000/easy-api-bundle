<?php

namespace EasyApiBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use EasyApiBundle\Entity\User\AbstractUser as User;
use \DateTime;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractConnectionHistory
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="EasyApiBundle\Entity\User\User", cascade={})
     */
    protected $user;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isSso;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $ip;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $userAgent;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $tokenValue;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $browserName;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $browserVersion;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $browserEngineName;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $browserEngineVersion;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $operatingSystemName;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $operatingSystemVersion;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $deviceModel;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $deviceVersion;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $deviceBrand;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $deviceType;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isMobile;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isTouch;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $loginDate;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $lastActionDate;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isSso()
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
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
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
     * @param string $tokenValue
     */
    public function setTokenValue(?string $tokenValue)
    {
        $this->tokenValue = $tokenValue;
    }

    /**
     * @return string
     */
    public function getIp() :?string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(?string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getBrowserName() :?string
    {
        return $this->browserName;
    }

    /**
     * @param string $browserName
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
     * @param string $browserVersion
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
     * @param string $browserEngineName
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
     * @param string $browserEngineVersion
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
     * @param string $operatingSystemName
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
     * @param string $operatingSystemVersion
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
     * @param string $deviceModel
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
     * @param string $deviceVersion
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
     * @param string $deviceBrand
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
     * @param string $deviceType
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
