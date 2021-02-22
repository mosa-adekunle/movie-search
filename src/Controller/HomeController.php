<?php

namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use App\Model\MovieModel;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{

    public function home(): Response
    {
        return $this->render('home.html', [
            'movie_name' => '',
            'movie_info' => ''
        ]);
    }

    /**
     * @Route("/search", name="search")
     * @param MovieModel $movie_model
     * @return Response
     */
    public function searchName(MovieModel $movie_model): Response
    {
        $request = Request::createFromGlobals();        
        $movie_name = $request->query->get('movie-name');
        $movie_info = $movie_model->findMovieInfo($movie_name);
        $movie_info_display = [];

        if($movie_info){
            foreach($movie_info as $key => $value){
                if(is_array($value)) $value = json_encode($value);
                $movie_info_display[$key] = $value;
            }
        }

        return $this->render('home.html', [
            'movie_name' => $movie_name,
            'movie_info' => $movie_info_display
        ]);
    }

}
