<?php

/*
 * This file is part of the Docudex project.
 *
 * (c) Devnet Limited <http://www.devnetlimited.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Modules\Core\App\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cor_request_logs")
 * @ORM\Entity(repositoryClass="Modules\Core\App\Repositories\RequestLogRepository")
 */
class RequestLog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $method;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(type="json")
     */
    private $headers;

    /**
     * @var string
     *
     * @ORM\Column(type="json",nullable=true)
     */
    private $requestData;

    /**
     * @var string
     *
     * @ORM\Column(type="integer")
     */
    private $responseStatus;

    /**
     * @var string
     *
     * @ORM\Column(type="text",nullable=true)
     */
    private $responseData;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $ipAddress;

    /**
     * @var string
     *
     * @ORM\Column(type="string",nullable=true)
     */
    private $userAgent;

    /**
     * @var string
     *
     * @ORM\Column(type="float",nullable=true)
     */
    private $executionTime;

    /**
     * @ORM\ManyToOne(targetEntity="Modules\Core\App\Entities\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $userId;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="updated_at", type="datetime" , nullable=true)
     */
    private $updatedAt;

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    public function getExecutionTime(): string
    {
        return $this->executionTime;
    }

    public function setExecutionTime(string $executionTime): void
    {
        $this->executionTime = $executionTime;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getResponseData(): string
    {
        return $this->responseData;
    }

    public function setResponseData(string $responseData): void
    {
        $this->responseData = $responseData;
    }

    public function getResponseStatus(): string
    {
        return $this->responseStatus;
    }

    public function setResponseStatus(string $responseStatus): void
    {
        $this->responseStatus = $responseStatus;
    }

    public function getRequestData(): string
    {
        return $this->requestData;
    }

    public function setRequestData(string $requestData): void
    {
        $this->requestData = $requestData;
    }

    public function getHeaders(): string
    {
        return $this->headers;
    }

    public function setHeaders(string $headers): void
    {
        $this->headers = $headers;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }



}
