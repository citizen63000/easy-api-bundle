<?php

namespace EasyApiBundle\Entity\User;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractConnectionHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="EasyApiBundle\Entity\User\User", cascade={})
     */
    protected ?UserInterface $user = null;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $isSso = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $ip = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $userAgent = null;

    /**
     * @ORM\Column(type="string")
     */
    protected ?string $tokenValue = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $browserName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $browserVersion = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $browserEngineName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $browserEngineVersion = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $operatingSystemName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $operatingSystemVersion = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $deviceModel = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $deviceVersion = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $deviceBrand = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $deviceType = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected bool $isMobile = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected bool $isTouch = false;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected ?DateTimeInterface $loginDate = null;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected ?DateTimeInterface $lastActionDate = null;

    /**
     * Get id.
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
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    public function isSso(): bool
    {
        return $this->isSso;
    }

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

    public function setUserAgent(?string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getTokenValue(): ?string
    {
        return $this->tokenValue;
    }

    public function setTokenValue(?string $tokenValue)
    {
        $this->tokenValue = $tokenValue;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip)
    {
        $this->ip = $ip;
    }

    public function getBrowserName(): ?string
    {
        return $this->browserName;
    }

    public function setBrowserName(?string $browserName)
    {
        $this->browserName = $browserName;
    }

    /**
     * @return string
     */
    public function getBrowserVersion(): ?string
    {
        return $this->browserVersion;
    }

    public function setBrowserVersion(?string $browserVersion)
    {
        $this->browserVersion = $browserVersion;
    }

    /**
     * @return string
     */
    public function getBrowserEngineName(): ?string
    {
        return $this->browserEngineName;
    }

    public function setBrowserEngineName(?string $browserEngineName)
    {
        $this->browserEngineName = $browserEngineName;
    }

    /**
     * @return string
     */
    public function getBrowserEngineVersion(): ?string
    {
        return $this->browserEngineVersion;
    }

    public function setBrowserEngineVersion(?string $browserEngineVersion)
    {
        $this->browserEngineVersion = $browserEngineVersion;
    }

    /**
     * @return string
     */
    public function getOperatingSystemName(): ?string
    {
        return $this->operatingSystemName;
    }

    public function setOperatingSystemName(?string $operatingSystemName)
    {
        $this->operatingSystemName = $operatingSystemName;
    }

    /**
     * @return string
     */
    public function getOperatingSystemVersion(): ?string
    {
        return $this->operatingSystemVersion;
    }

    public function setOperatingSystemVersion(?string $operatingSystemVersion)
    {
        $this->operatingSystemVersion = $operatingSystemVersion;
    }

    /**
     * @return string
     */
    public function getDeviceModel(): ?string
    {
        return $this->deviceModel;
    }

    public function setDeviceModel(?string $deviceModel)
    {
        $this->deviceModel = $deviceModel;
    }

    /**
     * @return string
     */
    public function getDeviceVersion(): ?string
    {
        return $this->deviceVersion;
    }

    public function setDeviceVersion(?string $deviceVersion)
    {
        $this->deviceVersion = $deviceVersion;
    }

    /**
     * @return string
     */
    public function getDeviceBrand(): ?string
    {
        return $this->deviceBrand;
    }

    public function setDeviceBrand(?string $deviceBrand)
    {
        $this->deviceBrand = $deviceBrand;
    }

    /**
     * @return string
     */
    public function getDeviceType(): ?string
    {
        return $this->deviceType;
    }

    public function setDeviceType(?string $deviceType)
    {
        $this->deviceType = $deviceType;
    }

    /**
     * @return bool
     */
    public function isMobile(): ?bool
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
    public function isTouch(): ?bool
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

    public function getLoginDate(): DateTimeInterface
    {
        return $this->loginDate;
    }

    public function setLoginDate(DateTimeInterface $loginDate)
    {
        $this->loginDate = $loginDate;
    }

    public function getLastActionDate(): DateTimeInterface
    {
        return $this->lastActionDate;
    }

    public function setLastActionDate(DateTimeInterface $lastActionDate)
    {
        $this->lastActionDate = $lastActionDate;
    }
}
