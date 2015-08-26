<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Model_Abstract_Gallery
 *
 * @property int $id
 * @property Model_Photo $photos
 * @property Model_Abstract_Entity $owner
 */
abstract class Model_Abstract_Gallery extends Model_Abstract_Record
{
    /**
     * @var string
     */
    protected $_table_name = 'galleries';

    /**
     * @var array
     */
    protected $_has_many = array
    (
        'photos' => array
        (
            'model' => 'Photo',
            'foreign_key' => 'gallery_id'
        )
    );

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if ($key === 'owner')
        {
            foreach ($this->has_one() as $parent=>$details)
            {
                if ($this->$parent->id != null)
                {
                    $key = $parent;
                }
            }

            if ($key === 'owner')
            {
                return null;
            }
        }

        return parent::get($key);
    }

    /**
     * @return ORM
     */
    public function delete()
    {
        foreach ($this->photos->findAll() as $photo)
        {
            /** @var Model_Photo $photo */
            $photo->delete();
        }

        return parent::delete();
    }
}
