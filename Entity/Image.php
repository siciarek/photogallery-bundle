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
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $mime_type;

    /**
     * @var integer
     */
    private $file_size;

    /**
     * @var integer
     */
    private $width;

    /**
     * @var integer
     */
    private $height;

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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $albums;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->albums = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set path
     *
     * @param string $path
     * @return Image
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set mime_type
     *
     * @param string $mimeType
     * @return Image
     */
    public function setMimeType($mimeType)
    {
        $this->mime_type = $mimeType;
    
        return $this;
    }

    /**
     * Get mime_type
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }

    /**
     * Set file_size
     *
     * @param integer $fileSize
     * @return Image
     */
    public function setFileSize($fileSize)
    {
        $this->file_size = $fileSize;
    
        return $this;
    }

    /**
     * Get file_size
     *
     * @return integer 
     */
    public function getFileSize()
    {
        return $this->file_size;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;
    
        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;
    
        return $this;
    }

    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
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
     * Add albums
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Album $albums
     * @return Image
     */
    public function addAlbum(\Siciarek\PhotoGalleryBundle\Entity\Album $albums)
    {
        $albums->addImage($this);
        $this->albums[] = $albums;
    
        return $this;
    }

    /**
     * Remove albums
     *
     * @param \Siciarek\PhotoGalleryBundle\Entity\Album $albums
     */
    public function removeAlbum(\Siciarek\PhotoGalleryBundle\Entity\Album $albums)
    {
        $albums->removeImage($this);
        $this->albums->removeElement($albums);
    }

    /**
     * Get albums
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAlbums()
    {
        return $this->albums;
    }
}