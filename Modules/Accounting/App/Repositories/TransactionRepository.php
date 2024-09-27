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
use Modules\Inventory\App\Entities\Purchase;
use Modules\Inventory\App\Entities\PurchaseItem;


/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 *
 * See https://symfony.com/doc/current/doctrine/repository.html
 *
 * @author Md Shafiqul islam <shafiqabs@gmail.com>
 */
class TransactionRepository extends EntityRepository
{


    public function insertPurchaseInventoryItem(Purchase $purchase)
    {
        $purchase;
    }


    public function purchaseTransaction(Purchase $purchase)
    {
        $em = $this->_em;
        $categories = $em->getRepository(PurchaseItem::class)->returnCategoryWiseAmount($purchase);
        foreach ($categories as $category){
            $this->insertInventoryAsset($purchase->getId(),$category);
        }
       // $this->insertPurchaseCash($purchase,$accountPurchase);
        $this->insertPurchaseAccountPayable($purchase);
    }


    private function insertInventoryAsset($purchase,$category)
    {
        $em = $this->_em;
        $account = $em->getRepository(AccountHead::class)->findOneBy(['accountProduct' => $category]);
        $amount = $category['amount'];
        $exist = $this->findOneBy(['processHead' => 'Inventory-Purchase','accountRefNo' => $purchase,'accountHead'=>$account]);
        if(empty($exist)){
            $transaction = new Transaction();
            $transaction->setProcess('Inventory Assets');
            $transaction->setProcessHead('Inventory-Purchase');
            $transaction->setAccountRefNo($purchase);
            /* Inventory Assets - Purchase Goods Received account */
            // $transaction->setSubAccountHead($account);
            $transaction->setAccountHead($account);
            $transaction->setAmount($amount);
            $transaction->setDebit($amount);
            $em->persist($transaction);
            $em->flush();
            $this->updateAccountHeadBalance($account,'head');
        }

    }

    private function insertPurchaseAccountPayable(Purchase $purchase)
    {
        $em = $this->_em;
        $amount = $purchase->getTotal();
        $account = $em->getRepository(AccountHead::class)->findOneBy(['accountVendor' => $purchase->getVendor()->getId()]);
        $exist = $this->findOneBy(['processHead' => 'Inventory-Purchase','accountRefNo' => $purchase->getId(),'subAccountHead'=>$account]);
        if(empty($exist)){
            $transaction = new Transaction();
            $transaction->setProcess('Current Liabilities');
            $transaction->setProcessHead('Inventory-Purchase');
            $transaction->setAccountRefNo($purchase->getId());
            /* Current Liabilities-Purchase Account payable */
            $transaction->setSubAccountHead($account);
            $transaction->setAccountHead($account->getParent());
            $transaction->setAmount('-'.$amount);
            $transaction->setCredit($amount);
            $em->persist($transaction);
            $em->flush();
            $this->updateAccountHeadBalance($account->getParent(),'head');
            $this->updateAccountHeadBalance($account,'subHead');
        }
    }

    public function openingStockTransaction(AccountJournal $journal ,PurchaseItem $opening)
    {
        $em = $this->_em;
        $category = $opening->getStockItem()->getProduct()->getCategory();
        $this->insertOpeningInventoryAsset($journal,$opening,$category);
        $this->insertOpeningAccountPayable($journal,$opening);
    }

    private function insertOpeningInventoryAsset(AccountJournal $journal,PurchaseItem $opening,$category)
    {
        $em = $this->_em;
        $accountLedger = $em->getRepository(AccountHead::class)->findOneBy(['category' => $category]);
        $amount = $opening->getSubTotal();
        if(empty($journal->getJournalItems())){
            $transaction = new AccountJournalItem();
            $transaction->setAccountLedger($accountLedger);
            $transaction->setAccountHead($accountLedger->getParent());
            $transaction->setAmount($amount);
            $transaction->setDebit($amount);
            $em->persist($transaction);
            $em->flush();
            $this->updateAccountHeadBalance($accountLedger,'ledger');
            $this->updateAccountHeadBalance($accountLedger->getParent(),'head');
        }

    }

    private function insertOpeningAccountPayable(AccountJournal $journal , Purchase $purchase)
    {
        $em = $this->_em;
        $amount = $purchase->getTotal();
        $account = $em->getRepository(AccountHead::class)->findOneBy(['slug' => 'opening-balance']);
        $exist = $this->findOneBy(['processHead' => 'Opening-Inventory','accountRefNo' => $purchase->getId(),'accountHead'=>$account]);
        if(empty($exist)){
            $transaction = new AccountJournalItem();
            $transaction->setProcess('Current Liabilities');
            /* Current Liabilities-Purchase Account payable */
            $transaction->setAccountHead($account);
            $transaction->setAccountSubHead($account);
            $transaction->setAmount('-'.$amount);
            $transaction->setCredit($amount);
            $em->persist($transaction);
            $em->flush();
            $this->updateAccountHeadBalance($account,'ledger');
            $this->updateAccountHeadBalance($account,'head');
        }
    }

