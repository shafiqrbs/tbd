<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Modules\Accounting\App\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Modules\Accounting\App\Entities\AccountHead;
use Modules\Accounting\App\Entities\AccountHeadDetails;
use Modules\Accounting\App\Entities\AccountMasterHead;
use Modules\Accounting\App\Entities\Config;
use Modules\Accounting\App\Entities\TransactionMode;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Core\App\Entities\Customer;
use Modules\Core\App\Entities\User;
use Modules\Core\App\Entities\Vendor;
use Modules\Inventory\App\Entities\Category;


/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 *
 * See https://symfony.com/doc/current/doctrine/repository.html
 *
 * @author Md Shafiqul islam <shafiqabs@gmail.com>
 */
class AccountHeadRepository extends EntityRepository
{



    public function getMotherGroupAccount()
    {

        $query = $this->createQueryBuilder('e');
        $query->join('e.motherAccount','m');
        $query->select('e');
        $query->where('e.motherAccount IS NOT NULL');
        $query->andWhere('m.slug IN (:mother)')->setParameter('mother',array('assets','liabilities'));
        $accountHeads =  $query->getQuery()->getResult();
        $array = array();
        /* @var $head AccountHead */
        foreach ($accountHeads as $head){
            $array[$head->getMotherAccount()->getSlug()][] = $head;
        }

    }

    public function getBalanceSheetAccount($global)
    {
        $accountHead = $this->findBy(array('isParent' => 1),array('name'=>'ASC'));
        $heads = array();
        /* @var $child AccountHead */
        foreach ($accountHead as $row){
            $childs = $this->getChildrenAccount($row->getId());
            if($childs){
                foreach ($childs as $child) {
                    $heads[$row->getId()][] = $child;
                    $subs = $this->getChildrenAccount($child['id'],$global);
                    if ($subs) {
                        foreach ($subs as $sub) {
                            $heads[$child['id']][] = $sub;
                        }
                    }
                }
            }
        }
        return $heads;
    }


    public function getChildrenTransactionAccount($parent = '', $option = '')
    {
        $query = $this->createQueryBuilder('e');
        $query->leftJoin('e.parent','p');
        $query->select('e.id as id');
        $query->addSelect('e.name as name');
        $query->addSelect('e.toIncrease as toIncrease');
        $query->addSelect('e.code as code');
        $query->addSelect('e.source as source');
        $query->addSelect('e.amount as amount');
        $query->addSelect('p.name as parentName');
        $query->where("e.status =1");
        if(!empty($parent)) {
            $query->andWhere("e.parent =:parent");
            $query->setParameter('parent', $parent);
        }
        if(!empty($option)) {
            $query->andWhere("e.globalOption =:option");
            $query->setParameter('option', $option);
        }
        $query->orderBy('e.name', 'ASC');
        return $query->getQuery()->getArrayResult();
    }

    public function getAllChildrenAccount()
    {
        $accountHead = $this->findBy(array('isParent' => 1,'status' => 1),array('name'=>'ASC'));
        $heads = array();
        /* @var $child AccountHead */
        foreach ($accountHead as $row){
            $childs = $this->getChildrenAccount($row->getId());
            if($childs){
                foreach ($childs as $child) {
                    $heads[$row->getId()][] = $child;
                    $subs = $this->getChildrenAccount($child['id']);
                    if ($subs) {
                        foreach ($subs as $sub) {
                            $heads[$child['id']][] = $sub;
                        }
                    }
                }
            }
        }
        return $heads;
    }

    public function cashOverview()
    {
        $accountHead = $this->findBy(array('isParent' => 1,'id' => 1,'status' => 1),array('name'=>'ASC'));
        $heads = array();
        /* @var $child AccountHead */
        foreach ($accountHead as $row) {
            $childs = $this->getChildrenAccount($row->getId());
            if ($childs) {
                foreach ($childs as $child) {
                    if (in_array($child['id'], [3, 30])) {
                        $heads[$row->getId()][] = $child;
                        $subs = $this->getChildrenAccount($child['id']);
                        if ($subs) {
                            foreach ($subs as $sub) {
                                $heads[$child['id']][] = $sub;
                            }
                        }
                    }
                }

            }
        }
        return $heads;
    }


