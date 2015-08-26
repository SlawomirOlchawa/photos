<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Model_Photo
 *
 * @property int $id
 * @property string|null $name
 * @property datetime $created
 * @property Model_Abstract_User $author
 * @property Model_Gallery $gallery
 */
class Model_Photo extends Model_Abstract_Record
{
    const IMAGES_DIRECTORY = 'zdjecia';
    const SIZE_MIN = 148;
    const SIZE_MED = 286;
    const SIZE_MAX = 768;
    const NO_IMAGE = 'brak.jpg';

    /**
     * @var string
     */
    protected $_table_name = 'photos';

    /**
     * @var string|null
     */
    protected static $_watermarkPath = null;

    /**
     * @var array
     */
    protected $_belongs_to = array
    (
        'author' => array
        (
            'model' => 'User',
            'foreign_key' => 'author_id'
        ),
        'gallery' => array
        (
            'model' => 'Gallery',
            'foreign_key' => 'gallery_id'
        ),
    );

    /**
     * @param string $path
     */
    public static function setWatermarkPath($path)
    {
        static::$_watermarkPath = $path;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules['name'] = array
        (
            array('Model_Abstract_Record::alnum'),
            array('max_length', array(':value', 255)),
        );

        return $rules;
    }

    /**
     * @return array
     */
    public function filters()
    {
        $filters = parent::filters();

        $filters['name'] = $this->_defaultFilters;
        $filters['name'][] = array('Model_Abstract_Record::wordWrapUtf8', array(':value', 15));
        $filters['name'][] = array('Text::limit_chars', array(':value', 254));

        return $filters;
    }

    /**
     * @return Model_Abstract_Entity
     */
    public function getOwner()
    {
        $gallery = $this->gallery;

        if (!$gallery->loaded())
        {
            $gallery->find();
        }

        return $gallery->owner;
    }

    /**
     * @return null|string
     */
    public function getPhotoMin()
    {
        return $this->_getURL('min');
    }

    /**
     * @return null|string
     */
    public function getPhotoMed()
    {
        return $this->_getURL('med');
    }

    /**
     * @return null|string
     */
    public function getPhotoMax()
    {
        return $this->_getURL('max', false);
    }

    /**
     * Save file and generate thumbnails
     *
     * @param array $image
     * @throws Exception
     */
    public function saveImages($image)
    {
        if (
            ! Upload::valid($image) OR
            ! Upload::not_empty($image) OR
            ! Upload::type($image, array('jpg', 'jpeg', 'png', 'gif')) OR
            ! is_numeric($this->id))
        {
            throw new Exception('Nieprawidłowy plik');
        }

        try
        {
            $directory = DOCROOT.self::IMAGES_DIRECTORY.'/';

            $hash = Helper_Gallerier::hash($this->id);
            $dir = Helper_Gallerier::getDir($hash);

            if (!file_exists($directory.'min/'.$dir))
            {
                @mkdir($directory.'min/'.$dir);
                @mkdir($directory.'med/'.$dir);
                @mkdir($directory.'max/'.$dir);
            }

            $name = $hash.'.jpg';
            $path = $dir.'/'.$name;

            if ($file = Upload::save($image, $name, $directory))
            {
                Image::factory($file)->resize(self::SIZE_MIN, self::SIZE_MIN, Image::INVERSE)->save($directory.'min/'.$path, 80);
                Image::factory($file)->resize(self::SIZE_MED, self::SIZE_MED, Image::INVERSE)->save($directory.'med/'.$path, 80);

                $fullImage = Image::factory($file)->resize(self::SIZE_MAX, self::SIZE_MAX, Image::INVERSE);

                if (!empty(static::$_watermarkPath))
                {
                    try
                    {
                        $watermark = Image::factory(static::$_watermarkPath);
                        $xOffset = $fullImage->width - $watermark->width;
                        $yOffset = $fullImage->height - $watermark->height;
                        $fullImage->watermark($watermark, $xOffset, $yOffset, 100);
                    }
                    catch (Exception $e) {}
                }

                $fullImage->save($directory.'max/'.$path, 80);

                unlink($file);
            }
        }
        catch (Exception $e)
        {
            throw new Exception('Nie udało się zapisać pliku');
        }
    }

    /**
     * @return ORM
     */
    public function delete()
    {
        $entity = $this->getOwner();
        $this->_unlinkImages();

        $result = parent::delete();

        if ($entity->main_photo->id == null)
        {
            Model_Photo::setMainPhoto($entity);
        }

        return $result;
    }