    private function insertPurchaseCash(Purchase $purchase,AccountPurchase $accountPurchase)
    {

        $amount = $purchase->getPaymentAmount();
        if($amount > 0) {

            $transaction = new Transaction();
            $transaction->setGlobalOption($purchase->getInventoryConfig()->getGlobalOption());
            $transaction->setProcessHead('Purchase');
            $transaction->setProcess('Cash');
            $transaction->setAccountRefNo($accountPurchase->getAccountRefNo());
            $transaction->setUpdated($accountPurchase->getUpdated());

            /* Cash - Cash various */
            if($purchase->getTransactionMethod()->getId() == 2 ){
                /* Current Asset Bank Cash Debit */
                $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(3));
                $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertBankAccount($purchase->getAccountBank());
                $transaction->setSubAccountHead($subAccount);
                $transaction->setProcess('Current Assets');
            }elseif($purchase->getTransactionMethod()->getId() == 3 ){
                /* Current Asset Mobile Account Debit */
                $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(10));
                $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertMobileBankAccount($purchase->getAccountMobileBank());
                $transaction->setSubAccountHead($subAccount);
                $transaction->setProcess('Current Assets');
            }elseif($purchase->getTransactionMethod()->getId() == 1 ){
                /* Cash - Cash Debit */
                $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(30));
                $transaction->setProcess('Cash');
            }
            $transaction->setAmount('-' . $amount);
            $transaction->setCredit($amount);
            $this->_em->persist($transaction);
            $this->_em->flush();

        }
    }

    public function insertVendorOpeningTransaction(AccountPurchase $entity)
    {

        $transaction = new Transaction();
        $transaction->setGlobalOption($entity->getGlobalOption());
        $transaction->setAccountRefNo($entity->getAccountRefNo());
        $transaction->setProcessHead('Opening Liabilities');
        $transaction->setUpdated($entity->getUpdated());
        $transaction->setProcess('Current Liabilities');
        /* Current Liabilities - Account Payable Payment */
        $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(13));
        /* ==== Sub Account set ====*/
        if($entity->getGlobalOption()->getMainApp()->getSlug() == 'miss'){
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertMedicineVendorAccount($entity->getMedicineVendor());
        }elseif ($entity->getGlobalOption()->getMainApp()->getSlug() == 'inventory'){
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertInventoryVendorAccount($entity->getVendor());
        }else{
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertVendorAccount($entity->getAccountVendor());
        }
        $transaction->setSubAccountHead($subAccount);
        $transaction->setAmount('-'.$entity->getPurchaseAmount());
        $transaction->setCredit($entity->getPurchaseAmount());
        $this->_em->persist($transaction);
        $this->_em->flush();

        $transaction = new Transaction();
        $transaction->setGlobalOption($entity->getGlobalOption());
        $transaction->setAccountRefNo($entity->getAccountRefNo());
        $transaction->setProcessHead('Opening Liabilities');
        $transaction->setUpdated($entity->getUpdated());
        $transaction->setProcess('Inventory Assets');
        /* Current Liabilities - Account Payable Payment */
        $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(6));
        $transaction->setSubAccountHead($subAccount);
        $transaction->setAmount($entity->getPurchaseAmount());
        $transaction->setDebit($entity->getPurchaseAmount());
        $this->_em->persist($transaction);
        $this->_em->flush();

    }

    public function updateAccountHeadBalance(AccountHead $account,$process = 'head'){

        $em = $this->_em;
        $qb = $this->createQueryBuilder('e');
        $qb->select('COALESCE(SUM(e.debit),0) as debit','COALESCE(SUM(e.credit),0) as credit');
        if($process == 'head'){
            $qb->where("e.accountHead = :account")->setParameter('account', $account->getId());
        }else{
            $qb->where("e.subAccountHead = :account")->setParameter('account', $account->getId());
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


    public function insertPurchaseVendorTransaction(AccountPurchase $entity)
    {
        $this->insertPurchaseCashCreditTransaction($entity);
        $this->insertPurchaseLiabilityDebitTransaction($entity);
    }

    public function insertPurchaseCashCreditTransaction(AccountPurchase $entity)
    {

        $transaction = new Transaction();
        $transaction->setGlobalOption($entity->getGlobalOption());
        $transaction->setAccountRefNo($entity->getAccountRefNo());
        $transaction->setProcessHead('Purchase');
        $transaction->setUpdated($entity->getUpdated());

        /* Cash - Cash various */
        if($entity->getTransactionMethod()->getId() == 2 ){
            /* Current Asset Bank Cash Debit */
            $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(3));
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertBankAccount($entity->getAccountBank());
            $transaction->setSubAccountHead($subAccount);
            $transaction->setProcess('Current Assets');
        }elseif($entity->getTransactionMethod()->getId() == 3 ){
            /* Current Asset Mobile Account Debit */
            $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(10));
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertMobileBankAccount($entity->getAccountMobileBank());
            $transaction->setSubAccountHead($subAccount);
            $transaction->setProcess('Current Assets');
        }elseif($entity->getTransactionMethod()->getId() == 1 ){
            /* Cash - Cash Debit */
            $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(30));
            $transaction->setProcess('Cash');
        }
        if($entity->getGlobalOption()->getMainApp()->getSlug() == 'miss'){
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertMedicineVendorAccount($entity->getMedicineVendor());
        }elseif ($entity->getGlobalOption()->getMainApp()->getSlug() == 'inventory'){
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertInventoryVendorAccount($entity->getVendor());
        }else{
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertVendorAccount($entity->getAccountVendor());
        }
        //   $transaction->setSubAccountHead($subAccount);
        $transaction->setAmount('-'.$entity->getPayment());
        $transaction->setCredit($entity->getPayment());
        $this->_em->persist($transaction);
        $this->_em->flush();
    }

    public function insertPurchaseLiabilityDebitTransaction(AccountPurchase $entity)
    {

        $transaction = new Transaction();
        $transaction->setGlobalOption($entity->getGlobalOption());
        $transaction->setAccountRefNo($entity->getAccountRefNo());
        $transaction->setProcessHead('Purchase');
        $transaction->setUpdated($entity->getUpdated());
        $transaction->setProcess('Current Liabilities');
        /* Current Liabilities - Account Payable Payment */
        $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(13));
        /* ==== Sub Account set ==== */
        if($entity->getGlobalOption()->getMainApp()->getSlug() == 'miss'){
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertMedicineVendorAccount($entity->getMedicineVendor());
        }elseif ($entity->getGlobalOption()->getMainApp()->getSlug() == 'inventory'){
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertInventoryVendorAccount($entity->getVendor());
        }else{
            $subAccount = $this->_em->getRepository('AccountingBundle:AccountHead')->insertVendorAccount($entity->getAccountVendor());
        }
        $transaction->setSubAccountHead($subAccount);
        $transaction->setAmount($entity->getPayment());
        $transaction->setDebit($entity->getPayment());
        $this->_em->persist($transaction);
        $this->_em->flush();

    }

    public function purchaseReturnTransaction($entity,$accountPurchaseReturn)
    {

        $this->insertPurchaseReturn($entity,$accountPurchaseReturn);
        $this->insertPurchaseReturnAccountReceivable($entity,$accountPurchaseReturn);

    }

    private function insertPurchaseReturn(PurchaseReturn $entity,AccountPurchaseReturn $accountPurchaseReturn)
    {

        $transaction = new Transaction();
        $transaction->setGlobalOption($accountPurchaseReturn->getGlobalOption());
        $transaction->setAccountRefNo($accountPurchaseReturn->getAccountRefNo());
        $transaction->setProcessHead('PurchaseReturn');
        $transaction->setUpdated($entity->getUpdated());
        $transaction->setProcess('Goods');
        /* Inventory Assets-Purchase Return account */
        $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(34));
        $transaction->setAmount('-'.$entity->getTotal());
        $transaction->setCredit($entity->getTotal());
        $this->_em->persist($transaction);
        $this->_em->flush();

    }

    private function insertPurchaseReturnAccountReceivable(PurchaseReturn $entity,AccountPurchaseReturn $accountPurchaseReturn)
    {
        $transaction = new Transaction();
        $transaction->setGlobalOption($accountPurchaseReturn->getGlobalOption());
        $transaction->setAccountRefNo($accountPurchaseReturn->getAccountRefNo());
        $transaction->setProcessHead('PurchaseReturn');
        $transaction->setUpdated($entity->getUpdated());
        $transaction->setProcess('Cash');
        /* Assets Account - Account Cash */
        $transaction->setAccountHead($this->_em->getRepository('AccountingBundle:AccountHead')->find(30));
        $transaction->setAmount($entity->getTotal());
        $transaction->setDebit($entity->getTotal());
        $this->_em->persist($transaction);
        $this->_em->flush();

    }

    public function salesTransaction($entity,$data)
    {
        $em = $this->_em;
        $journal = $em->getRepository(AccountJournal::class)->insertJournalOrderDelivery($entity,$data);
        $this->insertSalesItem($journal,$entity);
        $this->insertSalesCash($journal,$entity,$data);
        $this->insertSalesAccountReceivable($journal,$entity);
    }


    private function insertSalesItem(AccountJournal $journal,OrderDelivery $entity)
    {
        $em = $this->_em;
        $categories = $em->getRepository(OrderDeliveryItem::class)->returnCategoryBasePrice($entity->getId());
        foreach ($categories as $category){
            $account = $em->getRepository(AccountHead::class)->findOneBy(['accountProduct' => $category['categoryId']]);
            $amount = $category['amount'];
            $exist = $this->findOneBy(['processHead' => 'Delivery-Order','accountRefNo' => $entity->getId(),'accountHead' => $account]);
            if(empty($exist)){
                $transaction = new Transaction();
                $transaction->setProcessHead('Delivery-Order');
                $transaction->setProcess('Inventory-Sales');
                $transaction->setAccountJournal($journal);
                $transaction->setAccountRefNo($entity->getId());
                $transaction->setAccountHead($account->getParent());
                $transaction->setSubAccountHead($account);
                $transaction->setAmount("-".$amount);
                $transaction->setCredit($amount);
                $em->persist($transaction);
                $em->flush();
                $this->updateAccountHeadBalance($account,'head');
            }
        }
    }

    private function insertSalesCash(AccountJournal $journal,OrderDelivery $entity,$data)
    {
        $em = $this->_em;
        $amount = $entity->getAmount();
        $exist = $this->findOneBy(['processHead' => 'Delivery-Order','accountRefNo' => $entity->getId(),'subAccountHead' => $entity->getBankAccountSubHead()]);
        if(empty($exist) and $amount > 0 and $entity->getBankAccountSubHead()) {
            $journalItem = $em->getRepository(AccountJournalItem::class)->insertCustomerPaymentReceive($entity,$data);
            $transaction = new Transaction();
            $transaction->setAccountJournal($journal);
            $transaction->setAccountRefNo($entity->getId());
            $transaction->setProcessHead('Delivery-Order');
            $transaction->setProcess('Inventory-Sales');
            $transaction->setUpdated($entity->getUpdated());
            $transaction->setAccountHead($entity->getDebitAccountHead());
            $transaction->setAccountJournalItem($journalItem);
            if ($entity->getBankAccountSubHead()) {
                $transaction->setSubAccountHead($entity->getBankAccountSubHead());
            }
            $transaction->setAmount($amount);
            $transaction->setDebit($amount);
            $this->_em->persist($transaction);
            $this->_em->flush();
            $this->updateAccountHeadBalance($transaction->getAccountHead(),'head');
            $this->updateAccountHeadBalance($transaction->getSubAccountHead(),'subHead');
        }
    }

    private function insertSalesAccountReceivable(AccountJournal $journal,OrderDelivery $entity)
    {
        $em = $this->_em;
        $amount = ($entity->getSubTotal() - $entity->getAmount());
        $accountSubHead = $entity->getDebitAccountSubHead();
        $exist = $this->findOneBy(['processHead' => 'Delivery-Order','accountRefNo' => $entity->getId(),'subAccountHead' => $accountSubHead]);
        if(empty($exist) and $amount > 0 and $accountSubHead){
            $transaction = new Transaction();
            $transaction->setAccountJournal($journal);
            $transaction->setAccountRefNo($entity->getId());
            $transaction->setProcessHead('Delivery-Order');
            $transaction->setProcess('Inventory-Sales');
            $transaction->setAccountHead($accountSubHead->getParent());
            $transaction->setSubAccountHead($accountSubHead);
            $transaction->setAmount($amount);
            $transaction->setDebit($amount);
            $em->persist($transaction);
            $em->flush();
            $this->updateAccountHeadBalance($transaction->getAccountHead(),'head');
            $this->updateAccountHeadBalance($transaction->getSubAccountHead(),'subHead');
        }
    }


    public function resetSalesTransaction($option , $entity, $accountSales)
    {
        $this->salesTransaction($entity,$accountSales);
    }

    public function salesReturnTransaction(OrderDeliveryReturn $entity)
    {
        $em = $this->_em;
        $this->insertSalesReturnDebit($entity);
        $this->insertSalesReturnCredit($entity);
    }

    private function insertSalesReturnDebit(OrderDeliveryReturn $entity)
    {
        $em = $this->_em;
        $categories = $em->getRepository(OrderDeliveryReturnItem::class)->returnCategoryBasePrice($entity->getId());
        foreach ($categories as $category){
            $account = $em->getRepository(AccountHead::class)->findOneBy(['accountProduct' => $category['categoryId']]);
            $amount = $category['amount'];
            $exist = $this->findOneBy(['processHead' => 'Sales-Return','accountRefNo' => $entity->getId(),'accountHead' => $account]);
            if(empty($exist)){
                $transaction = new Transaction();
                $transaction->setProcessHead('Sales-Return');
                $transaction->setProcess('Inventory-Sales');
                $transaction->setAccountRefNo($entity->getId());
                $transaction->setAccountHead($account);
                $transaction->setAmount($amount);
                $transaction->setDebit($amount);
                $em->persist($transaction);
                $em->flush();
                $this->updateAccountHeadBalance($transaction->getAccountHead(),'head');
            }
        }

    }

    private function insertSalesReturnCredit(OrderDeliveryReturn $entity)
    {
        $em = $this->_em;
        $amount = $entity->getSubTotal();
        $accountSubHead = $entity->getOrderDelivery()->getDebitAccountSubHead();
        $exist = $this->findOneBy(['processHead' => 'Sales-Return','accountRefNo' => $entity->getId(),'subAccountHead' => $accountSubHead]);
        if(empty($exist) and $amount > 0 and $accountSubHead){
            $transaction = new Transaction();
            $transaction->setAccountRefNo($entity->getId());
            $transaction->setProcessHead('Sales-Return');
            $transaction->setProcess('Inventory-Sales');
            $transaction->setAccountHead($accountSubHead->getParent());
            $transaction->setSubAccountHead($accountSubHead);
            $transaction->setAmount("-".$amount);
            $transaction->setCredit($amount);
            $em->persist($transaction);
            $em->flush();
            $this->updateAccountHeadBalance($transaction->getAccountHead(),'head');
            $this->updateAccountHeadBalance($transaction->getSubAccountHead(),'subHead');
        }

    }

    public function insertExpenditureTransaction($entity)
    {
        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb->delete(Transaction::class, 'e')->where('e.processHead = ?1')->setParameter(1,'Expenditure')->andWhere('e.accountRefNo = ?2')->setParameter(2,"{$entity->getId()}")->getQuery()->execute();
        $this->insertExpenditureDebitTransaction($entity);
        $this->insertExpenditureCreditTransaction($entity);
    }

    public function insertExpenditureDebitTransaction(AccountExpense $entity)
    {
        $exist = $this->findOneBy(['processHead' => 'Expenditure','accountRefNo' => $entity->getId(),'accountHead' => $entity->getDebitAccountHead()]);
        if(empty($exist) and $entity->getAmount() > 0 and $entity->getDebitAccountHead()) {
            $transaction = new Transaction();
            $transaction->setAccountRefNo($entity->getId());
            $transaction->setProcessHead('Expenditure');
            if ($entity->getDebitAccountHead()) {
                $transaction->setAccountHead($entity->getDebitAccountHead());
            }
            if ($entity->getDebitAccountSubHead()) {
                $transaction->setSubAccountHead($entity->getDebitAccountSubHead());
            }
            $transaction->setAmount($entity->getAmount());
            $transaction->setDebit($entity->getAmount());
            $this->_em->persist($transaction);
            $this->_em->flush();
            $this->updateAccountHeadBalance($transaction->getAccountHead(), 'head');
            if ($transaction->getSubAccountHead()) {
                $this->updateAccountHeadBalance($transaction->getSubAccountHead(), 'subHead');
            }
        }

    }

    public function insertExpenditureCreditTransaction(AccountExpense $entity)
    {

        $exist = $this->findOneBy(['processHead' => 'Expenditure','accountRefNo' => $entity->getId(),'accountHead' => $entity->getCreditAccountHead()]);
        if(empty($exist) and $entity->getAmount() > 0 and $entity->getCreditAccountHead()) {
            $transaction = new Transaction();
            $transaction->setAccountRefNo($entity->getId());
            $transaction->setProcessHead('Expenditure');
            if ($entity->getCreditAccountHead()) {
                $transaction->setAccountHead($entity->getCreditAccountHead());
            }
            if ($entity->getCreditAccountSubHead()) {
                $transaction->setSubAccountHead($entity->getCreditAccountSubHead());
            }
            $transaction->setAmount('-' . $entity->getAmount());
            $transaction->setCredit($entity->getAmount());
            $this->_em->persist($transaction);
            $this->_em->flush();
            $this->updateAccountHeadBalance($transaction->getAccountHead(), 'head');
            if ($transaction->getSubAccountHead()) {
                $this->updateAccountHeadBalance($transaction->getSubAccountHead(), 'subHead');
            }
        }

    }


    public function insertPaymentReceiveContraTransaction($entity)
    {
        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb->delete(Transaction::class, 'e')->where('e.processHead = ?1')->setParameter(1,'Payment-Receive')->andWhere('e.accountRefNo = ?2')->setParameter(2,"{$entity->getId()}")->getQuery()->execute();
        $this->insertDebitTransaction($entity);
        $this->insertCreditTransaction($entity);
    }

    public function insertDebitTransaction(AccountPaymentReceive $entity)
    {
        $exist = $this->findOneBy(['processHead' => 'Payment-Receive','accountRefNo' => $entity->getId(),'accountHead' => $entity->getDebitAccountHead()]);
        if(empty($exist) and $entity->getAmount() > 0 and $entity->getDebitAccountHead()) {
            $transaction = new Transaction();
            $transaction->setAccountRefNo($entity->getId());
            $transaction->setProcessHead('Payment-Receive');
            if ($entity->getDebitAccountHead()) {
                $transaction->setAccountHead($entity->getDebitAccountHead());
            }
            if ($entity->getDebitAccountSubHead()) {
                $transaction->setSubAccountHead($entity->getDebitAccountSubHead());
            }
            $transaction->setAmount($entity->getAmount());
            $transaction->setDebit($entity->getAmount());
            $this->_em->persist($transaction);
            $this->_em->flush();
            $this->updateAccountHeadBalance($transaction->getAccountHead(), 'head');
            if ($transaction->getSubAccountHead()) {
                $this->updateAccountHeadBalance($transaction->getSubAccountHead(), 'subHead');
            }
        }

    }

    public function insertCreditTransaction(AccountPaymentReceive $entity)
    {

        $exist = $this->findOneBy(['processHead' => 'Payment-Receive','accountRefNo' => $entity->getId(),'accountHead' => $entity->getCreditAccountHead()]);
        if(empty($exist) and $entity->getAmount() > 0 and $entity->getCreditAccountHead()) {
            $transaction = new Transaction();
            $transaction->setAccountRefNo($entity->getId());
            $transaction->setProcessHead('Payment-Receive');
            if ($entity->getCreditAccountHead()) {
                $transaction->setAccountHead($entity->getCreditAccountHead());
            }
            if ($entity->getCreditAccountSubHead()) {
                $transaction->setSubAccountHead($entity->getCreditAccountSubHead());
            }
            $transaction->setAmount('-' . $entity->getAmount());
            $transaction->setCredit($entity->getAmount());
            $this->_em->persist($transaction);
            $this->_em->flush();
            $this->updateAccountHeadBalance($transaction->getAccountHead(), 'head');
            if ($transaction->getSubAccountHead()) {
                $this->updateAccountHeadBalance($transaction->getSubAccountHead(), 'subHead');
            }
        }

    }

    public function insertDoubleEntryTransaction(AccountJournal $journal)
    {

        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb->delete(Transaction::class, 'e')->where('e.processHead = ?1')->setParameter(1,'Journal')->andWhere('e.accountJournal = ?2')->setParameter(2,"{$journal->getId()}")->getQuery()->execute();


        /* @var $entity AccountJournalItem */

        foreach ($journal->getJournalItems() as $entity):

            $transaction = new Transaction();
            $transaction->setAccountJournal($journal);
            $transaction->setAccountRefNo($journal->getId());
            $transaction->setAccountJournalItem($entity);
            $transaction->setProcessHead('Journal');
            $transaction->setCreated($journal->getCreated());
            $transaction->setUpdated($journal->getCreated());
            $transaction->setNarration($journal->getDescription());
            if($entity->getDebit() > 0){
                $transaction->setAccountHead($entity->getAccountHead());
                if($entity->getAccountSubHead()){
                    $transaction->setSubAccountHead($entity->getAccountSubHead());
                }
                $transaction->setAmount($entity->getDebit());
                $transaction->setDebit($entity->getDebit());
            }else{
                $transaction->setAccountHead($entity->getAccountHead());
                if($entity->getAccountSubHead()){
                    $transaction->setSubAccountHead($entity->getAccountSubHead());
                }
                $transaction->setAmount("-{$entity->getCredit()}");
                $transaction->setCredit($entity->getCredit());
            }
            $em->persist($transaction);
            $em->flush();
            $this->updateAccountHeadBalance($transaction->getAccountHead(), 'head');
            if ($transaction->getSubAccountHead()) {
                $this->updateAccountHeadBalance($transaction->getSubAccountHead(), 'subHead');
            }
        endforeach;

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
        $qb->join('e.accountHead','accountHead');
        $qb->join('accountHead.parent','parent');
        $qb->select('sum(e.amount) as amount, sum(e.debit) as debit , sum(e.credit) as credit, accountHead.name as name , parent.name as parentName,parent.id as parentId, accountHead.id as accountHeadId, accountHead.toIncrease, accountHead.code as code');
        $qb->where("parent.slug IN (:parent)")->setParameter('parent',$heads);
        $qb->andWhere("e.created <= :tillDate")->setParameter('tillDate', $tillDate);
        $qb->groupBy('e.accountHead');
        $qb->orderBy('parentName','ASC')->orderBy('name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        dd($result);
        return $result;
    }


    public function getSubHeadAccountDebit($parent,$data)
    {
        if(empty($data)){
            $datetime = new \DateTime("now");
            $tillDate = $datetime->format('Y-m-d 23:59:59');
        }elseif(!empty($data['tillDate']) and !empty($data['tillDate'])){
            $datetime = new \DateTime($data['tillDate']);
            $tillDate = $datetime->format('Y-m-d 23:59:59');
        }
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.subAccountHead','subAccountHead');
        $qb->join('e.accountHead','accountHead');
        $qb->join('accountHead.parent','parent');
        $qb->select('subAccountHead.id as subHead , subAccountHead.name as headName , COALESCE(sum(e.amount),0) as amount');
        $qb->addSelect('accountHead.name as parentName',"accountHead.id as parentId");
        $qb->where("parent.slug IN(:parents)")->setParameter('parents', $parent);
        $qb->andWhere("accountHead.slug NOT IN(:heads)")->setParameter('heads', array('account-receivable'));
        $qb->andWhere("e.created <= :tillDate")->setParameter('tillDate', $tillDate);
        $qb->groupBy('subAccountHead.id');
        $qb->having('amount > 0');
        $qb->orderBy('accountHead.name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        $array = array();
        foreach ($result as $row):
            $array[$row['subHead']] = $row;
        endforeach;
        return $array;
    }

    public function getSubHeadAccountCredit($parent,$data)
    {
        if(empty($data)){
            $datetime = new \DateTime("now");
            $tillDate = $datetime->format('Y-m-d 23:59:59');
        }elseif(!empty($data['tillDate']) and !empty($data['tillDate'])){
            $datetime = new \DateTime($data['tillDate']);
            $tillDate = $datetime->format('Y-m-d 23:59:59');
        }
        $qb = $this->createQueryBuilder('e');
        $qb->join('e.subAccountHead','subAccountHead');
        $qb->join('e.accountHead','accountHead');
        $qb->join('accountHead.parent','parent');
        $qb->select('subAccountHead.id as subHead , subAccountHead.name as headName , COALESCE(sum(e.amount),0) as amount');
        $qb->addSelect('accountHead.name as parentName',"accountHead.id as parentId");
        $qb->where("parent.slug IN(:parents)")->setParameter('parents', $parent);
       // $qb->andWhere("accountHead.slug NOT IN(:heads)")->setParameter('heads', array('account-payable'));
        $qb->andWhere("e.created <= :tillDate")->setParameter('tillDate', $tillDate);
        $qb->groupBy('subAccountHead.id');
        $qb->having('amount < 0');
        $qb->orderBy('accountHead.name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        $array = array();
        foreach ($result as $row):
            $array[$row['subHead']] = $row;
        endforeach;
        return $array;
    }

    public function parentsAccountHead($parent,$data){

        $qb = $this->_em->createQueryBuilder();
        $qb->select('sum(ex.amount) as amount, accountHead.name as name , accountHead.id, accountHead.toIncrease, accountHead.code');
        $qb->from('AccountingBundle:Transaction','ex');
        $qb->innerJoin('ex.accountHead','accountHead');
        $qb->where("accountHead.parent IN(:parent)")->setParameter('parent', $parent);
        $this->handleSearchBetween($qb,$data);
        $qb->groupBy('ex.accountHead');
        $qb->orderBy('ex.accountHead','ASC');
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function accountLedger($data){


        $startDate = new \DateTime($data['startDate']);
        $startDate = $startDate->format('Y-m-d 23:59:59');
        $endDate = new \DateTime($data['endDate']);
        $endDate = $endDate->format('Y-m-d 23:59:59');
        $head= $data['accountSubHead'];
        $qb = $this->createQueryBuilder('e');
        $qb->leftJoin('e.accountJournalItem','ji');
        $qb->leftJoin('ji.accountJournal','j');
        $qb->where('e.updated >= :today_startdatetime') ->setParameter('today_startdatetime', $startDate);
        $qb->andWhere('e.updated <= :today_enddatetime')->setParameter('today_enddatetime', $endDate);
        $qb->andWhere('e.subAccountHead <= :subAccountHead')->setParameter('subAccountHead',$head);
        $qb->orderBy('e.accountHead','ASC');
        $result = $qb->getQuery()->getResult();


        $startDate = new \DateTime($data['startDate']);
        $startDate = $startDate->format('Y-m-d 23:59:59');
        $endDate = new \DateTime($data['endDate']);
        $endDate = $endDate->format('Y-m-d 23:59:59');
        $head= $data['accountSubHead'];
        $qb = $this->createQueryBuilder('e');
        $qb->leftJoin('e.accountJournalItem','ji');
        $qb->leftJoin('ji.accountJournal','j');
        $qb->where('e.updated >= :today_startdatetime') ->setParameter('today_startdatetime', $startDate);
        $qb->andWhere('e.updated <= :today_enddatetime')->setParameter('today_enddatetime', $endDate);
        $qb->andWhere('e.subAccountHead <= :subAccountHead')->setParameter('subAccountHead',$head);
        $qb->orderBy('e.accountHead','ASC');
        $result = $qb->getQuery()->getResult();
        return $result;


    }

    public function specificAccountHead($globalOption,$accountHead){

        $datetime = new \DateTime("now");
        $today_startdatetime = $datetime->format('Y-m-d 00:00:00');
        $today_enddatetime = $datetime->format('Y-m-d 23:59:59');

        $qb = $this->_em->createQueryBuilder();
        $qb->select('e.amount as amount,e.debit as debit, e.credit as credit , e.updated,e.accountRefNo, e.processHead, e.toIncrease, e.content');
        $qb->from('AccountingBundle:Transaction','e');
        $qb->where('e.globalOption = :globalOption')
            ->andWhere("e.accountHead = :accountHead");
        $qb->setParameter('globalOption', $globalOption->getId())
            ->setParameter('accountHead', $accountHead);
        $qb->orderBy('e.updated','DESC');
        $result = $qb->getQuery()->getResult();

        return $result;

    }

    public function reportTransactionIncomeLoss($globalOption,$accountHeads,$data){

        $qb = $this->createQueryBuilder('ex');
        $qb->join('ex.accountHead','accountHead');
        $qb->select('COALESCE(SUM(ex.amount),0) as amount');
        $qb->where("accountHead.parent IN (:parent)");
        $qb->setParameter('parent', $accountHeads);
        $qb->andWhere('ex.globalOption = :globalOption');
        $qb->setParameter('globalOption', $globalOption);
        $this->handleSearchBetween($qb,$data);
        $res =  $qb->getQuery();
        $result = $res->getOneOrNullResult();
        return $result;

    }

    public function reportTransactionProfitIncomeLoss(AccountProfit $profit,$accountHeads){

        $qb = $this->createQueryBuilder('ex');
        $qb->join('ex.accountHead','accountHead');
        $qb->select('COALESCE(SUM(ex.amount),0) as amount');
        $qb->where("accountHead.parent IN (:parent)");
        $qb->setParameter('parent', $accountHeads);
        $qb->andWhere('ex.accountProfit = :profit');
        $qb->setParameter('profit', $profit->getId());
        $res =  $qb->getQuery();
        $result = $res->getOneOrNullResult();
        return $result;

    }


    public function reportTransactionIncome($globalOption,$accountHeads,$data){

        $qb = $this->createQueryBuilder('ex');
        $qb->join('ex.accountHead','accountHead');
        $qb->select('COALESCE(sum(ex.amount),0) as amount, COALESCE(sum(ex.debit),0) as debit , COALESCE(sum(ex.credit),0) as credit, accountHead.name as name, accountHead.toIncrease as toIncrease');
        $qb->where("accountHead.parent IN (:parent)");
        $qb->setParameter('parent', $accountHeads);
        $qb->andWhere('ex.globalOption = :globalOption');
        $qb->setParameter('globalOption', $globalOption);
        $this->handleSearchBetween($qb,$data);
        $qb->groupBy('ex.accountHead');
        $res =  $qb->getQuery();
        $result = $res->getArrayResult();
        return $result;

    }

    public function reportDebitTransactionIncome($globalOption,$accountHeads,$data){

        $qb = $this->createQueryBuilder('ex');
        $qb->join('ex.accountHead','accountHead');
        $qb->select('sum(ex.amount) as amount');
        $qb->where("accountHead.id IN (:ids)");
        $qb->setParameter('ids', $accountHeads);
        $qb->andWhere('ex.globalOption = :globalOption');
        $qb->setParameter('globalOption', $globalOption);
        $this->handleSearchBetween($qb,$data);
        $qb->groupBy('ex.accountHead');
        $res =  $qb->getQuery();
        $result = $res->getOneOrNullResult();
        return $result['amount'];

    }

    public function bankReconcilation($data,$mode)
    {
        if(empty($data)){
            $datetime = new \DateTime("now");
            $tillDate = $datetime->format('Y-m-d 23:59:59');
        }elseif(!empty($data['tillDate']) and !empty($data['tillDate'])){
            $datetime = new \DateTime($data['tillDate']);
            $tillDate = $datetime->format('Y-m-d 23:59:59');
        }
        $accountHeads = array(3);
        $qb = $this->createQueryBuilder('e');
        $qb->leftJoin('e.subAccountHead','sah');
        $qb->leftJoin('e.accountHead','ah');
        $qb->leftJoin('e.accountJournal','ah');
        $qb->select('ah.name as accountHead , sah.name as subAccountHead , COALESCE(e.amount,0) as amount, COALESCE(e.debit,0) as debit, COALESCE(e.credit,0) as credit');
        $qb->addSelect('e.id as id','e.created as created','e.processHead as processHead','e.status as status');
        $qb->where("e.accountHead IN(:accountHeads)")->setParameter('accountHeads', $accountHeads);
      //  $qb->andWhere("accountHead.slug NOT IN(:heads)")->setParameter('heads', array('account-receivable'));
      //
        if($mode == "archive"){
            $qb->andWhere("e.status=true");
        }else{
            $qb->andWhere("e.status=false");
        }
        $qb->orderBy('ah.name','ASC')->addOrderBy('sah.name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        return $result;
    }


}
