<?php
/**
 * Created by PhpStorm.
 * User: shafiq
 * Date: 10/9/15
 * Time: 8:05 AM
 */

namespace Core\UserBundle\Repository;


use Core\UserBundle\Entity\Profile;
use Core\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ProfileRepository extends EntityRepository {

    public function updateProfile(Profile $entity , $data , $file){

        $em = $this->_em;
        if(isset($file['file'])) {
            $this->fileUploader($entity, $file);
        }
       // var_dump($file);
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

    public function insertNewMember(User $entity, $data = '')
    {
        $em = $this->_em;
        $name = isset($data['registration_name']) ? $data['registration_name'] :'';
        $address = isset($data['registration_address']) ? $data['registration_address'] :'';
        $registration_facebookId = isset($data['registration_facebookId']) ? $data['registration_facebookId'] :'';
        $registration_email = isset($data['registration_email']) ? $data['registration_email'] :'';
        $profile = new Profile();
        $profile->setUser($entity);
        $profile->setMobile($entity->getUsername());
        $profile->setName($name);
        $profile->setAddress($address);
        $profile->setFacebookId($registration_facebookId);
        $profile->setEmail($registration_email);
        $em->persist($profile);
        $em->flush();
    }

    public function insertEcommerce(User $entity, $data = array())
    {
        $em = $this->_em;
        $profile = new Profile();
        $profile->setUser($entity);
        $profile->setMobile($entity->getUsername());
        $profile->setName($data['name']);
        $profile->setAddress($data['address']);
        $profile->setEmail($data['email']);
        $em->persist($profile);
        $em->flush();
    }

    public function updateEcommerce(User $entity, $data = array())
    {

    }

}