    public function getChildrenAccount($parent = '')
    {
        $query = $this->createQueryBuilder('e');
        $query->leftJoin('e.parent','p');
        $query->select('e.id as id');
        $query->addSelect('e.name as name');
        $query->addSelect('e.toIncrease as toIncrease');
        $query->addSelect('e.code as code');
        $query->addSelect('e.amount as amount','e.credit as credit','e.debit as debit');
        $query->addSelect('e.showAmount as showAmount');
        $query->addSelect('e.source as source');
        $query->addSelect('p.name as parentName');
        $query->where("e.status =1");
        if(!empty($parent)) {
            $query->andWhere("e.parent =:parent");
            $query->setParameter('parent', $parent);
        }
        $query->orderBy('e.name', 'ASC');
        return $query->getQuery()->getArrayResult();

    }

    public function getChildrenAccountHead($parent = '')
    {
        $query = $this->createQueryBuilder('e');
        $query->select('e.id as id');
        $query->addSelect('e.name as name');
        $query->addSelect('e.toIncrease as toIncrease');
        if(!empty($parent)) {
            $query->where("e.parent IN (:parent)");
            $query->setParameter('parent', $parent);
        }
        $query->orderBy('e.name', 'ASC');
        return $query->getQuery()->getResult();

    }

    public function getAccountHeadTrees(){

        $ret = array();
        $parent = array(23,37,9);
        $query = $this->createQueryBuilder('e');
        $query->select('e');
        $query->where("e.parent IN (:parent)");
        $query->setParameter('parent', $parent);
        $query->orderBy('e.name', 'ASC');
        $accountHeads =  $query->getQuery()->getResult();

        foreach( $accountHeads as $cat ){
            if( !$cat->getParent() ){
                continue;
            }
            $key = $cat->getParent()->getName();
            if(!array_key_exists($key, $ret) ){
                $ret[ $cat->getParent()->getName() ] = array();
            }
            $ret[ $cat->getParent()->getName() ][ $cat->getId() ] = $cat;
        }

        return $ret;


    }



    public function getAccountLedger(){

        $parent = array(23,37,9);
        $ret = array();
        $query = $this->createQueryBuilder('e');
        $query->select('e');
        $query->where("e.parent IN (:parent)");
        $query->setParameter('parent', $parent);
        $query->orderBy('e.name', 'ASC');
        $accountHeads =  $query->getQuery()->getResult();
        foreach( $accountHeads as $cat ){
            if( !$cat->getParent() ){
                continue;
            }
            $key = $cat->getParent()->getName();
            if(!array_key_exists($key, $ret) ){
                $ret[ $cat->getParent()->getName() ] = array();
            }
            $ret[ $cat->getParent()->getName() ][ $cat->getId() ] = $cat;
        }
        return $ret;
    }

    public function getExpenseAccountHead(){

        $ret = array();
        $parent = array(23,37);
        $query = $this->createQueryBuilder('e');
        $query->select('e');
        $query->where("e.id IN (:parent)");
        $query->setParameter('parent', $parent);
        $query->orderBy('e.name', 'ASC');
        $accountHeads =  $query->getQuery()->getResult();
        return $accountHeads;

    }


