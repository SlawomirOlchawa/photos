<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Model_User
 *
 * @property int $id
 * @property Model_Photo $photos_added
 * @property Model_Gallery $gallery
 * @property Model_Photo $main_photo
 */
class Model_User extends Model_Abstract_User
{
    /**
     * @var string
     */
    protected $_table_name = 'users';

    /**
     * @var array
     */
    protected $_belongs_to = array
    (
        'gallery' => array
        (
            'model' => 'Gallery',
            'foreign_key' => 'gallery_id'
        ),
        'main_photo' => array
        (
            'model' => 'Photo',
            'foreign_key' => 'main_photo_id'
        ),
    );

    /**
     * @var array
     */
    protected $_has_many = array
    (
        'user_tokens' => array('model' => 'User_Token'),
        'roles'       => array('model' => 'Role', 'through' => 'roles_users'),

        // photos added by this user
        'photos_added' => array
        (
            'model' => 'Photo',
            'foreign_key' => 'author_id',
        ),
    );

    /**
     * @return ORM
     */
    public function delete()
    {
        $this->gallery->delete();

        return parent::delete();
    }
}

