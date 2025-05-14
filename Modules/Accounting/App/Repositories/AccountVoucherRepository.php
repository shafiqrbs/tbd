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
use Modules\Accounting\App\Entities\AccountMasterVoucher;
use Modules\Accounting\App\Entities\AccountVoucher;
use Modules\Accounting\App\Entities\Config;


/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 *
 * See https://symfony.com/doc/current/doctrine/repository.html
 *
 * @author Md Shafiqul islam <shafiqabs@gmail.com>
 */
class AccountVoucherRepository extends EntityRepository
{

    public function resetVoucher($configId)
    {

        $em = $this->_em;
        $config = $em->getRepository(Config::class)->find($configId);

        if($config){
            $qb = $this->getEntityManager()
                ->getConnection()
                ->createQueryBuilder()
                ->delete('acc_voucher')
                ->where('config_id =:config_id')
                ->setParameter('config_id', $configId);
            $qb->execute();
        }

        $parentHeads = $em->getRepository(AccountMasterVoucher::class)->findBy(['status'=> 1]);

        /** @var AccountVoucher $head */

        foreach ($parentHeads as $head){
            $entity = new AccountVoucher();
            $entity->setConfig($config);
            $entity->setVoucherType($head->getVoucherType());
            $entity->setName($head->getName());
            $entity->setShortName($head->getShortName());
            $entity->setShortCode($head->getShortCode());
            $entity->setSlug($head->getSlug());
            $entity->setMode($head->getMode());
            $entity->setStatus(1);
            $entity->setIsPrivate(1);
            $em->persist($entity);
            $em->flush();
        }
        $vouchers = $em->getRepository(AccountVoucher::class)->findBy(['config'=> $configId]);
        return $vouchers;


    }
}
