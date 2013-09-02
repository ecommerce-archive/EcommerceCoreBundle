<?php

namespace Ecommerce\Bundle\CoreBundle\Doctrine\Orm;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductReference
 */
class ProductReference
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;



    /**
     * Constructor.
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return ProductReference
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
