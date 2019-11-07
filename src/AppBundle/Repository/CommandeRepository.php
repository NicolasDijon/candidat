<?php

namespace AppBundle\Repository;

/**
 * CommandeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CommandeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Méthode qui retourne la liste des commande d'un client
     * 
     * @return Commande[] 
     */
    public function commandeParClient($client)
    {
        return $this->createQueryBuilder('c')
            ->where('c.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Méthode qui retourne la liste des commande par produit
     * 
     * @return Commande[] 
     */
    public function produitParCommande($produit): array
    {
        return $this->createQueryBuilder('c')
            ->select('c, p')
            ->leftJoin('c.produits', 'p')
            ->where('p.id = :produits')
            ->setParameter('produits', $produit)
            ->getQuery()
            ->getResult();
        ;
    }

    /**
    * Méthode qui retourne la liste des commande par date
    *
    * @return Commande[] 
    */
    public function filtreParDate(): array
    {
        return $this->createQueryBuilder('c')
            ->addOrderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
