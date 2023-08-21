<?php
namespace App\Model;


use Doctrine\Common\Collections\ArrayCollection;

class KidsCollection
{
    private $kids;

    public function __construct()
    {
        $this->kids = new ArrayCollection();
    }

    public function getKids(): ArrayCollection
    {
        return $this->kids;
    }

    public function setKids(ArrayCollection $kids): void
    {
        $this->kids = $kids;
    }
}
