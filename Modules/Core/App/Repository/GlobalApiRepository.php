<?php
/**
 * Created by PhpStorm.
 * User: shafiq
 * Date: 10/9/15
 * Time: 8:05 AM
 */

namespace Core\UserBundle\Repository;



use Appstore\Bundle\DomainUserBundle\Entity\Customer;
use Core\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Setting\Bundle\ToolBundle\Entity\GlobalOption;

class GlobalApiRepository extends EntityRepository {

    public function getCustomers(GlobalOption $option){

        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb->from(User::class,'e');
        $qb->join('e.profile','p');
        $qb->join('e.globalOption','g');
        $qb->select('e.username as username','e.email as email', 'e.id as id', 'e.appPassword as appPassword', 'e.appRoles as appRoles');
        $qb->addSelect('p.name as fullName');
        $qb->where("e.globalOption =".$option->getId());
        $qb->andWhere("g.status =1");
        $qb->andWhere('e.domainOwner = 2');
        $qb->andWhere('e.enabled = 1');
        $qb->andWhere('e.isDelete != 1');
        $qb->orderBy("p.name","ASC");
        $result = $qb->getQuery()->getArrayResult();
        $data =array();
        if($result){
            foreach($result as $key => $row){
                $roles = unserialize(serialize($row['appRoles']));
                $rolesSeparated = implode(",", $roles);
                $data[$key]['user_id'] = (int) $row['id'];
                $data[$key]['username'] = $row['username'];
                $data[$key]['fullName'] = $row['fullName'];
                $data[$key]['email'] = $row['email'];
                $data[$key]['password'] = $row['appPassword'];
                $data[$key]['roles'] = $rolesSeparated;

            }
        }

        return $data;

    }

