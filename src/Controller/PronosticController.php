<?php

namespace App\Controller;

use App\Entity\Pronostic;
use App\Event\DoctrineLogEvent;
use App\EventListener\DoctrineLogListener;
use App\Form\PronosticType;
use App\Repository\PronosticRepository;
use App\Repository\UtilisateurRepository;
use App\Service\MailerService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\FootballCupController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/pronostic")
 */
class PronosticController extends AbstractController
{

    private $logger;

    public function __construct(FootballCupController $football_cup){
        $this->football_cup = $football_cup;
    }
    /**
     * @Route("/", name="pronostic_index", methods="GET")
     */
    public function index(PronosticRepository $pronosticRepository): Response
    {
        //$liste_competitions = $this->football_cup->getListGames();
        return $this->render('pronostic/index.html.twig', ['pronostics' => $pronosticRepository->findAll()]);
    }

    /**
     * @Route("/new", name="pronostic_new", methods="GET|POST")
     */
    public function new(Request $request, MailerService $mailerService, EventDispatcherInterface $dispatcher): Response
    {
        $pronostic = new Pronostic();
        $form = $this->createForm(PronosticType::class, $pronostic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($pronostic);
            $em->flush();

//            eventlistener utilisant le kernel.vent_listener de SF (!= GameController::class)
            $messageLog = 'Pronostic successfully created.';
//            $logEvent = new DoctrineLogEvent($messageLog);
//            $dispatcher->dispatch($logEvent, DoctrineLogEvent::NAME);


//            $dispatcher->dispatch($event)

//            $mailerService->sendMail('sicomore@hotmail.com','no-reply@babyfootgame.com','Pronostic ajouté','Un pronostic a été ajouté');

            return $this->redirectToRoute('pronostic_index');
        }

        return $this->render('pronostic/new.html.twig', [
            'pronostic' => $pronostic,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/{id}", name="pronostic_show", methods="GET")
     */
    public function show(Pronostic $pronostic, UtilisateurRepository $utilisateurRepository, PronosticRepository $pronosticRepository): Response
    {
        return $this->render('pronostic/show.html.twig', [
            'pronostic' => $pronostic,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="pronostic_edit", methods="GET|POST")
     */
    public function edit(Request $request, Pronostic $pronostic): Response
    {
        $this->denyAccessUnlessGranted('PRONOSTIC_EDIT', $pronostic);

//        if($this->getUser()->getId() == $pronostic->getIdUser()->getId())
//        {
        $form = $this->createForm(PronosticType::class, $pronostic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('pronostic_edit', ['id' => $pronostic->getId()]);
        }

        return $this->render('pronostic/edit.html.twig', [
            'pronostic' => $pronostic,
            'form' => $form->createView(),
        ]);
//        }
//        else  {
//            throw new AccessDeniedException('Vous ne pouvez modifier que vos propres pronostics!');
//        }
    }

    /**
     * @Route("/{id}", name="pronostic_delete", methods="DELETE")
     */
    public function delete(Request $request, Pronostic $pronostic): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pronostic->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($pronostic);
            $em->flush();
        }

        return $this->redirectToRoute('pronostic_index');
    }
}
