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

}
