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
use Modules\Accounting\App\Entities\AccountJournal;
use Modules\Accounting\App\Entities\AccountJournalItem;


/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 *
 * See https://symfony.com/doc/current/doctrine/repository.html
 *
 * @author Md Shafiqul islam <shafiqabs@gmail.com>
 */
class AccountJournalItemRepository extends EntityRepository
{


    protected function  handleSearchBetween($qb, $form)
    {

        if (isset($form['account_filter_form'])) {
            $data = $form['account_filter_form'];
            $startDate = isset($data['startDate']) ? $data['startDate'] : '';
            $endDate = isset($data['endDate']) ? $data['endDate'] : '';
            $voucher = isset($data['voucher']) ? $data['voucher'] : '';
            $invoice = !empty($data['accountRefNo']) ? $data['accountRefNo'] : '';
            $createdBy = !empty($data['createdBy']) ? $data['createdBy'] : '';

            if (!empty($invoice)) {
                $qb->andWhere($qb->expr()->like("e.accountRefNo", "'%$invoice%'"));
            }
            if (!empty($createdBy)) {
                $qb->andWhere('u.id = :user')->setParameter('user', $createdBy);
            }
            if (!empty($voucher)) {
                $qb->andWhere('v.id = :voucher')->setParameter('voucher', $voucher);
            }

            if (!empty($startDate)) {
                $datetime = new \DateTime($startDate);
                $startDate = $datetime->format('Y-m-d 00:00:00');
                $qb->andWhere("e.generatedDate  >= :startDate");
                $qb->setParameter('startDate', $startDate);
            }

            if (!empty($endDate)) {
                $datetime = new \DateTime($endDate);
                $endDate = $datetime->format('Y-m-d 23:59:59');
                $qb->andWhere("e.generatedDate  <= :endDate");
                $qb->setParameter('endDate', $endDate);
            }

        }
    }

    public function insertDoubleEntry(AccountJournal $journal, $data)
    {
        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb->delete(AccountJournalItem::class, 'e')->where('e.accountJournal = ?1')->setParameter(1,"{$journal->getId()}")->getQuery()->execute();

        foreach ($data['accountHead'] as $key => $value):
            $debit = (isset($data['debit'][$key]) and $data['debit'][$key])? $data['debit'][$key]:0;
            $credit = (isset($data['credit'][$key]) and $data['credit'][$key])? $data['credit'][$key]:0;
            if($value){
                $metaId = isset($data['journalItem'][$key]) ? $data['journalItem'][$key] : 0 ;
                $journalItem = $this->find($metaId);
                if($journalItem){
                    $item = $journalItem;
                }else{
                    $item = new AccountJournalItem();
                }
                $item->setAccountJournal($journal);
                $accountHead = $em->getRepository(AccountHead::class)->find($value);
                $item->setAccountHead($accountHead);
                $item->setDebit($debit);
                $item->setCredit($credit);
                if ($data['subAccountHead'][$key] > 0) {
                    $accountSubHead = $em->getRepository(AccountHead::class)->find($data['subAccountHead'][$key]);
                    $item->setAccountSubHead($accountSubHead);
                }
                $item->setNarration($data['narration'][$key]);
                $em->persist($item);
                $em->flush();;
            }
        endforeach;
        $this->updateSummary($journal);
    }

    public function updateSummary(AccountJournal $entity)
    {
        $em = $this->_em;
        $qb = $this->createQueryBuilder('e');
        $qb->select('SUM(e.debit) as debit','SUM(e.credit) as credit');
        $qb->where('e.accountJournal =:report')->setParameter('report',"{$entity->getId()}");
        $result = $qb->getQuery()->getOneOrNullResult();
        $entity->setAmount($result['debit']);
        $entity->setDebit($result['debit']);
        $entity->setCredit($result['credit']);
        $em->flush();
        return $result;

    }

