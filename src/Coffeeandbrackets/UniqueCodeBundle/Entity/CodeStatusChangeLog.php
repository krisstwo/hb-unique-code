<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CodeStatusChangeLog
 *
 * @ORM\Table(name="log_code")
 * @ORM\Entity(repositoryClass="Coffeeandbrackets\UniqueCodeBundle\Repository\LogCodeRepository")
 */
class CodeStatusChangeLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="from_status", type="string", length=255)
     */
    private $fromStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="to_status", type="string", length=255)
     */
    private $toStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_admin_action", type="boolean")
     */
    private $isAdminAction;

    /**
     * @ORM\ManyToOne(targetEntity="Coffeeandbrackets\UniqueCodeBundle\Entity\Code")
     * @ORM\JoinColumn(name="code_id", referencedColumnName="id")
     */
    private $code;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="cam_id")
     */
    private $campaign;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fromStatus
     *
     * @param string $fromStatus
     *
     * @return CodeStatusChangeLog
     */
    public function setFromStatus($fromStatus)
    {
        $this->fromStatus = $fromStatus;

        return $this;
    }

    /**
     * Get fromStatus
     *
     * @return string
     */
    public function getFromStatus()
    {
        return $this->fromStatus;
    }

    /**
     * Set toStatus
     *
     * @param string $toStatus
     *
     * @return CodeStatusChangeLog
     */
    public function setToStatus($toStatus)
    {
        $this->toStatus = $toStatus;

        return $this;
    }

    /**
     * Get toStatus
     *
     * @return string
     */
    public function getToStatus()
    {
        return $this->toStatus;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return CodeStatusChangeLog
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set isAdminAction
     *
     * @param boolean $isAdminAction
     *
     * @return CodeStatusChangeLog
     */
    public function setIsAdminAction($isAdminAction)
    {
        $this->isAdminAction = $isAdminAction;

        return $this;
    }

    /**
     * Get isAdminAction
     *
     * @return bool
     */
    public function getIsAdminAction()
    {
        return $this->isAdminAction;
    }

    /**
     * @return Code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param Code $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param mixed $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }
}

