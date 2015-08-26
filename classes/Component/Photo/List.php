<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Component_Photo_List
 */
class Component_Photo_List extends Tag_Block
{
    /**
     * @var Model_Photo
     */
    protected $_photos;

    /**
     * @var Model_Abstract_Entity
     */
    protected $_owner;

    /**
     * @param Model_Photo $photos
     * @param Model_Abstract_Entity $owner
     */
    public function __construct(Model_Photo $photos, Model_Abstract_Entity $owner = null)
    {
        parent::__construct();

        $this->_photos = $photos;
        $this->_owner = $owner;

        Helper_Includer::addCSS('media/mod/photos/css/main.css');
        Helper_Gallerier::includeFancyboxFiles();
    }

    /**
     * @return string
     */
    protected function _render()
    {
        $this->addCSSClass('photo_list');

        $photosData = $this->_photos->findAll();

        if ($photosData->count() === 0)
        {
            $info = new Tag_Paragraph('Nie ma jeszcze zdjęć.');
            $info->addCSSClass('light');
            $this->add($info);
        }

        foreach ($photosData as $photo)
        {
            $tile = new Component_Photo_Tile($photo, Helper_Gallerier::isAdmin($photo));
            $this->add($tile);

            if (!empty($this->_owner))
            {
                $tile->setOwner($this->_owner);
            }
        }

        return parent::_render();
    }
}
