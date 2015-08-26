<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Component_Photo_TileBig
 */
class Component_Photo_TileBig extends Component_Photo_Tile
{
    /**
     * @return string
     */
    protected function _render()
    {
        $this->addCSSClass('bigtile');
        $this->_gallery = false;
        $this->_hideTitles = true;

        return parent::_render();
    }

    /**
     * @return null|string
     */
    protected function _getPhoto()
    {
        return $this->_photo->getPhotoMed();
    }
}
