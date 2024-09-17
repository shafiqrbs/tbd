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
use Modules\Accounting\App\Entities\AccountJournal;
use Modules\Accounting\App\Entities\AccountJournalItem;
use Modules\Accounting\App\Entities\AccountVoucher;
use Modules\Accounting\App\Entities\Transaction;
use Modules\Core\App\Entities\User;
use Modules\Inventory\App\Entities\PurchaseItem;


/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 *
 * See https://symfony.com/doc/current/doctrine/repository.html
 *
 * @author Md Shafiqul islam <shafiqabs@gmail.com>
 */
class AccountJournalRepository extends EntityRepository
{


    protected function handleSearchBetween($qb, $form)
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

    public function findSearchQuery($config, User $user, $data = []): array
    {

        $sort = isset($data['sort']) ? $data['sort'] : 'e.generatedDate';
        $direction = isset($data['direction']) ? $data['direction'] : 'DESC';
        $entryMode = isset($data['entryMode']) ? $data['entryMode'] : 'voucher';
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.id', 'e.accountRefNo as invoice','e.amount as amount','e.created as created','e.updated as updated','e.generatedDate as generatedDate', 'e.process as process', 'e.waitingProcess as waitingProcess');
        $qb->addSelect('rto.id as reportTo','rto.name as reportToName');
        $qb->addSelect('u.name as createdBy','u.id as userId');
        $qb->addSelect('a.name as approveBy');
        $qb->addSelect('v.name as voucher','v.shortName as vouchershortName');
        $qb->leftJoin('e.reportTo', 'rto');
        $qb->leftJoin('e.approvedBy', 'a');
        $qb->leftJoin('e.createdBy', 'u');
        $qb->leftJoin('e.voucher', 'v');
        $qb->where("e.module = 'account-journal'");
        $qb->andWhere("e.entryMode = '{$entryMode}'");
        if($data['mode'] == "in-progress") {
            $qb->andWhere('e.waitingProcess =:process')->setParameter('process', "In-progress");
        }elseif($data['mode'] == "approve"){
            $qb->andWhere('e.reportTo =:report')->setParameter('report',"{$user->getId()}");
        }elseif($data['mode'] == "new"){
            $qb->andWhere('e.waitingProcess =:process')->setParameter('process', "New");
        }elseif($data['mode'] == "list"){
            $qb->andWhere('u.id = :initior')->setParameter('initior', $user->getId());
        }elseif($data['mode'] == "archive"){
            $qb->andWhere('e.waitingProcess IN (:process)')->setParameter('process', ["Approved","Closed"]);
        }
        $this->handleSearchBetween($qb, $data);
        $qb->orderBy("{$sort}", $direction);
        $result = $qb->getQuery()->getArrayResult();
        return $result;

    }

