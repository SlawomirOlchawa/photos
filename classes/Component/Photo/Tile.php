<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Component_Photo_Tile
 */
class Component_Photo_Tile extends Tag_Block
{
    /**
     * @var Model_Photo
     */
    protected $_photo;

    /**
     * @var null|string
     */
    protected $_url;

    /**
     * @var null|string
     */
    protected $_title;

    /**
     * @var null|string
     */
    protected $_extraTitle;

    /**
     * @var bool
     */
    protected $_admin;

    /**
     * @var bool
     */
    protected $_clickable = true;

    /**
     * @var bool
     */
    protected $_gallery = true;

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
     * @param bool $admin
     * @param null|string $url
     * @param null|string $title
     * @param null|string $extraTitle
     * @param bool $clickable
     * @param bool $gallery
     */
    public function __construct(Model_Photo $photo, $admin = false, $url = null, $title = null, $extraTitle = null, $clickable = true, $gallery = true)
    {
        parent::__construct();

        $this->_photo = $photo;
        $this->_url = $url;
        $this->_title = $title;
        $this->_extraTitle = $extraTitle;
        $this->_admin = $admin;
        $this->_clickable = $clickable;
        $this->_gallery = $gallery;

        Helper_Includer::addCSS('media/mod/photos/css/main.css');
        Helper_Gallerier::includeFancyboxFiles();
    }

    /**
     * @return string
     */
    protected function _render()
    {
        $this->addCSSClass('tile');

        // only for author of photo (or site admin)
        if ($this->_admin)
        {
            $formDelete = new Tag_Form_PostLink('zdjecia/usun', 'Usuń', 'id', $this->_photo->id);
            $formDelete->addCSSClass('admin_button');
            $formDelete->addCSSClass('delete_photo');
            $this->add($formDelete);

            // only for admin of entity (or site admin) AND if this photo isn't already main
            if ((Helper_Gallerier::isEntityAdmin($this->_getOwner()))
                AND ($this->_getOwner()->main_photo->id !== $this->_photo->id))
            {
                $formSetMain = new Tag_Form_PostLink('zdjecia/ustaw-glowne', 'Główne', 'id', $this->_photo->id, false);
                $formSetMain->addCSSClass('admin_button');
                $formSetMain->addCSSClass('set_main_photo');
                $this->add($formSetMain);
            }
        }

        $border = new Tag_Block();
        $border->addCSSClass('border');

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

        $link->addCSSClass('photo');

        if (!empty($this->_photo->name))
        {
            $link->set('title', $this->_photo->name);
        }

        $thumb = new Tag_Block();
        $thumb->addCSSClass('thumb');
        $outer = new Tag_Span();
        $outer->addCSSClass('outer');
        $inner = new Tag_Span();
        $inner->addCSSClass('inner');

        $img = new Tag_Img($this->_getPhoto());

        if (!empty($this->_title))
        {
            $img->set('alt', $this->_title);
        }
        else if (!empty($this->_photo->name))
        {
            $img->set('alt', $this->_photo->name);
        }

        $this->add($border);
        $border->add($link);
        $link->add($thumb);
        $thumb->add($outer);
        $outer->add($inner);
        $inner->add($img);

        if (!$this->_hideTitles)
        {
            if (!empty($this->_url))
            {
                $this->add(new Tag_HyperLink(Text::limit_chars($this->_title, 15), $this->_url));

                if (!empty($this->_extraTitle))
                {
                    $extraTitle = new Tag_Span(Text::limit_chars($this->_extraTitle, 35));
                    $extraTitle->addCSSClass('extra_title');
                    $this->add($extraTitle);
                }
            }
            else
            {
                $title = $this->_title;

                if (empty($title))
                {
                    $title = $this->_photo->name;
                }

                $this->add(new Tag_Span(Text::limit_chars($title, 40)));
            }
        }

        return parent::_render();
    }

    /**
     * @param Model_Abstract_Entity $entity
     */
    public function setOwner(Model_Abstract_Entity $entity)
    {
        $this->_owner = $entity;
    }

    /**
     * @return Model_Abstract_Entity|null
     */
    protected function _getOwner()
    {
        if (empty($this->_owner))
        {
            $this->_owner = $this->_photo->getOwner();
        }

        return $this->_owner;
    }

    /**
     * @return null|string
     */
    protected function _getPhoto()
    {
        return $this->_photo->getPhotoMin();
    }
}
