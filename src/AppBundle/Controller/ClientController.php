<?php
/**
 * Created by PhpStorm.
 * User: m.kaisser
 * Date: 09/05/2017
 * Time: 10:08
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Commande;
use AppBundle\Form\ClientType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ClientController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="client.liste")
     */
    public function listeAction(Request $request) : Response
    {
        //Récupération de la liste des clients par ordre alphabétique
        $clients = $this
            ->getDoctrine()
            ->getRepository(Client::class)
            ->filtreParNom()
        ;

        return $this->render("@App/Client/liste.html.twig", [
            'clients' => $clients,
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/ajouter", name="client.ajouter")
     */
    public function ajouterAction(Request $request) {

        //Création du formulaire d'ajout d'un nouveau client
        $client = new Client();
        $form = $this->createForm('AppBundle\Form\ClientType', $client);
        $form->handleRequest($request);

        //Si le formulaire est soumis et valide, on enregistre le nouveau client en BDD
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($client);
            $em->flush();

            //Affichage d'un message flash en cas de succès
            $this->addFlash('success', "Le client a bien été ajouté !");

            //Redirection vers la liste des clients
            return $this->redirectToRoute('client.liste');
        }

        return $this->render("@App/Client/form.html.twig", [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/modifier/{id}", name="client.modifier")
     */
    public function modifierAction(Request $request, Client $client) {

        //Création du formulaire de modification d'un client
        $form = $this->createForm('AppBundle\Form\ClientType', $client);
        $form->handleRequest($request);

        //Si le formulaire est soumis et valide, on modifie le client en BDD
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            //Affichage d'un message flash en cas de succès
            $this->addFlash('success', "Le client a bien été modifié !");

            //Redirection vers la liste des clients
            return $this->redirectToRoute('client.liste');
        }

        return $this->render("@App/Client/modifierClient.html.twig", [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/voir/{id}", name="client.voir")
     */
    public function voirAction(Request $request, Client $client) 
    {
        //Récupération de la liste des commandes du client
        $commandes = $this
            ->getDoctrine()
            ->getRepository(Commande::class)
            ->findBy(array('client' => $client))
        ;

        return $this->render("@App/Client/voir.html.twig", [
            'client' => $client,
            'commandes' => $commandes,
        ]);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @Route("/supprimer/{id}", name="client.supprimer")
     */
    public function supprimerAction(Request $request, Client $client) {

        //Suppression du client de la BDD
        $em = $this->getDoctrine()->getManager();
        $em->remove($client);
        $em->flush();

        //Affichage d'un message flash en cas de succès
        $this->addFlash('success', "Le client a bien été supprimé !");

        //Redirection vers la liste des clients
        return $this->redirectToRoute('client.liste');
    }
}