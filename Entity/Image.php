<?php

namespace Siciarek\PhotoGalleryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Image
 */
class Image
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
    private $thumbnail;

    /**
     * @var \Siciarek\PhotoGalleryBundle\Entity\File
     */
    private $file;

    /**
     * @var \Siciarek\PhotoGalleryBundle\Entity\Album
     */
    private $album;


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
     * @return Image
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
     * @return Image
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
     * Set description
     *
     * @param string $description
     * @return Image
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
     * @return Image
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
     * @return Image
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
     * @return Image
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
     * @return Image
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
     * Set thumbnail
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Image $thumbnail
     * @return Image
     */
    public function setThumbnail(\Siciarek\PhotoGalleryBundle\Entity\Image $thumbnail = null)
    {
        $this->thumbnail = $thumbnail;
    
        return $this;
    }

    /**
     * Get thumbnail
     *
     * @return \Siciarek\PhotoGalleryBundle\Entity\Image 
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Set file
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\File $file
     * @return Image
     */
    public function setFile(\Siciarek\PhotoGalleryBundle\Entity\File $file = null)
    {
        $this->file = $file;
    
        return $this;
    }

    /**
     * Get file
     *
     * @return \Siciarek\PhotoGalleryBundle\Entity\File 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set album
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Album $album
     * @return Image
     */
    public function setAlbum(\Siciarek\PhotoGalleryBundle\Entity\Album $album = null)
    {
        $this->album = $album;
    
        return $this;
    }

    /**
     * Get album
     *
     * @return \Siciarek\PhotoGalleryBundle\Entity\Album 
     */
    public function getAlbum()
    {
        return $this->album;
    }
    /**
     * @var \Siciarek\PhotoGalleryBundle\Entity\Album
     */
    private $albumcov;


    /**
     * Set albumcov
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Album $albumcov
     * @return Image
     */
    public function setAlbumcov(\Siciarek\PhotoGalleryBundle\Entity\Album $albumcov = null)
    {
        $this->albumcov = $albumcov;
    
        return $this;
    }

    /**
     * Get albumcov
     *
     * @return \Siciarek\PhotoGalleryBundle\Entity\Album 
     */
    public function getAlbumcov()
    {
        return $this->albumcov;
    }
}