    /**
     * @param Model_Abstract_Entity $entity
     * @return Model_Photo
     */
    public static function getPhotos(Model_Abstract_Entity $entity)
    {
        return Model_Photo::_getGallery($entity)->photos;
    }

    /**
     * @param array $image
     * @param Model_Abstract_Entity $entity
     * @param string|null $text
     * @param Model_Abstract_User|null $author
     * @return Model_Photo
     * @throws Exception
     */
    public static function addPhoto($image, Model_Abstract_Entity $entity, $text, Model_Abstract_User $author = null)
    {
        $photo = static::_initPhoto($entity, $text, $author);
        $photo->save();

        // prevent from using old cache
        $entity->reload();

        if ($entity->main_photo->id == null)
        {
            static::setMainPhoto($entity, $photo);
        }

        try
        {
            $photo->saveImages($image);
        }
        catch (Exception $e)
        {
            $photo->delete();

            throw $e;
        }

        return $photo;
    }

    /**
     * Set main photo for entity
     *
     * @param Model_Abstract_Entity $entity
     * @param Model_Photo|null $photo
     */
    public static function setMainPhoto(Model_Abstract_Entity $entity, Model_Photo $photo = null)
    {
        if ($photo == null)
        {
            // find any photo from gallery
            $photo = $entity->getPhotos()->find();
        }

        if (!$photo->loaded()) return;

        $entity->main_photo = $photo;
        $entity->save();
    }

    /**
     * @return Model_Photo
     */
    public static function getLatestPhotos()
    {
        $photos = new Model_Photo();
        $latestPhotos = $photos->order_by('created', 'DESC');

        return $latestPhotos;
    }

    /**
     * Sort results placing items with set main photo before items without photos
     *
     * @param Model_Abstract_Entity $entity
     * @return Model_Abstract_Entity
     */
    public static function orderWithPhotosFirst(Model_Abstract_Entity $entity)
    {
        $expression = DB::expr('CASE WHEN main_photo_id IS NULL THEN 1 ELSE 0 END');
        $entity->order_by($expression);

        return $entity;
    }

    /**
     * @return bool
     */
    protected function _unlinkImages()
    {
        if (!is_numeric($this->id)) return false;

        $path = Helper_Gallerier::getPath($this->id).'.jpg';
        $directory = DOCROOT.self::IMAGES_DIRECTORY.'/';

        @unlink($directory.'min/'.$path);
        @unlink($directory.'med/'.$path);
        @unlink($directory.'max/'.$path);

        return true;
    }

    /**
     * @param string $size
     * @param bool $returnDefault
     * @return null|string
     */
    protected function _getURL($size, $returnDefault = true)
    {
        $dir = null;

        switch ($size)
        {
            case 'min': $dir = 'min'; break;
            case 'med': $dir = 'med'; break;
            case 'max': $dir = 'max'; break;
        }

        if ($dir === null) return null;

        if ($this->id == null)
        {
            if (!$returnDefault) return null;

            return URL::site(self::IMAGES_DIRECTORY.'/'.$dir.'/'.self::NO_IMAGE);
        }

        return URL::site(self::IMAGES_DIRECTORY.'/'.$dir.'/'.Helper_Gallerier::getPath($this->id).'.jpg');
    }

    /**
     * @param Model_Abstract_Entity $entity
     * @return Model_Abstract_Gallery
     */
    protected static function _getGallery(Model_Abstract_Entity $entity)
    {
        if ($entity->gallery->id == null)
        {
            // maybe using old cache (before gallery was created) so we need to
            // reload live data from database to ensure not creating duplicated gallery
            $entity->reload();

            if ($entity->gallery->id == null)
            {
                // class Model_Gallery (extending Model_Abstract_Gallery) must be defined
                // in app and should contain "$_has_one" with entities which can have photos
                $gallery = new Model_Gallery();
                $gallery->save();
                $entity->gallery = $gallery;
                $entity->save();
            }
        }

        return $entity->gallery;
    }

    /**
     * @param Model_Abstract_Entity $entity
     * @param string|null $text
     * @param Model_Abstract_User|null $author
     * @return Model_Photo
     */
    protected static function _initPhoto(Model_Abstract_Entity $entity, $text, Model_Abstract_User $author = null)
    {
        /** @var Model_Photo $photo */
        $photo = new static();

        if (!empty($text))
        {
            $photo->name = $text;
        }

        if (!empty($author))
        {
            $photo->author = $author;
        }

        $photo->gallery = Model_Photo::_getGallery($entity);

        return $photo;
    }
}