    public function generateAccountHead($configId)
    {
        $em = $this->_em;
        $qb = $this->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->delete('acc_head')
            ->where('config_id =:config_id')
            ->setParameter('config_id', $configId);
        $qb->execute();

        /** @var  Config $config */

        $config = $em->getRepository(Config::class)->find($configId);
        $parentHeads = $em->getRepository(AccountMasterHead::class)->findBy(['parent'=> NULL]);

        /** @var AccountMasterHead $head */

        foreach ($parentHeads as $head){

            $entity = new AccountHead();
            $entity->setConfig($config);
            $entity->setMotherAccount($head->getMotherAccount());
            $entity->setAccountMasterHead($head);
            $entity->setName($head->getName());
            $entity->setDisplayName($head->getName());
            $entity->setSlug($head->getSlug());
            $entity->setHeadGroup('head');
            $entity->setLevel($head->getLevel());
            $entity->setIsPrivate(1);
            $em->persist($entity);
            $em->flush();
            if($head->getChildren()){
                /* @var AccountMasterHead $child */
                foreach ($head->getChildren() as $child){
                    $subHead = new AccountHead();
                    $subHead->setConfig($config);
                    $subHead->setMotherAccount($child->getMotherAccount());
                    $subHead->setAccountMasterHead($child);
                    if($entity){
                        $subHead->setParent($entity);
                    }
                    $subHead->setName($child->getName());
                    $subHead->setDisplayName($child->getName());
                    $subHead->setSlug($child->getSlug());
                    $subHead->setHeadGroup('sub-head');
                    $subHead->setLevel($child->getLevel());
                    $subHead->setIsPrivate(1);
                    $em->persist($subHead);
                    $em->flush();
                    if(empty($subHead->getHeadDetail())){
                        $this->insertUpdateHeadDetails($subHead);
                    }
                    if($child->getChildren()){
                        foreach ($child->getChildren() as $row):
                            $ledger = new AccountHead();
                            $ledger->setConfig($config);
                            if($row->getMotherAccount()) {
                                $ledger->setMotherAccount($row->getMotherAccount());
                            }
                            if($child){
                                    $ledger->setParent($subHead);
                            }
                            $ledger->setAccountMasterHead($row);
                            $ledger->setName($row->getName());
                            $ledger->setDisplayName($row->getName());
                            $ledger->setSlug($row->getSlug());
                            $ledger->setHeadGroup('ledger');
                            $ledger->setLevel($row->getLevel());
                            $ledger->setIsPrivate(1);
                            $em->persist($ledger);
                            $em->flush();
                            if(empty($ledger->getHeadDetail())){
                                $this->insertUpdateHeadDetails($ledger);
                            }
                        endforeach;
                    }

                }

            }
        }
        /*

        $currentAssets = $em->getRepository(TransactionMode::class)->findBy(['config' => $configId,'status'=>1]);
        foreach ($currentAssets as $asset){
            $this->insertTransactionAccount($asset);
        }

        $customers = $em->getRepository(Customer::class)->findBy(['domain' => $config->getDomain(),'status'=>1]);
        foreach ($customers as $customer){
            $this->insertCustomerAccount($config,$customer);
        }

        $vendors = $em->getRepository(Vendor::class)->findBy(['domain' => $config->getDomain(),'status'=>1]);
        foreach ($vendors as $customer){
            $this->insertVendorAccount($config,$customer);
        }

        $users = $em->getRepository(User::class)->findBy(['domain' => $config->getDomain(),'userGroup'=>'user','enabled'=>1]);
        foreach ($users as $user){
            $this->insertUserAccount($config,$user);
        }

        $investors = $em->getRepository(User::class)->findBy(['domain' => $config->getDomain(),'userGroup'=>'investor','enabled'=>1]);
        foreach ($investors as $investor){
             $this->insertCapitalInvestmentAccount($config,$investor);
        }

        $inv = $em->getRepository(\Modules\Inventory\App\Entities\Config::class)->findOneBy(['domain' => $config->getDomain()]);
        $groups = $em->getRepository(Category::class)->findBy(['config' => $inv,'parent'=>null,'status'=>1]);
        foreach ($groups as $group){
            $this->insertCategoryGroupAccount($config,$group);
        }*/
        $config = $this->find($configId);
        return $config;
    }

