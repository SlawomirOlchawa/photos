<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Component_Photos
 */
class Component_Photos extends Container
{
    /**
     * @var Model_Abstract_Entity
     */
    protected $_entity;

    /**
     * @var bool
     */
    protected $_displayForm;

    /**
     * @param Model_Abstract_Entity $entity
     * @param bool $displayForm
     */
    public function __construct(Model_Abstract_Entity $entity, $displayForm = true)
    {
        $this->_entity = $entity;
        $this->_displayForm = $displayForm;

        Helper_Includer::addCSS('media/mod/photos/css/main.css');
        Helper_Gallerier::includeFancyboxFiles();
    }

    /**
     * @return string
     */
    protected function _render()
    {
        $photos = $this->_entity->getPhotos();
        $photos->order_by('created', 'DESC')->limit(36);

        if ($this->_displayForm)
        {
            $this->add(new Tag_Anchor('photos'));
            $this->add(new Component_Photo_Form($this->_entity));
        }

        $this->add(new Component_Photo_List($photos, $this->_entity));

        return parent::_render();
    }
}
