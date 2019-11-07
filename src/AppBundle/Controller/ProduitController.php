<?php
/**
 * Created by PhpStorm.
 * User: m.kaisser
 * Date: 09/05/2017
 * Time: 10:03
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Produit;
use AppBundle\Entity\Commande;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProduitController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="produit.liste")
     */
    public function listeAction(Request $request) : Response
    {
        //Récupération de la liste des produits
        $produits = $this
            ->getDoctrine()
            ->getRepository(Produit::class)
            ->findAll()
        ;

        //Mise en place de la pagination
        $paginator = $this->get('knp_paginator');
        $result = $paginator->paginate(
            $produits,
            $request->query->getInt('page', 1 ),
            $request->query->getInt('limite', 10 )
        );

        return $this->render("AppBundle:Produit:liste.html.twig", [
            'produits' => $result,
        ]);
    }

    /**
     * @param Request $request
     * @param Produit $produit
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/voir/{id}", name="produit.voir")
     */
    public function voirAction(Request $request, Produit $produit) 
    {
        //Récupération de la liste de toutes les commandes ou un produit est présent
        $commandes = $this
            ->getDoctrine()
            ->getRepository(Commande::class)
            ->produitParCommande($produit)
        ;

        return $this->render("@App/Produit/voir.html.twig", [
            'produit' => $produit,
            'commandes' => $commandes,
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/ajouter", name="produit.ajouter")
     */
    public function ajouterAction(Request $request) : Response
    {
        //Création du formulaire d'ajout d'un nouveau produit
        $produit = new Produit();
        $form = $this->createForm('AppBundle\Form\ProduitType', $produit);
        $form->handleRequest($request);

        //Si le formulaire est soumis et valide, on enregistre le nouveau produit en BDD
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($produit);
            $em->flush();

            //Affichage d'un message flash en cas de succès
            $this->addFlash('success', "Le produit a bien été ajouté !");

            //Redirection vers la liste des produits
            return $this->redirectToRoute('produit.liste');
        }

        return $this->render("@App/Produit/form.html.twig", [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    /**
     * @param Request $request
     * @param Produit $produit
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/modifier/{id}", name="produit.modifier")
     */
    public function modifierAction(Request $request, Produit $produit) : Response
    {
        //Création du formulaire de modification d'un produit
        $form = $this->createForm('AppBundle\Form\ProduitType', $produit);
        $form->handleRequest($request);

        //Si le formulaire est soumis et valide, on modifie le produit en BDD
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            //Affichage d'un message flash en cas de succès
            $this->addFlash('success', "Le produit a bien été modifié !");

            //Redirection vers la liste des produits
            return $this->redirectToRoute('produit.liste');
        }

        return $this->render("@App/Produit/modifierProduit.html.twig", [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    /**
     * @param Request $request
     * @param Produit $produit
     * @Route("/supprimer/{id}", name="produit.supprimer")
     */
    public function supprimerAction(Request $request, Produit $produit) {

        //Suppression du produit en BDD
        $em = $this->getDoctrine()->getManager();
        $em->remove($produit);
        $em->flush();

        //Affichage d'un message flash en cas de succès
        $this->addFlash('success', "Le produit a bien été supprimé !");

        //Redirection vers la liste des produits
        return $this->redirectToRoute('produit.liste');
    }

    /**
     * @param Request $request
     * @param Produit $produit
     * 
     * Controlleur qui affiche la liste des produit par ordre desc
     * 
     * @Route("/produit/desc", name="produit.desc")
     */
    public function produitDesc(Request $request) 
    {
        //Récupération de tout les produits par ordre desc
        $produits = $this
            ->getDoctrine()
            ->getRepository(Produit::class)
            ->produitDesc();
            
            //Mise en place de la pagination
            $paginator = $this->get('knp_paginator');
            $result = $paginator->paginate(
            $produits,
            $request->query->getInt('page', 1 ),
            $request->query->getInt('limite', 10 )
            );
        
        return $this->render("AppBundle:Produit:liste.html.twig", [
            'produits' => $result,
        ]);
    }

    /**
     * @param Request $request
     * @param Produit $produit
     * 
     * Controlleur qui affiche la liste des produit par ordre asc
     * 
     * @Route("/produit/asc", name="produit.asc")
     */
    public function produitAsc(Request $request) 
    {
        //Récupération de tout les produits par ordre asc
        $produits = $this
            ->getDoctrine()
            ->getRepository(Produit::class)
            ->produitAsc();

        //Mise en place de la pagination
        $paginator = $this->get('knp_paginator');
        $result = $paginator->paginate(
        $produits,
        $request->query->getInt('page', 1 ),
        $request->query->getInt('limite', 10 )
        );
            
        return $this->render("AppBundle:Produit:liste.html.twig", [
            'produits' => $result,
        ]);
    }
}