    public function insertChild(AccountJournalItem $entity)
    {
        $em = $this->_em;
        $item = new AccountJournalItem();
        $item->setAccountJournal($entity->getAccountJournal());
        $item->setParent($entity);
        $item->setAmount($entity->getAmount());
        $item->setFirstChild(true);
        if($entity->getMode() == "Debit"){
            $item->setBalanceMode('Cr.');
            $item->setMode("Credit");
            $item->setCredit($entity->getAmount());
        }else{
            $item->setMode("Debit");
            $item->setBalanceMode('Dr.');
            $item->setDebit($entity->getAmount());
        }
        $em->persist($item);
        $em->flush();
    }

    public function insertInitialEntry(AccountJournal $entity)
    {
        $em = $this->_em;
        $item = new AccountJournalItem();
        $item->setAccountJournal($entity);
        $item->setFirstChild(true);
        $em->persist($item);
        $em->flush();
    }

    public function accountLedger($data){

        $startDate = new \DateTime($data['startDate']);
        $startDate = $startDate->format('Y-m-d 00:00:01');
        $endDate = new \DateTime($data['endDate']);
        $endDate = $endDate->format('Y-m-d 23:59:59');
        $head= $data['accountSubHead'];
        $qb1 = $this->createQueryBuilder('ji');
        $qb1->select('(SUM(ji.debit) - SUM(ji.credit)) as balance');
        $qb1->addSelect('SUM(ji.debit) as debit','SUM(ji.credit) as credit');
        $qb1->join('ji.accountJournal','e');
        $qb1->where('e.generatedDate < :today_startdatetime') ->setParameter('today_startdatetime', $startDate);
        $qb1->andWhere('ji.accountSubHead = :subAccountHead')->setParameter('subAccountHead',$head);
        $openingBalance = $qb1->getQuery()->getOneOrNullResult();
        $opening = $openingBalance['balance'];

        $qb2 = $this->createQueryBuilder('ji');
        $qb2->select('e.id as id');
        $qb2->join('ji.accountJournal','e');
        $qb2->where('e.generatedDate >= :today_startdatetime') ->setParameter('today_startdatetime', $startDate);
        $qb2->andWhere('e.generatedDate <= :today_enddatetime')->setParameter('today_enddatetime', $endDate);
        $qb2->andWhere('ji.accountSubHead = :subAccountHead')->setParameter('subAccountHead',$head);
        $qb2->groupBy('e.id');
        $records = $qb2->getQuery()->getArrayResult();

        $qb3 = $this->createQueryBuilder('ji');
        $qb3->addSelect('e.id as journalId','ji.id as journalItemId','ji.mode as mode','ji.amount as amount','ji.debit as debit','ji.credit as credit','e.generatedDate as generatedDate');
        $qb3->addSelect('ash.name as account','ash.id as accountId');
        $qb3->join('ji.accountJournal','e');
        $qb3->join('ji.accountSubHead','ash');
        $qb3->where('e.generatedDate >= :today_startdatetime') ->setParameter('today_startdatetime', $startDate);
        $qb3->andWhere('e.generatedDate <= :today_enddatetime')->setParameter('today_enddatetime', $endDate);
        $qb3->andWhere('ji.accountSubHead = :subAccountHead')->setParameter('subAccountHead',$head);
        $qb3->orderBy('e.generatedDate','ASC');
        $qb3->addOrderBy('e.id','ASC');
        $records2 = $qb3->getQuery()->getArrayResult();

        $arrayData1 = [];
        foreach ($records2 as $row){
            $mode = $row['mode'] == 'Credit' ? 'Debit': 'Credit';
            $amount = $row['mode'] == 'credit' ? $row['debit']: $row['credit'];
            $qb4 = $this->createQueryBuilder('ji');
            $qb4->select('e.id as journalId','e.accountRefNo as accountRefNo','ji.id as journalItemId','ji.mode as mode','e.generatedDate as generatedDate','ji.amount as amount','ji.debit as debit','ji.credit as credit');
            $qb4->addSelect('ash.name as account','ash.id as accountId');
            $qb4->addSelect('v.name as voucher');
            $qb4->join('ji.accountJournal','e');
            $qb4->join('ji.accountSubHead','ash');
            $qb4->join('e.voucher','v');
            $qb4->where('e.id = :journal')->setParameter('journal',$row['journalId']);
            $qb4->andWhere('ji.mode = :mode')->setParameter('mode',$mode);
            $arrayData12 = $qb4->getQuery()->getArrayResult();
            foreach ($arrayData12 as $hr){
                $date = $row['generatedDate']->format('Y-m-d');
                if(count($arrayData12) > 1){
                    $arrayData1[$date][$hr['journalId']][] = $hr;
                }else{
                    $arrayData1[$date][$hr['journalId']][] = [
                        'journalId'=> $hr['journalId'],
                        'accountRefNo'=> $hr['accountRefNo'],
                        'voucher'=> $hr['voucher'],
                        "journalItemId" => $hr['journalItemId'],
                        "mode" =>$hr['mode'],
                        "amount" => $row['amount'],
                        "credit" => $row['credit'],
                        "debit" => $row['debit'],
                        "generatedDate" => $hr['generatedDate'],
                        "account" => $hr['account'],
                        "accountId" => $hr['journalId'],
                    ];
                }
            }
        }


        $head= $data['accountSubHead'];
        $qb = $this->createQueryBuilder('ji');
        $qb->join('ji.accountJournal','e');
        $qb->join('ji.accountSubHead','ash');
        $qb->join('e.voucher','v');
        $qb->select('e.accountRefNo');
        $qb->addSelect('e.id as journalId','ji.id as journalItemId','ji.mode as mode','ji.debit as debit','ji.credit as credit','e.generatedDate as generatedDate');
        $qb->addSelect('ash.name as account','ash.id as accountId');
        $qb->addSelect('v.name as voucher');
        $qb->where('e.id IN (:records)') ->setParameter('records', $records);
      //  $qb->andWhere('ji.accountSubHead != :subAccountHead')->setParameter('subAccountHead',$head);
        $this->handleSearchBetween($qb, $data);
        $qb->orderBy('e.generatedDate','ASC');
        $qb->addOrderBy('e.id','ASC');
        $result = $qb->getQuery()->getArrayResult();
        $arrayData = [];
        foreach ($result as $row){
            $date = $row['generatedDate']->format('Y-m-d');
            $dateJournal = "{$date}";
            $arrayData[$dateJournal][]=$row;
        }

        $rows = array('opening'=>$opening,'entities' => $result,'arrayData' => $arrayData1);
        return $rows;
    }

