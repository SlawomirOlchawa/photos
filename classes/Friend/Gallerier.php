<?php
/**
 * @author Sławomir Olchawa <slawooo@gmail.com>
 */

/**
 * Class Friend_Gallerier
 */
class Friend_Gallerier extends Friend_Abstract_Entity
{
    /**
     * Add photo to entity and redirect back
     */
    public function addPhoto()
    {
        $entity = $this->getEntity();
        $authInstance = Auth::instance();
        $description = $this->_controller->request->post('name');

        if ($authInstance->logged_in())
        {
            try
            {
                if ($this->_controller->request->method() == Request::POST)
                {
                    if (isset($_FILES['photo']))
                    {
                        $user = new Model_User($authInstance->get_user());
                        Model_Photo::addPhoto($_FILES['photo'], $entity, $description, $user);
                    }
                }
            }
            catch (Exception $e)
            {
                $error = $this->_controller->getErrorMessage($e);

                Session::instance()->set('photo-error', $error);
                Session::instance()->set('photo-name', $description);
            }
        }

        $this->_controller->redirect($entity->getURL().'#photos');
    }

    /**
     * Set photo as cover and redirect back to entity page
     */
    public function setMainPhoto()
    {
        $id = $this->_controller->request->post('id');
        $photo = new Model_Photo($id);

        if ($photo->loaded())
        {
            $token = $this->_controller->request->post('token');
            $entity = $photo->getOwner();

            if (!empty($token) AND Helper_Token::valid($token)
                AND Helper_Gallerier::isAdmin($photo) AND Helper_Gallerier::isEntityAdmin($entity))
            {
                Model_Photo::setMainPhoto($entity, $photo);
            }

            $this->_controller->redirect($entity->getURL());
        }

        exit();
    }

    /**
     * Delete photo and redirect back to entity page
     */
    public function deletePhoto()
    {
        $id = $this->_controller->request->post('id');
        $photo = new Model_Photo($id);

        if ($photo->loaded())
        {
            $token = $this->_controller->request->post('token');
            $entity = $photo->getOwner();

            if (!empty($token) AND Helper_Token::valid($token) AND Helper_Gallerier::isAdmin($photo))
            {
                $photo->delete();
            }

            $this->_controller->redirect($entity->getURL().'#photos');
        }

        exit();
    }
}
