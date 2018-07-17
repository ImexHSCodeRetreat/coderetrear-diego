<?php

namespace App\Controller;

use App\Entity\Game;
use App\View\GameStatus;
use App\Service\GameService;
use App\Exception\PlayerAlreadyInGameException;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;


class GameController extends Controller
{
    /**
     * @Route("/game", name="new_game"), methods={"POST"}
     */
    public function newGame(Request $request, GameService $gs, SerializerInterface $serializer)
    {
        $player = $request->attributes->get('_player');
        try {
            $game = $gs->registerGame($player);
            $gameStatus = $gs->getGameStatus($player, $game);
            $jgs = $serializer->serialize($gameStatus, 'json');
            return new Response($jgs);
        } catch (PlayerAlreadyInGameException $ex){
            throw new BadRequestHttpException($ex->getMessage());
        }
    }

    /**
     * @Route("/game/{id}", name="get_game"), methods={"GET"}
     */
    public function getGame($id, Request $request, GameService $gs, SerializerInterface $serializer)
    {
        $game = $this->getDoctrine()
            ->getRepository(Game::class)
            ->find($id);
        if (!$game) {
            throw $this->createNotFoundException(
                'No game found for id '.$id
            );
        }
        $player = $request->attributes->get('_player');
        $gameStatus = $gs->getGameStatus($player, $game);
        $jgs = $serializer->serialize($gameStatus, 'json');
        return new Response($jgs);
    }

}