    public function getFindCUstomerInvoiceExist($entity){

        $qb = $this->createQueryBuilder('e');
        $qb->where('e.customerInvoice = :customerInvoice') ->setParameter('customerInvoice', $entity);
        $qb->andWhere('e.accountJournal IS NULL');
        $result = $qb->getQuery()->getResult();
        return $result;


    }

    public function insertCustomerPaymentReceive(OrderDelivery $order, $data)
    {
        $em  = $this->_em;
        $no = (isset($data['receiveInitiatNo']) and $data['receiveInitiatNo'])? $data['receiveInitiatNo']: '';
        $date = (isset($data['receiveInitiatDate']) and $data['receiveInitiatDate'])? $data['receiveInitiatDate']: '';
        $bank = (isset($data['bank']) and $data['bank'])? $data['bank']: '';
        $branchName = (isset($data['branchName']) and $data['branchName'])? $data['branchName']: '';
        $receivedFrom = (isset($data['receivedFrom']) and $data['receivedFrom'])? $data['receivedFrom']: '';
        $exist = $this->getFindCUstomerInvoiceExist($order->getId());
        if(empty($exist) and $bank){
            $entity = new AccountJournalItem();
            $entity->setCustomerInvoice($order);
            $entity->setAccountSubHead($order->getBankAccountSubHead());
            $entity->setAccountHead($order->getBankAccountSubHead()->getParent());
            $entity->setInitiatNo($no);
            $entity->setInitiatDate(new \DateTime($date));
            $entity->setBank($em->getRepository(Bank::class)->find($bank));
            $entity->setBranchName($branchName);
            $entity->setReceivedFrom($receivedFrom);
            $entity->setDebit($order->getAmount());
            $entity->setAmount($order->getAmount());
            $em->persist($entity);
            $em->flush();
            return $entity;
        }
        return $exist;
    }

