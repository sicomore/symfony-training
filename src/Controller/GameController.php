<?php

namespace App\Controller;

use App\Entity\Game;
use App\Event\DoctrineLogEvent;
use App\EventListener\DoctrineLogListener;
use App\Form\GameType;
use App\Repository\GameRepository;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/game")
 */
class GameController extends AbstractController
{
    /** @var AdapterInterface */
    private $adapter;

    /**
     * GameController constructor.
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @Route("/", name="game_index", methods="GET")
     */
    public function index(GameRepository $gameRepository): Response
    {
        $item = $this->adapter->getItem('games.list');
        if (!$item->isHit()) {
            $list = $gameRepository->findAll();
            $item->set($list);
            $this->adapter->save($item);
        }

        $response = $this->render('game/index.html.twig', ['games' => $item->get()]);
        return $response;
    }

    /**
     * @Route("/new", name="game_new", methods="GET|POST")
     */
    public function new(Request $request, EventDispatcherInterface $dispatcher, LoggerInterface $logger): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($game);
            $em->flush();
            $em->clear();

            $this->adapter->deleteItem('games.list');
//            eventListener totalement customizé (!= PronosticController::class)
            $messageLog = 'Game successfully created!';
//            $event = new DoctrineLogEvent($messageLog);
//            $listener = new DoctrineLogListener($logger);
//            $dispatcher->addListener(DoctrineLogEvent::NAME, [$listener, 'onEntityCreate']);
//            $dispatcher->dispatch($event, DoctrineLogEvent::NAME);

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="game_show", methods="GET")
     */
    public function show(Game $game): Response
    {
        $item = $this->adapter->getItem('game.show.'.$game->getId());
        if (!$item->isHit()) {
            $bon_pronostic = $this->getDoctrine()->getRepository(Game::class)->findBonPronostic($game->getId());
            $item->set($game);
            $this->adapter->save($item);
        }

        $response = $this->render('game/show.html.twig', [
            'game' => $game,
            'pronostics' => $item->get(),
        ]);
        $response->setMaxAge(60);

        return $response;
    }

    /**
     * @Route("/{id}/edit", name="game_edit", methods="GET|POST")
     */
    public function edit(Request $request, Game $game): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->adapter->deleteItem('games.list');
            if ($game->getId() === $this->adapter->getItem('game.show')) {
                $this->adapter->deleteItem('game.show');
            }

            return $this->redirectToRoute('game_edit', ['id' => $game->getId()]);
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="game_delete", methods="DELETE")
     */
    public function delete(Request $request, Game $game): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($game);
            $em->flush();
        }

        return $this->redirectToRoute('game_index');
    }
}
