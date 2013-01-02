<?php

namespace Siciarek\PhotoGalleryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Album
 */
class Album
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $sequence_number;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $is_visible;

    /**
     * @var \DateTime
     */
    private $expires_at;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var \Siciarek\PhotoGalleryBundle\Entity\Image
     */
    private $cover;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $images;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sequence_number
     *
     * @param integer $sequenceNumber
     * @return Album
     */
    public function setSequenceNumber($sequenceNumber)
    {
        $this->sequence_number = $sequenceNumber;
    
        return $this;
    }

    /**
     * Get sequence_number
     *
     * @return integer 
     */
    public function getSequenceNumber()
    {
        return $this->sequence_number;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Album
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Album
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Album
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set is_visible
     *
     * @param boolean $isVisible
     * @return Album
     */
    public function setIsVisible($isVisible)
    {
        $this->is_visible = $isVisible;
    
        return $this;
    }

    /**
     * Get is_visible
     *
     * @return boolean 
     */
    public function getIsVisible()
    {
        return $this->is_visible;
    }

    /**
     * Set expires_at
     *
     * @param \DateTime $expiresAt
     * @return Album
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expires_at = $expiresAt;
    
        return $this;
    }

    /**
     * Get expires_at
     *
     * @return \DateTime 
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Album
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Album
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set cover
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Image $cover
     * @return Album
     */
    public function setCover(\Siciarek\PhotoGalleryBundle\Entity\Image $cover = null)
    {
        $this->cover = $cover;
    
        return $this;
    }

    /**
     * Get cover
     *
     * @return \Siciarek\PhotoGalleryBundle\Entity\Image 
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Add images
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Image $images
     * @return Album
     */
    public function addImage(\Siciarek\PhotoGalleryBundle\Entity\Image $images)
    {
        $this->images[] = $images;
    
        return $this;
    }

    /**
     * Remove images
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Image $images
     */
    public function removeImage(\Siciarek\PhotoGalleryBundle\Entity\Image $images)
    {
        $this->images->removeElement($images);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getImages()
    {
        return $this->images;
    }
    /**
     * @var \Siciarek\PhotoGalleryBundle\Entity\Creator
     */
    private $creator;


    /**
     * Set creator
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Creator $creator
     * @return Album
     */
    public function setCreator(\Siciarek\PhotoGalleryBundle\Entity\Creator $creator = null)
    {
        $this->creator = $creator;
    
        return $this;
    }

    /**
     * Get creator
     *
     * @return \Siciarek\PhotoGalleryBundle\Entity\Creator 
     */
    public function getCreator()
    {
        return $this->creator;
    }
}