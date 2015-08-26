<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Component_Photo_Form
 */
class Component_Photo_Form extends Tag_Block
{
    /**
     * @var Model_Abstract_Entity
     */
    protected $_entity;

    /**
     * @param Model_Abstract_Entity $entity
     */
    public function __construct(Model_Abstract_Entity $entity)
    {
        parent::__construct();

        $this->_entity = $entity;
        Helper_Includer::addCSS('media/mod/photos/css/main.css');
    }

    /**
     * @return string
     */
    protected function _render()
    {
        $this->addCSSClass('photo_form');

        $row = new Tag_Block();
        $row->addCSSClass('row');

        $form = new Tag_Form($this->_entity->getURL().'/dodaj-zdjecie');
        $form->set('enctype', 'multipart/form-data');
        $form->addCSSClass('photo_add');

        $file = new Tag_Form_Input('photo');
        $file->type = 'file';
        $file->set('accept', 'image/jpeg, image/png, image/gif');

        $label = new Tag_Form_Label('Opis:');
        $description = new Tag_Form_Input('name');

        $form->add($file);
        $form->add($label);
        $form->add($description);
        $form->add(new Tag_Form_Submit('Dodaj zdjęcie'));

        $row->add($form);

        $clear = new Tag_Block();
        $clear->addCSSClass('clear');
        $form->add($clear);

        $this->add($form);

        $error = Session::instance()->get('photo-error');

        if (!empty($error))
        {
            $p = new Tag_Paragraph('Błąd!'.PHP_EOL.$error.'.');
            $p->addCSSClass('error');
            $this->add($p);
            $description->setValue(Session::instance()->get('photo-name'));

            Session::instance()->delete('photo-error');
            Session::instance()->delete('photo-name');
        }

        $line = new Tag_Line();
        $line->addCSSClass('space');
        $this->add($line);

        return parent::_render();
    }
}
