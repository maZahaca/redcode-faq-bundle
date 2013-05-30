<?php

namespace RedCode\FaqBundle\Entity;

/**
 * @author maZahaca
 * @ORM\Entity(repositoryClass="RedCode\FaqBundle\EntityRepository\FaqItem")
 */
abstract class FaqItem
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $question;

    /**
     * @var string
     */
    protected $answer;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
}
