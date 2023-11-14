<?php
  
namespace App\Controller;
  
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Movie;
  
#[Route('/api', name: 'api_')]
class ProjectController extends AbstractController
{
#[Route(path: '/home', name: 'homepage')]
public function homepage(HttpClientInterface $httpClient): Response
{
    $apiKey = ENV['API_KEY'];
    $apiUrl = "https://api.themoviedb.org/3/movie/popular";

    try {
        $moviesData = [];

        for ($page = 1; $page <= 5; $page++) {  
            $response = $httpClient->request('GET', $apiUrl, [
                'query' => [
                    'api_key' => $apiKey,
                    'language' => 'en-US',
                    'page' => $page,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            if ($statusCode >= 200 && $statusCode < 300) {
                $moviesData = array_merge($moviesData, $content['results'] ?? []);
            } else {
                throw new \Exception('Error en la solicitud a la API: ' . $statusCode);
            }
        }

        return $this->render('project/homepage.html.twig', [
            'title' => 'Popular Movies',
            'movies' => $moviesData,
        ]);
    } catch (\Exception $e) {
        return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    #[Route('/movies', name: 'movies_index', methods:['get'] )]
    public function index(ManagerRegistry $doctrine): Response
    {
        $movies = $doctrine
            ->getRepository(Movie::class)
            ->findAll();
    
        $data = [];
    
        foreach ($movies as $movie) {
            $data[] = [
                'id' => $movie->getId(),
                'title' => $movie->getTitle(),
                'poster_path' => $movie->getPosterPath(),
            ];
        }
    
        return $this->render("project/myfavs.html.twig", [
            "movies" => $data,
        ]);
    }
    
    
    #[Route('/newMovie', name: 'new_movie', methods:['post'] )]
    public function create(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $existingMovie = $entityManager->getRepository(Movie::class)->findOneBy([
        'movie_id' => $request->request->get('movie_id'),
        ]);

        if ($existingMovie) {
        return $this->json(['message' => 'Movie already in favorites']);
    }
    
        $movie = new Movie();
        $movie->setTitle($request->request->get('title'));
        $movie->setPosterPath($request->request->get('poster_path'));
        $movie->setMovieId($request->request->get('movie_id'));
    
        $entityManager->persist($movie);
        $entityManager->flush();
    
        $data =  [
            'id' => $movie->getId(),
            'title' => $movie->getTitle(),
            'poster_path' => $movie->getPosterPath(),
            'movie_id' => $movie->getMovieId(),
        ];
            
        return $this->render("project/newMovie.html.twig", [
            "movie" => $data,
        ]);
    }
    
    
    #[Route('/movie/{id}', name: 'movie_detail', methods:['get'] )]
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $movie = $doctrine->getRepository(movie::class)->find($id);
    
        if (!$movie) {
    
            return $this->json('No movie found for id ' . $id, 404);
        }
    
        $data =  [
            'id' => $movie->getId(),
            'title' => $movie->getTitle(),
            'poster_path' => $movie->getPosterPath(),
        ];
            
        return $this->json($data);
    }
}
