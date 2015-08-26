<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Component_Photo_TileSmall
 */
class Component_Photo_TileSmall extends Tag_Block
{
    /**
     * @var Model_Photo
     */
    protected $_photo;

    /**
     * @var null|string
     */
    protected $_alt;

    /**
     * @var null|string
     */
    protected $_url;

    /**
     * @var bool
     */
    protected $_clickable = false;

    /**
     * @var bool
     */
    protected $_gallery = false;

    /**
     * @var null|Model_Abstract_Entity
     */
    protected $_owner = null;

    /**
     * @var bool
     */
    protected $_hideTitles = false;

    /**
     * @param Model_Photo $photo
     * @param null|string $alt
     * @param null|string $url
     * @param bool $clickable
     * @param bool $gallery
     */
    public function __construct(Model_Photo $photo, $alt = null, $clickable = false, $url = null, $gallery = false)
    {
        parent::__construct();

        $this->_photo = $photo;
        $this->_alt = $alt;
        $this->_clickable = $clickable;
        $this->_url = $url;
        $this->_gallery = $gallery;

        Helper_Includer::addCSS('media/mod/photos/css/main.css');
    }

    /**
     * @return string
     */
    protected function _render()
    {
        $this->addCSSClass('smalltile');

        $link = new Tag_Span();

        if ($this->_clickable)
        {
            if (empty($this->_url))
            {
                $url = $this->_photo->getPhotoMax();

                if (!empty($url))
                {
                    $link = new Tag_HyperLink(null, $url);
                    $link->addCSSClass('fancybox');

                    if ($this->_gallery)
                    {
                        $link->set('rel', 'gallery');
                    }
                }
            }
            else
            {
                $link = new Tag_HyperLink(null, $this->_url);
            }
        }

        $img = new Tag_Img($this->_getPhoto());

        if (!empty($this->_alt))
        {
            $img->set('alt', $this->_alt);
        }
        else if (!empty($this->_photo->name))
        {
            $link->set('title', $this->_photo->name);
            $img->set('alt', $this->_photo->name);
        }

        $this->add($link);
        $link->add($img);

        return parent::_render();
    }

    /**
     * @return null|string
     */
    protected function _getPhoto()
    {
        return $this->_photo->getPhotoMin();
    }
}
