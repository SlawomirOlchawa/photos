<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Component_ListWithPhotosSmall
 */
class Component_ListWithPhotosSmall extends Component_List
{
    /**
     * @param Model_Abstract_Entity $entities
     */
    public function __construct(Model_Abstract_Entity $entities)
    {
        parent::__construct($entities);

        $this->addCSSClass('smallphoto_list');

        Helper_Includer::addCSS('media/mod/photos/css/main.css');
    }

    /**
     * @param Model_Abstract_Entity $entity
     * @return Tag_HyperLink
     */
    protected function _getListItem(Model_Abstract_Entity $entity)
    {
        $tile = new Component_Photo_TileSmall($entity->main_photo);
        $name = new Tag_HyperLink(Text::limit_chars($entity->name,45), $entity->getURL());
        $name->addCSSClass('name');

        $result = new Tag_Block();
        $result->add($tile);
        $result->add($name);

        return $result;
    }
}