    public function bankReconcilation($data,$mode)
    {
        $head= $data['accountBankSubHead'];
        $startDate = new \DateTime($data['startDate']);
        $startDate = $startDate->format('Y-m-d 00:00:01');
        $endDate = new \DateTime($data['endDate']);
        $endDate = $endDate->format('Y-m-d 23:59:59');

        $qb1 = $this->createQueryBuilder('aj');
        $qb1->select('(SUM(aj.debit) - SUM(aj.credit)) as balance');
        $qb1->addSelect('SUM(aj.debit) as debit','SUM(aj.credit) as credit');
        $qb1->join('aj.accountJournal','e');
        $qb1->where("aj.accountSubHead IN(:accountHeads)")->setParameter('accountHeads', $head);
        $bookBalance = $qb1->getQuery()->getOneOrNullResult();

        $qb2 = $this->createQueryBuilder('aj');
        $qb2->select('SUM(aj.debit) as debit','SUM(aj.credit) as credit');
        $qb2->join('aj.accountJournal','e');
        $qb2->where('aj.reconcileDate IS NULL');
        $qb2->where('aj.status IS NULL');
        $qb2->andWhere("aj.accountSubHead IN(:accountHeads)")->setParameter('accountHeads', $head);
        $bookBalanceWithOutReconcile = $qb2->getQuery()->getOneOrNullResult();

/*
        echo "Balance as Company Books:{$bookBalance['balance']}<p/>";
        echo $debitBalance = ($bookBalance['debit'] - $bookBalanceWithOutReconcil['debit']);
         echo "Bedit<p/>";
        echo $debitBalance = ($bookBalance['debit'] - $bookBalanceWithOutReconcil['debit']);
        echo "Credit<p/>";
        echo $creditBalance = ($bookBalance['credit'] - $bookBalanceWithOutReconcil['credit']);

        exit;*/

        $qb3 = $this->createQueryBuilder('aj');
        $qb3->select('SUM(aj.debit) as debit','SUM(aj.credit) as credit');
        $qb3->join('aj.accountJournal','e');
        $qb3->where('aj.reconcileDate IS NOT NULL');
        $qb3->where('aj.status IS NOT NULL');
        $qb3->andWhere("aj.accountSubHead IN(:accountHeads)")->setParameter('accountHeads', $head);
        $reconcilationResult = $qb3->getQuery()->getOneOrNullResult();

        $reconcilationBalance = ($reconcilationResult['debit'] - $reconcilationResult['credit']);


        $qb = $this->createQueryBuilder('aj');
        $qb->leftJoin('aj.bank','b');
        $qb->leftJoin('aj.accountSubHead','sah');
        $qb->leftJoin('aj.accountHead','ah');
        $qb->leftJoin('aj.accountJournal','e');
        $qb->leftJoin('e.voucher','v');
        $qb->select('ah.name as accountHead , sah.name as subAccountHead , COALESCE(aj.amount,0) as amount, COALESCE(aj.debit,0) as debit, COALESCE(aj.credit,0) as credit');
        $qb->addSelect('aj.id as id','aj.initiatDate as initiatDate','aj.reconcileDate as reconcileDate','aj.initiatNo as initiatNo','aj.receivedFrom as receivedFrom','aj.bankName as bankName','aj.branchName as branchName','aj.forwardingName as forwardingName','aj.mode as mode','aj.accountMode as accountMode','aj.status as status');
        $qb->addSelect('e.accountRefNo as accountRefNo','e.generatedDate as created');
        $qb->addSelect('v.name as voucher');
        $qb->addSelect('b.name as bank');
        $qb->where("aj.accountSubHead IN(:accountHeads)")->setParameter('accountHeads', $head);
        if($mode == "archive"){
            $qb->andWhere("aj.status=true");
        }else{
            //$qb->andWhere("e.status !=1");
        }
        $qb->orderBy('ah.name','ASC')->addOrderBy('sah.name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        $array = array ('bookBalance' => $bookBalance['balance'],'reconcilationBalance'=>$bookBalanceWithOutReconcile,'records' => $result);
        return $array;
    }

    public function getGroupByAccountHead($heads,$data){

        if(empty($data)){
            $datetime = new \DateTime("now");
            $tillDate = $datetime->format('Y-m-d 23:59:59');
        }elseif(!empty($data['tillDate']) and !empty($data['tillDate'])){
            $datetime = new \DateTime($data['tillDate']);
            $tillDate = $datetime->format('Y-m-d 23:59:59');
        }

        $qb = $this->createQueryBuilder('e');
        $qb->join('e.accountJournal','aj');
        $qb->join('e.accountHead','accountHead');
        $qb->join('accountHead.parent','parent');
        $qb->select('sum(e.amount) as amount, sum(e.debit) as debit , sum(e.credit) as credit, accountHead.name as name , parent.name as parentName,parent.id as parentId, accountHead.id as accountHeadId, accountHead.toIncrease, accountHead.code as code');
        $qb->where("parent.slug IN (:parent)")->setParameter('parent',$heads);
    //    $qb->andWhere("aj.generatedDate <= :tillDate")->setParameter('tillDate', $tillDate);
        $qb->groupBy('e.accountHead');
        $qb->orderBy('parentName','ASC')->orderBy('name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        return $result;
    }

    public function trailBalance($data){

        $qb = $this->createQueryBuilder('e');
        $qb->join('e.accountJournal','aj');
        $qb->join('e.accountSubHead','accountHead');
        $qb->join('accountHead.parent','parent');
        $qb->select('sum(e.amount) as amount, sum(e.debit) as debit , sum(e.credit) as credit, accountHead.name as name , parent.name as parentName,parent.id as parentId, accountHead.id as accountHeadId, accountHead.toIncrease, accountHead.code as code');
        $startDate = new \DateTime($data['startDate']);
        $startDate = $startDate->format('Y-m-d 00:00:00');
        $endDate = new \DateTime($data['endDate']);
        $endDate = $endDate->format('Y-m-d 23:59:59');
        $qb->where("aj.generatedDate  >= :startDate")->setParameter('startDate', $startDate);
        $qb->andWhere("aj.generatedDate  <= :endDate")->setParameter('endDate', $endDate);
        $qb->groupBy('e.accountHead');
        $qb->orderBy('parentName','ASC')->orderBy('name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        return $result;
    }

    public function incomeExpenseBalance($data){

        $qb = $this->createQueryBuilder('e');
        $qb->join('e.accountJournal','aj');
        $qb->join('e.accountSubHead','accountHead');
        $qb->join('accountHead.parent','parent');
        $qb->select('sum(e.amount) as amount, sum(e.debit) as debit , sum(e.credit) as credit, accountHead.name as name , parent.name as parentName,parent.id as parentId, accountHead.id as accountHeadId, accountHead.toIncrease, accountHead.code as code');
        $startDate = new \DateTime($data['startDate']);
        $startDate = $startDate->format('Y-m-d 00:00:00');
        $endDate = new \DateTime($data['endDate']);
        $endDate = $endDate->format('Y-m-d 23:59:59');
        $qb->where("aj.generatedDate  >= :startDate")->setParameter('startDate', $startDate);
        $qb->andWhere("aj.generatedDate  <= :endDate")->setParameter('endDate', $endDate);
        $qb->groupBy('e.accountHead');
        $qb->orderBy('parentName','ASC')->orderBy('name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        return $result;
    }


    public function getGroupBaseExpense($heads,$data){

        $qb = $this->createQueryBuilder('e');
  //      $qb->join('e.accountJournal','aj');
        $qb->join('e.accountHead','accountHead');
        $qb->join('accountHead.parent','parent');
        $qb->select('sum(e.amount) as amount, sum(e.debit) as debit , sum(e.credit) as credit, accountHead.name as name , parent.name as parentName,parent.id as parentId, accountHead.id as accountHeadId, accountHead.toIncrease, accountHead.code as code');
        $qb->where("accountHead.slug IN (:parent)")->setParameter('parent',$heads);
        //    $startDate = new \DateTime($data['startDate']);
     //   $startDate = $startDate->format('Y-m-d 00:00:00');
     //   $endDate = new \DateTime($data['endDate']);
     //   $endDate = $endDate->format('Y-m-d 23:59:59');
    //    $qb->where("aj.generatedDate  >= :startDate")->setParameter('startDate', $startDate);
   //     $qb->andWhere("aj.generatedDate  <= :endDate")->setParameter('endDate', $endDate);
        $qb->groupBy('e.accountHead');
        $qb->orderBy('accountHead.name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        dd($result);
        return $result;
    }

    public function getGroupBaseIncome($heads,$data){

        $qb = $this->createQueryBuilder('e');
        //      $qb->join('e.accountJournal','aj');
        $qb->join('e.accountHead','accountHead');
        $qb->join('accountHead.parent','parent');
        $qb->select('sum(e.amount) as amount, sum(e.debit) as debit , sum(e.credit) as credit, accountHead.name as name , parent.name as parentName,parent.id as parentId, accountHead.id as accountHeadId, accountHead.toIncrease, accountHead.code as code');
        $qb->where("accountHead.slug IN (:parent)")->setParameter('parent',$heads);
        //    $startDate = new \DateTime($data['startDate']);
        //   $startDate = $startDate->format('Y-m-d 00:00:00');
        //   $endDate = new \DateTime($data['endDate']);
        //   $endDate = $endDate->format('Y-m-d 23:59:59');
        //    $qb->where("aj.generatedDate  >= :startDate")->setParameter('startDate', $startDate);
        //     $qb->andWhere("aj.generatedDate  <= :endDate")->setParameter('endDate', $endDate);
        $qb->groupBy('e.accountHead');
        $qb->orderBy('accountHead.name','ASC');
        $results = $qb->getQuery()->getArrayResult();

        $qb1 = $this->createQueryBuilder('e');
        //      $qb->join('e.accountJournal','aj');
        $qb1->join('e.accountHead','accountHead');
        $qb1->join('accountHead.parent','parent');
        $qb1->select('sum(e.amount) as amount, sum(e.debit) as debit , sum(e.credit) as credit, parent.name as parentName,parent.id as parentId');
        $qb1->where("accountHead.slug IN (:parent)")->setParameter('parent',$heads);
        //    $startDate = new \DateTime($data['startDate']);
        //   $startDate = $startDate->format('Y-m-d 00:00:00');
        //   $endDate = new \DateTime($data['endDate']);
        //   $endDate = $endDate->format('Y-m-d 23:59:59');
        //    $qb->where("aj.generatedDate  >= :startDate")->setParameter('startDate', $startDate);
        //     $qb->andWhere("aj.generatedDate  <= :endDate")->setParameter('endDate', $endDate);
        $qb1->groupBy('parent.id');
        $qb1->orderBy('parent.name','ASC');
        $parents = $qb1->getQuery()->getArrayResult();
        $datas = array();
        foreach ($results as $row):
            $datas[$row['parentId']] = $row;
        endforeach;
        return $data = array('parents' => $parents,'heads' => $datas);
    }



}