    public function resetAccountHead($configId)
    {

        $em = $this->_em;
        $qb = $this->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->delete('acc_head')
            ->where('config_id =:config_id')
            ->setParameter('config_id', $configId);
        $qb->execute();
        $config = $em->getRepository(Config::class)->find($configId);
        $parentHeads = $em->getRepository(AccountMasterHead::class)->findBy(['parent'=> NULL]);

        /** @var AccountMasterHead $head */

        foreach ($parentHeads as $head){

            $entity = new AccountHead();
            $entity->setConfig($config);
            $entity->setMotherAccount($head->getMotherAccount());
            $entity->setAccountMasterHead($head);
            $entity->setName($head->getName());
            $entity->setSlug($head->getSlug());
            $entity->setHeadGroup('head');
            $entity->setIsPrivate(1);
            $entity->setLevel($head->getLevel());
            $em->persist($entity);
            $em->flush();
            if($head->getChildren()){
                foreach ($head->getChildren() as $child){
                    $subHead = new AccountHead();
                    $subHead->setConfig($config);
                    $subHead->setMotherAccount($child->getMotherAccount());
                    $subHead->setAccountMasterHead($child);
                    $subHead->setParent($entity);
                    $subHead->setName($child->getName());
                    $subHead->setSlug($child->getSlug());
                    $subHead->setHeadGroup('sub-head');
                    $subHead->setLevel($child->getLevel());
                    $subHead->setIsPrivate(1);
                    $em->persist($subHead);
                    $em->flush();
                    if(empty($subHead->getHeadDetail())){
                        $this->insertUpdateHeadDetails($subHead);
                    }
                }
            }
        }
        $config = $this->find($configId);
        return $config;
    }

    private function insertUpdateHeadDetails(AccountHead $head){

        $em = $this->_em;
        if(empty($em->getRepository(AccountHeadDetails::class)->find($head))){
            $entity = new AccountHeadDetails();
            $entity->setConfig($head->getConfig());
            $entity->setAccount($head);
            $em->persist($entity);
            $em->flush();
        }
    }

    public function insertTransactionAccount(TransactionMode $entity)
    {

        $em = $this->_em;
        $exist = $this->findOneBy(array('transaction' => $entity));
        if (empty($exist)) {
            $parent = '';
            $head = new AccountHead();

            /* @var $config Config */
            $config = $em->getRepository(Config::class)->find($entity->getConfig());
            if($entity->getMethod()->getSlug() == 'cash'){
                $parent = $config->getAccountCash();
            }elseif($entity->getMethod()->getSlug() == 'bank'){
                $parent = $config->getAccountBank();
            }elseif($entity->getMethod()->getSlug() == 'mobile'){
                $parent = $config->getAccountMobile();
            }
            if(!empty($parent)){

                $head->setConfig($entity->getConfig());
                $head->setName($entity->getName());
                $head->setDisplayName($entity->getName());
                $head->setSlug($entity->getSlug());
                if($parent){ $head->setParent($parent);}
                $head->setHeadGroup('ledger');
                $head->setTransaction($entity);
                $head->setLevel(3);
                $head->setMode('debit');
                $head->setIsPrivate(1);
                $em->persist($head);
                $em->flush();
                if(empty($head->getHeadDetail())){
                    $this->insertUpdateHeadDetails($head);
                }
            }

        }

    }

    public function insertCustomerAccount(Config $config , Customer $entity)
    {

        /* @var $entity Customer */

        $em = $this->_em;
        $exist = $this->findOneBy(array('customer' => $entity));
        if(empty($exist)){
            $name = "{$entity->getMobile()}-{$entity->getName()}";
            $head = new AccountHead();

            $head->setConfig($config);
            if($config && $config->getAccountCustomer()) {
                $head->setParent($config->getAccountCustomer());
            }
            $head->setName($name);
            $head->setDisplayName($entity->getName());
            $head->setSlug($entity->getSlug());
            $head->setHeadGroup('ledger');
            $head->setCustomer($entity);
            $head->setLevel(3);
            $head->setMode('debit');
            $em->persist($head);
            $em->flush();
            if(empty($head->getHeadDetail())){
                $this->insertUpdateHeadDetails($head);
            }
        }
    }