    public function getPreviousJournalNarration($q)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.description as name');
        $qb->where($qb->expr()->like("e.description", "'$q%'"  ));
        $qb->groupBy('e.description');
        $qb->setMaxResults( '50' );
        $result = $qb->getQuery()->getArrayResult();
        return $result;
    }

    public function getLastJournal()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where('e.waitingProcess =:process')->setParameter('process', "In-progress");
        $qb->setMaxResults( 1);
        $qb->orderBy('e.id','DESC');
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    public function insertOpeningPurchase(PurchaseItem $purchaseItem)
    {
        $em  = $this->_em;
        $exist = $this->findOneBy(['purchaseItem' => $purchaseItem]);
        $voucherType = $em->getRepository(AccountVoucher::class)->findOneBy(['slug'=>'ov']);
        if(empty($exist)){
            $entity = new AccountJournal();
            $entity->setPurchaseItem($purchaseItem);
            $entity->setVoucher($voucherType);
            $em->persist($entity);
            $em->flush();
            $em->getRepository(Transaction::class)->openingStockTransaction($entity,$purchaseItem);
            return $entity;
        }
        return $exist;
    }

    public function insertJournalOrderDelivery(OrderDelivery $order, $data)
    {
        $em  = $this->_em;
        $exist = $order->getAccountJournal();
        if(empty($exist)){
            $entity = new AccountJournal();
            $entity->setOrderDelivery($order);
            $em->persist($entity);
            $em->flush();
            return $entity;
        }
        return $exist;
    }

    public function insertJournalOrderDeliveryReturn(OrderDeliveryReturn $order, $data)
    {
        $em  = $this->_em;
        $exist = $order->getAccountJournal();
        if(empty($exist)){
            $entity = new AccountJournal();
            $entity->setOrderDelivery($order);
            $em->persist($entity);
            $em->flush();
            return $entity;
        }
        return $exist;
    }

    public function accountDayBook($data){

        $startDate = new \DateTime($data['endDate']);
        $startDate = $startDate->format('Y-m-d 00:00:01');
        $endDate = new \DateTime($data['endDate']);
        $endDate = $endDate->format('Y-m-d 23:59:59');
        $qb = $this->createQueryBuilder('e');
        $datetime = new \DateTime($startDate);
        $startDate = $datetime->format('Y-m-d 00:00:00');
        $qb->where("e.generatedDate  >= :startDate");
        $qb->setParameter('startDate', $startDate);
        $datetime = new \DateTime($endDate);
        $endDate = $datetime->format('Y-m-d 23:59:59');
        $qb->andWhere("e.generatedDate  <= :endDate");
        $qb->setParameter('endDate', $endDate);
        $qb->andWhere('e.waitingProcess NOT IN (:process)')->setParameter('process', ["New"]);
        $qb->orderBy('e.generatedDate','DESC');
        $result = $qb->getQuery()->getResult();
        return $result;

    }

    public function accountDayBookxxx($data){

        $day = isset($data['day'])? $data['day'] :'';
        $month = isset($data['month'])? $data['month'] :$month;
        $year = isset($data['financialYear'])? $data['financialYear'] :"2023-2024";

        $sql = "select count(pr.id) as total, DATE_FORMAT(pr.generatedDate,'%M') as month,(pr.financial_year) as year
from acc_journal_item as pi
join acc_journal  as pr ON  pi.account_journal_id = pr.id
where pr.process IN('In-progress','Approved') AND DATE_FORMAT(pr.generatedDate,'%d') =:day AND MONTHNAME(pr.generatedDate) =:month AND pr.financial_year =:year pr.financial_year =:year
group by month";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('day', $day);
        $stmt->bindValue('month', $month);
        $stmt->bindValue('year', $year);
        $stmt->execute();
        $result =  $stmt->fetchAll();
        return $result;
    }


    public function accountMonthlyDayBook($financial,$data){

        $year = isset($data['financialYear'])? $data['financialYear'] :$financial;
        $sql = "select count(pr.id) as total, DATE_FORMAT(pr.generatedDate,'%M') as month,(pr.financial_year) as year
        from acc_journal as pr
        where  pr.financial_year =:year group by month ORDER BY MONTH(pr.generatedDate) asc";
        $qb = $this->getEntityManager()->getConnection()->prepare($sql);
        $qb->bindValue('year', $year);
        $qb->execute();
        $result =  $qb->fetchAll();
        return $result;
    }

    public function accountDailyDayBook($data){

        $month = 'December';
        $month = isset($data['month'])? $data['month'] :$month;
        $year = isset($data['financialYear'])? $data['financialYear'] :"2023-2024";
        $sql = "SELECT
        COUNT(pr.id) AS total,
        DATE_FORMAT(pr.generatedDate,'%d-%m-%Y') AS date, DATE_FORMAT(pr.generatedDate,'%d') AS day, MONTHNAME(pr.generatedDate) as month ,(pr.financial_year) as year FROM
        acc_journal AS pr
        WHERE  pr.process IN('In-progress','Approved') AND MONTHNAME(pr.generatedDate) =:month AND pr.financial_year =:year
        GROUP BY date ORDER BY date ASC";
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('month', $month);
        $stmt->bindValue('year', $year);
        $stmt->execute();
        $results =  $stmt->fetchAll();
        return $results;

    }


    public function insertPaymentReceiveContraTransaction(AccountPaymentReceive $entity)
    {
        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb->delete(AccountJournal::class, 'e')->where('e.paymentReceive = ?1')->setParameter(1,"{$entity->getId()}")->getQuery()->execute();
        $journal = new AccountJournal();
        $voucher = $em->getRepository(AccountVoucher::class)->find(4);
        $journal->setPaymentReceive($entity);
        $journal->setVoucher($voucher);
        $journal->setModule("account-journal");
        $journal->setEntryMode("contra");
        $journal->setWaitingProcess("Approved");
        $journal->setProcess("Approved");
        $journal->setFinancialYear($entity->getFinancialYear());
        $date = new \DateTime("now");
        $journal->setGeneratedDate($entity->getGeneratedDate());
        $journal->setCreatedBy($entity->getCreatedBy());
        $journal->setAmount($entity->getAmount());
        $journal->setDebit($entity->getAmount());
        $journal->setCredit($entity->getAmount());
        $em->persist($journal);
        $em->flush();
        $this->insertDebitTransaction($entity,$journal);
        $this->insertCreditTransaction($entity,$journal);
    }

    public function insertDebitTransaction(AccountPaymentReceive $entity, AccountJournal $journal)
    {
        $transaction = new AccountJournalItem();
        $transaction->setAccountJournal($journal);
        if ($entity->getDebitAccountHead()) {
            $transaction->setAccountHead($entity->getDebitAccountHead());
        }
        if ($entity->getDebitAccountSubHead()) {
            $transaction->setAccountSubHead($entity->getDebitAccountSubHead());
        }
        $transaction->setAmount($entity->getAmount());
        $transaction->setMode('Dedit');
        $this->_em->persist($transaction);
        $this->_em->flush();
        $this->updateAccountHeadBalance($transaction->getAccountHead(), 'head');
        if ($transaction->getAccountSubHead()) {
            $this->updateAccountHeadBalance($transaction->getAccountSubHead(), 'subHead');
        }
    }

    public function insertCreditTransaction(AccountPaymentReceive $entity, AccountJournal $journal)
    {

        $transaction = new AccountJournalItem();
        $transaction->setAccountJournal($journal);
        if ($entity->getCreditAccountHead()) {
            $transaction->setAccountHead($entity->getCreditAccountHead());
        }
        if ($entity->getCreditAccountSubHead()) {
            $transaction->setAccountSubHead($entity->getCreditAccountSubHead());
        }
        $transaction->setAmount($entity->getAmount());
        $transaction->setMode('Credit');
        $this->_em->persist($transaction);
        $this->_em->flush();
        $this->updateAccountHeadBalance($transaction->getAccountHead(), 'head');
        if ($transaction->getAccountSubHead()) {
            $this->updateAccountHeadBalance($transaction->getAccountSubHead(), 'subHead');
        }

    }


    public function updateAccountHeadBalance(AccountHead $account,$process = 'head'){

        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb = $qb->from('TerminalbdAccountingBundle:AccountJournalItem','e');
        $qb->select('COALESCE(SUM(e.debit),0) as debit','COALESCE(SUM(e.credit),0) as credit');
        if($process == 'head'){
            $qb->where("e.accountHead = :account")->setParameter('account', $account->getId());
        }else{
            $qb->where("e.accountSubHead = :account")->setParameter('account', $account->getId());
        }
        $result = $qb->getQuery()->getSingleResult();
        if($account->getToIncrease() == "Debit"){
            $amount = ($result['debit'] - $result['credit']);
        }else{
            $amount = ($result['credit'] - $result['debit']);
        }
        $account->setDebit($result['debit']);
        $account->setCredit($result['credit']);
        $account->setAmount($amount);
        $em->persist($account);
        $em->flush();

    }

}
