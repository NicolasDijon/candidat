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

class CommandeController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="commande.liste")
     */
    public function listeAction(Request $request) : Response
    {
        //Récupération de toutes les commandes par date
        $commandes = $this
            ->getDoctrine()
            ->getRepository(Commande::class)
            ->filtreParDate()
        ;

        //Mise en place de la pagination
        $paginator = $this->get('knp_paginator');
        $result = $paginator->paginate(
            $commandes,
            $request->query->getInt('page', 1 ),
            $request->query->getInt('limite', 10 )
        );

        return $this->render("AppBundle:Commande:liste.html.twig", [
            'commandes' => $result,
        ]);
    }

    /**
     * @param Request $request
     * @param Commande $commande
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/voir/{id}", name="commande.voir")
     */
    public function voirAction(Request $request, Commande $commande) 
    {
        //Affichage des produits de la commande
        return $this->render("@App/Commande/voir.html.twig", [
            'commande' => $commande,
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/ajouter", name="commande.ajouter")
     */
    public function ajouterAction(Request $request) : Response
    {
        //Récupération de la liste des produits
        $produits = $this
            ->getDoctrine()
            ->getRepository(Produit::class)
            ->findAll();

        //Création du formulaire d'ajout d'une nouvelle commande
        $commande = new Commande();
        $form = $this->createForm('AppBundle\Form\CommandeType', $commande);
        $form->handleRequest($request);

        //Si le formulaire est soumis et valide, on enregistre la commande en BDD
        if ($form->isSubmitted() && $form->isValid()) {
            //Date du jour
            $commande->setDate(new \DateTime);
            //Référence aléatoire pour la commande
            $commande->setReference($this->referenceAleatoire());
            $em = $this->getDoctrine()->getManager();
            
            //On boucle sur la liste des produits pour les checkbox
            foreach($produits as $produit){
                $produitCb = $request->request->get('produit'.$produit->getId());
                //On ajoute le produit si la case est coché
                if($produitCb == "on") {
                    $commande->addProduit($produit); 
                }
            }

            $em->persist($commande);
            $em->flush();

            //Affichage d'un message flash en cas de succès
            $this->addFlash('success', "La commande a bien été ajoutée !");

            //Redirection vers la liste des commandes
            return $this->redirectToRoute('commande.liste');
        }

        return $this->render("@App/Commande/form.html.twig", [
            'form' => $form->createView(),
            'produits' => $produits,
        ]);
    }

    /**
     * @param Request $request
     * @param Commande $commande
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/modifier/{id}", name="commande.modifier")
     */
    public function modifierAction(Request $request, Commande $commande) 
    {
        //Récupération de la liste des produits
        $produits = $this
            ->getDoctrine()
            ->getRepository(Produit::class)
            ->findAll();

        //Création du formulaire de modification d'une commande
        $form = $this->createForm('AppBundle\Form\CommandeType', $commande);
        $form->handleRequest($request);

        //Si le formulaire est soumis et valide, on modifie la commande en BDD
        if ($form->isSubmitted() && $form->isValid()) {

            //On boucle sur la liste des produits pour les checkbox
            foreach($produits as $produit){
                $produitCb = $request->request->get('produit'.$produit->getId());
                //On ajoute le produit si la case est coché
                if($produitCb == "on") {
                    $commande->addProduit($produit); 
                }
            }

            $this->getDoctrine()->getManager()->flush();

            //Affichage d'un message flash en cas de succès
            $this->addFlash('success', "La commande a bien été modifié !");

            //Redirection vers la liste des commandes
            return $this->redirectToRoute('commande.liste');
        }

        return $this->render("@App/Commande/modifierCommande.html.twig", [
            'form' => $form->createView(),
            'commande' => $commande,
            'produits' => $produits,
        ]);
    }

    /**
     * @param Request $request
     * @param Commande $commande
     * @Route("/supprimer/{id}", name="commande.supprimer")
     */
    public function supprimerAction(Request $request, Commande $commande) {

        //Suppression de la commande en BDD
        $em = $this->getDoctrine()->getManager();
        $em->remove($commande);
        $em->flush();

        //Affichage d'un message flash en cas de succès
        $this->addFlash('success', "La commande a bien été supprimée !");

        //Redirection vers la liste des commandes
        return $this->redirectToRoute('commande.liste');
    }

    /**
     * Fonction qui retourne une référence aléatoire pour une 
     * nouvelle commande.
     */
    public function referenceAleatoire()
    {
        // On définit les lettres possible et la taille de la chaîne
        $letters = 'ABCDEFGHIJKLMOPQRSTUVWXYZ';
        $maxLetters = strlen($letters) - 1;
        
        // On définit les chiffres possible et la taille de la chaîne
        $numbers = '0123456789';
        $maxNumbers = strlen($numbers) - 1;
        
        // Initialisation de la référence avec un #
        $reference = '#';
        
        // Ajout des 3 premières lettres
        for ($i = 0; $i < 3; $i++) {
            $reference .= $letters[rand(0, $maxLetters)];
        }
        
        // Ajout d'un trait de séparation
        $reference .= '-';
        
        // Ajout des 3 chiffres dernier chiffres
        for ($i = 0; $i < 3; $i++) {
            $reference .= $numbers[rand(0, $maxNumbers)];
        }

        // Retourne la référence
        return $reference;
    }
}