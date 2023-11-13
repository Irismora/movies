<?php
  
namespace App\Controller;
  
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Movie;
  
#[Route('/api', name: 'api_')]
class ProjectController extends AbstractController
{
    #[Route('/movies', name: 'movies_index', methods:['get'] )]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $movies = $doctrine
            ->getRepository(Movie::class)
            ->findAll();
    
        $data = [];
    
        foreach ($movies as $movie) {
            $data[] = [
                'id' => $movie->getId(),
                'name' => $movie->getName(),
                'description' => $movie->getDescription(),
            ];
        }
    
        return $this->json($data);
    }
  
  
    #[Route('/newMovie', name: 'new_movie', methods:['post'] )]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();
    
        $movie = new Movie();
        $movie->setName($request->request->get('name'));
        $movie->setDescription($request->request->get('description'));
        $movie->setGender($request->request->get('gender'));
    
        $entityManager->persist($movie);
        $entityManager->flush();
    
        $data =  [
            'id' => $movie->getId(),
            'name' => $movie->getName(),
            'gender' => $movie->getGender(),
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
            'name' => $movie->getName(),
            'description' => $movie->getDescription(),
        ];
            
        return $this->json($data);
    }


}
