<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Component_ListWithPhotos
 */
class Component_ListWithPhotos extends Tag_Block
{
    /**
     * @var Model_Abstract_Entity
     */
    protected $_entities;

    /**
     * @var string
     */
    protected $_noResultsInfo = 'Brak wyników do wyświetlenia.';

    /**
     * @param Model_Abstract_Entity $entities
     */
    public function __construct(Model_Abstract_Entity $entities)
    {
        parent::__construct();

        $this->_entities = $entities;
        Helper_Includer::addCSS('media/mod/photos/css/main.css');
    }

    /**
     * @return string
     */
    protected function _render()
    {
        $entityList = $this->_entities->findAll();

        if ($entityList->count() === 0)
        {
            $info = new Tag_Paragraph($this->_noResultsInfo);
            $info->addCSSClass('light');
            $this->add($info);
        }
        else
        {
            $list = new Tag_Block();
            $list->addCSSClass('photo_list');
            $this->add($list);

            foreach ($entityList as $entity)
            {
                $link = $this->_getListItem($entity);
                $list->add($link);
            }
        }

        return parent::_render();
    }

    /**
     * @param Model_Abstract_Entity $entity
     * @return Tag_HyperLink
     */
    protected function _getListItem(Model_Abstract_Entity $entity)
    {
        return new Component_Photo_Tile($entity->main_photo, false, $entity->getURL(), $entity->name);
    }
}
