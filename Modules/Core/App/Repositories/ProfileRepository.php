<?php
/**
 * Created by PhpStorm.
 * User: shafiq
 * Date: 10/9/15
 * Time: 8:05 AM
 */

namespace Modules\Core\App\Repositories;

use Doctrine\ORM\EntityRepository;
use Modules\Core\App\Entities\Profile;

class ProfileRepository extends EntityRepository {

    public function updateProfile(Profile $entity , $data , $file){

        $em = $this->_em;
        if(isset($file['file'])) {
            $this->fileUploader($entity, $file);
        }
        if(isset($data['name']) && $data['name'] != null)
            $entity->setName($data['name']);
        if(isset($data['mobile']) && $data['mobile'] != null)
            $entity->setMobile($data['mobile']);
        if(isset($data['interest']) && $data['interest'] != null)
            $entity->setInterest($data['interest']);
        if(isset($data['profession']) && $data['profession'] != null)
            $entity->setProfession($data['profession']);
        if(isset($data['designation']) && $data['designation'] != null)
            $entity->setDesignation($data['designation']);
        if(isset($data['dob']) && $data['dob'] != null)
            $entity->setDob($data['dob']);
        if(isset($data['bloodGroup']) && $data['bloodGroup'] != null)
            $entity->setBloodGroup($data['bloodGroup']);
        if(isset($data['address']) && $data['address'] != null)
            $entity->setAddress($data['address']);
        if(isset($data['about']) && $data['about'] != null)
            $entity->setAbout($data['about']);
        $em->persist($entity);
        $em->flush();

    }

    public function fileUploader(Profile $entity, $file = '')
    {
        $em = $this->_em;
        if(isset($file['file'])){
            $img = $file['file'];
            $fileName = $img->getClientOriginalName();
            $imgName =  uniqid(). '.' .$fileName;
            $img->move($entity->getUploadDir(), $imgName);
            $entity->setPath($imgName);
        }
        $em->persist($entity);
        $em->flush();
    }



}
