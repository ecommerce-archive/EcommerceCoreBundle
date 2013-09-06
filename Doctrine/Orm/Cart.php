<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

use Doctrine\Common\Collections\ArrayCollection;

class Cart
{
    const STATUS_EXPIRED = 0;
    const STATUS_CREATED = 1;
    const STATUS_OPEN = 2;
    const STATUS_CHECKOUT = 3;

    /** @var string */
    private $id;

    /** @var integer */
    private $status;

    /** @var CartItem[] */
    private $items;

    /** @var integer */
    private $totalItems;

    /** @var float */
    private $total;

    /** @var \DateTime */
    private $createdAt;

    /** @var \DateTime */
    private $updatedAt;

    /** @var \DateTime */
    private $expiresAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->status = self::STATUS_CREATED;

        $this->items = new ArrayCollection();

        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $this->setExpiresAt();
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param integer $status
     * @return Cart
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param CartItem $item
     * @return Cart
     */
    public function addItem(CartItem $item)
    {
        $this->items[] = $item;

        $this->setExpiresAt();

        return $this;
    }

    /**
     * @param CartItem $item
     * @return Cart
     */
    public function removeItem(CartItem $item)
    {
        $this->items->removeElement($item);

        $this->setExpiresAt();

        return $this;
    }


    /**
     * @return CartItem[]|ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }


    /**
     * @param integer $totalItems
     * @return Cart
     */
    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;

        return $this;
    }

    /**
     * @return integer
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }


    /**
     * @param float $total
     * @return Cart
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }


    /**
     * Calculate totals
     */
    public function calculateTotals()
    {
        $totalItems = 0;
        $total      = 0;

        foreach ($this->getItems() as $item) {
            $totalItems++;
            $total += (float)$item->getPrice();
        }

        $this->totalItems = $totalItems;
        $this->total      = $total;
    }


    /**
     * @param \DateTime|null $createdAt
     * @return Cart
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt instanceof \DateTime ? $createdAt : new \DateTime();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


    /**
     * @param \DateTime|null $updatedAt
     * @return Cart
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt instanceof \DateTime ? $updatedAt : new \DateTime();

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    /**
     * @param \DateTime|null $expiresAt
     * @return Cart
     */
    public function setExpiresAt(\DateTime $expiresAt = null)
    {
        if (!$expiresAt instanceof \DateTime) {
            $expiresAt = new \DateTime();
            // @TODO: Move to listener to inject time interval
            $expiresAt->add(new \DateInterval('PT3H'));
        }

        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return boolean
     */
    public function isExpired()
    {
        return $this->getExpiresAt() < new \DateTime('now');
    }
}
