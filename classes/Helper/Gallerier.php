<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Helper_Gallerier
 */
class Helper_Gallerier
{
    /**
     * Get hashed filename (with dir)
     *
     * @param int $id
     * @return string
     */
    public static function getPath($id)
    {
        $hash = static::hash($id);
        $dir = static::getDir($hash);

        return $dir.'/'.$hash;
    }

    /**
     * Get hashed filename (without dir)
     *
     * @param int $id
     * @return string
     */
    public static function hash($id)
    {
        return md5($id.'NaCl');
    }

    /**
     * Get dir from hash
     *
     * @param string $hash
     * @return string
     */
    public static function getDir($hash)
    {
        return substr($hash, 0, 2);
    }

    /**
     * Check if actual user has privileges to manage photos (is author or admin)
     *
     * @param Model_Photo $photo
     * @return bool
     */
    public static function isAdmin($photo)
    {
        $user = Auth::instance()->get_user();

        if ($user === null) return false;
        if (Auth::instance()->logged_in('admin')) return true;

        $result = false;

        if ($user->id === $photo->author->id)
        {
            $result = true;
        }

        return $result;
    }

    /**
     * Check if actual user has privileges to manage entity (is author or admin)
     *
     * @param Model_Abstract_Entity $entity
     * @return bool
     */
    public static function isEntityAdmin(Model_Abstract_Entity $entity)
    {
        $user = Auth::instance()->get_user();

        if ($user === null) return false;
        if (Auth::instance()->logged_in('admin')) return true;

        $result = false;

        if ($user->id === $entity->getAdmin()->id)
        {
            $result = true;
        }

        return $result;
    }

    /**
     * Include CSS and JS files
     */
    public static function includeFancyboxFiles()
    {
        Helper_Includer::addCSS('media/vendor/fancybox/jquery.fancybox-1.3.4.css', true);
        Helper_Includer::addJS('media/vendor/fancybox/jquery.fancybox-1.3.4.pack.js', 'js-body');
        Helper_Includer::addJS('media/mod/photos/js/main.js', 'js-body');
    }
}
