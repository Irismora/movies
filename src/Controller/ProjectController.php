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
        $apiUrl = "https://api.themoviedb.org/3/movie/popular?api_key={$apiKey}&language=en-US&page=1";

        try {
            $response = $httpClient->request('GET', $apiUrl, [
                'query' => [
                    'api_key' => $apiKey,
                    'language' => 'en-US',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();
            
            // Verificar si la solicitud fue exitosa (código 2xx)
            if ($statusCode >= 200 && $statusCode < 300) {
                $moviesData = $content['results'] ?? [];
                
                return $this->render('project/homepage.html.twig', [
                    'title' => 'Popular Movies',
                    'movies' => $moviesData,
                ]);
            } else {
                // Manejar el error de acuerdo a tus necesidades
                // Aquí puedes lanzar una excepción o mostrar un mensaje de error
                throw new \Exception('Error en la solicitud a la API: ' . $statusCode);
            }
        } catch (\Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante la solicitud
            // Aquí puedes registrar el error, mostrar un mensaje amigable, etc.
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


/*       #[Route(path:"/home", name:"homepage")]
  public function homepage() : Response
  {
    $apiKey = ENV['API_KEY']; 
    
    $apiUrl = "https://api.themoviedb.org/3/movie/popular?api_key={$apiKey}&language=en-US&page=1";
    $httpClient = HttpClient::create();
    $response = $httpClient->request('GET', $apiUrl, [
                'query' => [
                    'api_key' => $apiKey,
                    'language' => 'en-US',
                ],
                ]);
    $responseData = $response->toArray();
        // Obtener la lista de películas desde la respuesta de la API
        $moviesData = isset($responseData['results']) ? $responseData['results'] : [];
        return $this->render('project/homepage.html.twig', [
            'title' => 'Popular Movies',
            'movies' => $moviesData,
        ]);
    } */




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
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();
    
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
            
        return $this->json($data);
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
