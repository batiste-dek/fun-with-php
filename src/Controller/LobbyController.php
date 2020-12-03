<?php

namespace App\Controller;

use App\Entity\Lobby;
use App\Form\LobbyType;
use App\Repository\LobbyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lobby")
 * @Security("is_granted('ROLE_USER')")
 */
class LobbyController extends AbstractController
{
    /**
     * @Route("/", name="lobby_index", methods={"GET"})
     */
    public function index(LobbyRepository $lobbyRepository): Response
    {
        return $this->render('lobby/index.html.twig', [
            'lobbies' => $lobbyRepository->findAll(),
        ]);
    }
    /**
     * @Route("/new", name="lobby_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $lobby = new Lobby();
        $form = $this->createForm(LobbyType::class, $lobby);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($lobby);
            $entityManager->flush();

            return $this->redirectToRoute('lobby_index');
        }

        return $this->render('lobby/new.html.twig', [
            'lobby' => $lobby,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="lobby_page", methods={"GET"})
     */
    public function join(Lobby $lobby): Response
    {
        $user = $this->getUser();
        $lobby->addPlayer($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($lobby);
        $entityManager->flush();
        return $this->render('lobby/show.html.twig', [
            'lobby' => $lobby,
        ]);
    }

    /**
     * @Route("/leave/{id}", name="lobby_leave", methods={"GET"})
     */
     public function leave(Lobby $lobby): Response
     {
         $user = $this->getUser();
         $lobby->removePlayer($user);
         $entityManager = $this->getDoctrine()->getManager();
         $entityManager->persist($lobby);
         $entityManager->flush();
         return $this->redirectToRoute('lobby_index');
     }
    
     /**
     * @Route("/match/${id}", name="lobby_match", methods={"GET"})
     */
     public function match(Lobby $lobby): Response
     {
         $user = $this->getUser();
         $players = $lobby->getPlayers($user);
         if(count($players) == 1) {
            return $this->redirectToRoute('lobby_page', 
                [
                    "id" => $lobby->getId(),
                    "errorMessage" => "No match available",
                ]
            );
         }
         $entityManager = $this->getDoctrine()->getManager();
         $entityManager->persist($lobby);
         $entityManager->flush();
         return $this->redirectToRoute('lobby_page', ["id" => $lobby->getId()]);
     }

    /**
     * @Route("/{id}/edit", name="lobby_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Lobby $lobby): Response
    {
        $form = $this->createForm(LobbyType::class, $lobby);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('lobby_index');
        }

        return $this->render('lobby/edit.html.twig', [
            'lobby' => $lobby,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="lobby_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Lobby $lobby): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lobby->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($lobby);
            $entityManager->flush();
        }

        return $this->redirectToRoute('lobby_index');
    }
}