    public function getApiCustomer(GlobalOption $option)
    {
        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb->from(Customer::class,'customer');
        $qb->select('customer.id as customerId','customer.name as name','customer.mobile as mobile');
        $qb->where("customer.globalOption = :globalOption");
        $qb->andWhere("customer.mobile IS NOT NULL");
        $qb->setParameter('globalOption', $option->getId());
        $qb->andWhere("customer.status=1");
        $qb->orderBy('customer.name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        $data = array();
        foreach($result as $key => $row) {
            $data[$key]['global_id']            = (int) $option->getId();
            $data[$key]['customer_id']          = (int) $row['customerId'];
            $data[$key]['name']                 = $row['name'];
            $data[$key]['mobile']               = $row['mobile'];
        }
        return $data;

    }

    public function getApiSplsshStock(GlobalOption $option)
    {
        $config = $option->getMedicineConfig();
        $em = $this->_em;
        $qb = $em->createQueryBuilder();
        $qb->from('MedicineBundle:MedicineStock','e');
        $qb->leftJoin('e.medicineBrand','brand');
        $qb->leftJoin('e.unit','u');
        $qb->select('e.id as stockId','e.barcode as barcode','e.name as name','e.remainingQuantity as remainingQuantity','e.salesPrice as salesPrice','e.purchasePrice as purchasePrice','e.printHide as printHidden','e.path as path');
        $qb->addSelect('e.name as brandName','brand.strength as strength');
        $qb->addSelect('u.id as unitId','u.name as unitName');
        $qb->where('e.medicineConfig = :config')->setParameter('config', $config->getId()) ;
        if($config->isActiveQuantity() == 1){
            $qb->andWhere('e.purchaseQuantity > :searchTerm OR e.openingQuantity > :searchTerm')->setParameter('searchTerm', 0);
        }
        if($config->isRemainingQuantity() == 1){
            $qb->andWhere('e.remainingQuantity > :searchTerm')->setParameter('searchTerm', 0);
        }
        $qb->andWhere('e.status = 1');
        $qb->orderBy('e.sku','ASC');
        $result = $qb->getQuery()->getArrayResult();
        $data = array();
        foreach($result as $key => $row) {

            $data[$key]['global_id']            = (int) $option->getId();
            $data[$key]['item_id']              = (int) $row['stockId'];
            $printName = trim($row['name']);
            $data[$key]['category_id']      = 0;
            $data[$key]['categoryName']     = '';
            $data[$key]['brandName']            = $row['brandName'];
            $data[$key]['barcode']              = $row['barcode'];
            $data[$key]['unit']                 = ($row['unitName']) ? $row['unitName'] : "";
            $data[$key]['name']                 = $row['name'];
            $data[$key]['printName']            = $printName;
            $data[$key]['quantity']             = $row['remainingQuantity'];
            $data[$key]['salesPrice']           = $row['salesPrice'];
            $data[$key]['purchasePrice']        = $row['purchasePrice'];
            $data[$key]['printHidden']          = ($row['printHidden']) ? $row['printHidden'] : ""; $row['printHidden'];
            if($row['path']){
                $path = $this->resizeFilter("uploads/domain/{$option->getId()}/product/{$row['path']}");
                $data[$key]['imagePath']            =  $path;
            }else{
                $data[$key]['imagePath']            = "";
            }

        }
        return $data;
    }

    /**
     * @param $qb
     * @param $data
     */

    protected function handleSearchBetween($qb,$data)
    {

        $invoice = isset($data['invoice'])? $data['invoice'] :'';
        $transactionMethod = isset($data['transactionMethod'])? $data['transactionMethod'] :'';
        $salesBy = isset($data['salesBy'])? $data['salesBy'] :'';
        $paymentStatus = isset($data['paymentStatus'])? $data['paymentStatus'] :'';
        $bank = isset($data['bank'])? $data['bank'] :'';
        $mobileBank = isset($data['mobileBank'])? $data['mobileBank'] :'';
        $device = isset($data['device'])? $data['device'] :'';
        $customer = isset($data['customer'])? $data['customer'] :'';
        $customerName = isset($data['name'])? $data['name'] :'';
        $customerMobile = isset($data['mobile'])? $data['mobile'] :'';
        $createdStart = isset($data['startDate'])? $data['startDate'] :'';
        $createdEnd = isset($data['endDate'])? $data['endDate'] :'';
        $amount = isset($data['amount'])? $data['amount'] :'';
        $process = isset($data['process'])? $data['process'] :'';
        $due = isset($data['due'])? $data['due'] :'';
        if (!empty($invoice)) {
            $qb->andWhere($qb->expr()->like("s.invoice", "'%$invoice%'"  ));
        }
        if (!empty($customerName)) {
            $qb->join('s.customer','c');
            $qb->andWhere($qb->expr()->like("c.name", "'$customerName%'"  ));
        }
        if (!empty($due)) {
            $qb->andWhere("s.due >= :due")->setParameter('due', $due);
        }
        if (!empty($process)) {
            $qb->andWhere($qb->expr()->like("s.process", "'$process%'"  ));
        }
        if (!empty($customerMobile)) {
            $qb->join('s.customer','c');
            $qb->andWhere($qb->expr()->like("c.mobile", "'%$customerMobile%'"  ));
        }

        if (!empty($customer)) {
            $qb->join('s.customer','c');
            $qb->andWhere($qb->expr()->like("c.mobile", "'%$customer%'"  ));
        }

        if (!empty($amount)) {
            $qb->andWhere($qb->expr()->like("s.netTotal", "'%$amount%'"  ));
        }

        if (!empty($createdStart)) {
            $compareTo = new \DateTime($createdStart);
            $created =  $compareTo->format('Y-m-d 00:00:00');
            $qb->andWhere("s.created >= :createdStart");
            $qb->setParameter('createdStart', $created);
        }else{
            $datetime = new \DateTime("now");
            $created = $datetime->format('Y-m-d 00:00:00');
            $qb->andWhere("s.created >= :createdStart")->setParameter('createdStart', $created);
        }

        if (!empty($createdEnd)) {
            $compareTo = new \DateTime($createdEnd);
            $createdEnd =  $compareTo->format('Y-m-d 23:59:59');
            $qb->andWhere("s.created <= :createdEnd");
            $qb->setParameter('createdEnd', $createdEnd);
        }else{
            $datetime = new \DateTime("now");
            $createdEnd = $datetime->format('Y-m-d 23:59:59');
            $qb->andWhere("s.created <= :createdEnd")->setParameter('createdEnd', $createdEnd);
        }

        if(!empty($salesBy)){
            $qb->join("s.salesBy",'un');
            $qb->andWhere("un.username = :username");
            $qb->setParameter('username', $salesBy);
        }
        if(!empty($paymentStatus)){
            $qb->andWhere("s.paymentStatus = :status");
            $qb->setParameter('status', $paymentStatus);
        }
        if(!empty($transactionMethod)){
            $qb->andWhere("s.transactionMethod = :method");
            $qb->setParameter('method', $transactionMethod);
        }
        if(!empty($bank)){
            $qb->join("s.accountBank","bank");
            $qb->andWhere("bank.id = :bankId");
            $qb->setParameter('bankId', $bank);
        }
        if(!empty($mobileBank)){
            $qb->join("s.accountMobileBank","mobile");
            $qb->andWhere("mobile.id = :mobileId");
            $qb->setParameter('mobileId', $mobileBank);
        }
        if(!empty($device)){
            $qb->andWhere("s.androidProcess = :device");
            $qb->setParameter('device', $device);
        }


    }



}