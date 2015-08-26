<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Interface Model_Interface_Photoable
 *
 * @property Model_Abstract_Gallery $gallery
 * @property Model_Photo $main_photo
 */
interface Model_Interface_Photoable
{
    /**
     * @param array $image
     * @param string $text
     * @param Model_Abstract_User|null $author
     * @return Model_Photo
     */
    public function addPhoto($image, $text, Model_Abstract_User $author = null);

    /**
     * @return Model_Photo
     */
    public function getPhotos();
}