    public function insertVendorAccount(Config $config , Vendor $entity)
    {

        /* @var $entity Vendor */

        $em = $this->_em;
        $exist = $this->findOneBy(array('vendor' => $entity));
        if(empty($exist)){
            $name = "{$entity->getMobile()}-{$entity->getCompanyName()}";
            $head = new AccountHead();

            $head->setConfig($config);
            if($config && $config->getAccountVendor()) {
                $head->setParent($config->getAccountVendor());
            }
            $head->setName($name);
            $head->setDisplayName($entity->getCompanyName());
            $head->setSlug($entity->getSlug());
            $head->setHeadGroup('ledger');
            $head->setVendor($entity);
            $head->setLevel(3);
            $head->setMode('credit');
            $em->persist($head);
            $em->flush();
            if(empty($head->getHeadDetail())){
                $this->insertUpdateHeadDetails($head);
            }
        }
    }

    public function insertUserAccount(Config $config , User $user)
    {

        $em = $this->_em;
        /* @var $exist AccountHead */

        $exist = $this->findOneBy(array('user' => $user));
        if(empty($exist)){

            $head = new AccountHead();
            $head->setConfig($config);
            if($config && $config->getAccountUser()){
                $head->setParent($config->getAccountUser());
            }
            $head->setName($user->getName());
            $head->setDisplayName($user->getName());
            $head->setSlug($user->getName());
            $head->setHeadGroup('ledger');
            $head->setUser($user);
            $head->setLevel(3);
            $head->setMode('credit');
            $em->persist($head);
            $em->flush();
            if(empty($head->getHeadDetail())){
                $this->insertUpdateHeadDetails($head);
            }
        }
    }

    public function insertCapitalInvestmentAccount(Config $config , User $user)
    {

        $em = $this->_em;

        /* @var $exist AccountHead */
        $exist = $this->findOneBy(array('user' => $user));
        if(empty($exist)){

            $head = new AccountHead();
            $head->setConfig($config);
            if($config && $config->getCapitalInvestment()){
                $head->setParent($config->getCapitalInvestment());
            }
            $head->setName($user->getName());
            $head->setDisplayName($user->getName());
            $head->setSlug($user->getName());
            $head->setHeadGroup('ledger');
            $head->setUser($user);
            $head->setLevel(3);
            $head->setMode('credit');
            $em->persist($head);
            $em->flush();
            if(empty($head->getHeadDetail())){
                $this->insertUpdateHeadDetails($head);
            }
        }
    }

    public function insertCategoryGroupAccount(Config $config,Category $entity)
    {

        /* @var $exist AccountHead */

        $exist = $this->findOneBy(array('productGroup' => $entity));
        if(empty($exist)){
            $em = $this->_em;
            $head = new AccountHead();
            $head->setConfig($config);
            if($config && $config->getAccountProductGroup()){
                $head->setParent($config->getAccountProductGroup());
            }
            $head->setName($entity->getName());
            $head->setDisplayName($entity->getName());
            $head->setProductGroup($entity);
            $head->setHeadGroup('ledger');
            $head->setLevel(3);
            $head->setIsPrivate(1);
            $head->setMode('debit');
            $em->persist($head);
            $em->flush();
            if(empty($head->getHeadDetail())){
                $this->insertUpdateHeadDetails($head);
            }
        }


    }

    public function insertCategoryAccount(AccountHead $parent,Category $entity)
    {

        /* @var $exist AccountHead */

        $em = $this->_em;
        $head = new AccountHead();
        $head->setConfig($parent->getConfig());
        $head->setName($entity->getName());
        $head->setDisplayName($entity->getName());
        $head->setParent($parent);
        $head->setCategory($entity);
        $head->setLevel(3);
        $head->setMode('debit');
        $head->setHeadGroup('ledger');
        $em->persist($head);
        $em->flush();
        if(empty($head->getHeadDetail())){
            $this->insertUpdateHeadDetails($head);
        }
        return $head;

    }




}
