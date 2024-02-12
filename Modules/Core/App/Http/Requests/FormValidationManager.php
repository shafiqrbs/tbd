<?php

namespace Modules\Core\App\Http\Requests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

/**
 * RequestManager
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */

class FormValidationManager

{
    public function getErrorsFromForm($form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }
}
