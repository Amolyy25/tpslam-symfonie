<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MovieController extends AbstractController
{

    #[Route('/movie')]
    public function index(Request $request, MovieRepository $repo)
    {
        $q = $request->query->get('q');

        $moviesData = $q
            ? $repo->searchByTitle($q)
            : $repo->findAll();

            return $this->render('movie.html.twig', [
                'movies' => $moviesData,
                'total' => $repo->count([]),
                'q' => $q,
            ]);
    }
    #[Route('/movie/new')]
    public function newMovie(Request $request, EntityManagerInterface $em)
    {
        $movie = new Movie();

        $form = $this->createFormBuilder($movie)
            ->add('title', TextType::class)
            ->add('director', TextType::class)
            ->add('releaseYear', IntegerType::class)
            ->add('synopsis', TextareaType::class)
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Action' => 'Action',
                    'Science-Fiction' => 'Science-Fiction',
                    'Comédie' => 'Comédie',
                    'Horreur' => 'Horreur',
                    'Drame' => 'Drame',
                ],
                'placeholder' => 'Choisir un genre',
            ])
            ->add('save', SubmitType::class, ['label' => 'Ajouter le film'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($movie);
            $em->flush();

            return $this->redirectToRoute('app_movie_index');
        }

        return $this->render('movienew.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/movie/{id}/edit', name: 'app_movie_edit')]
    public function edit(Request $request, EntityManagerInterface $em, MovieRepository $repo, int $id)
    {
        $movie = $repo->find($id);

        $form = $this->createFormBuilder($movie)
            ->add('title', TextType::class)
            ->add('director', TextType::class)
            ->add('releaseYear', IntegerType::class)
            ->add('synopsis', TextareaType::class)
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Action' => 'Action',
                    'Science-Fiction' => 'Science-Fiction',
                    'Comédie' => 'Comédie',
                    'Horreur' => 'Horreur',
                    'Drame' => 'Drame',
                ],
                'placeholder' => 'Choisir un genre',
            ])
            ->add('save', SubmitType::class, ['label' => 'Modifier le film'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_movie_index');
        }

        return $this->render('movieedit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/movie/{id}', name: 'app_movie_show')]
    public function show(MovieRepository $repo, int $id)
    {
        $movieData = $repo->find($id);

        return $this->render('show.html.twig', [
            'movie' => $movieData,
        ]);
    }
    #[Route('/movie/{id}/delete', name: 'app_movie_delete')]
    public function delete(EntityManagerInterface $em, MovieRepository $repo, int $id)
    {
        $movie = $repo->find($id);

        if ($movie) {
            $em->remove($movie);
            $em->flush();
        }

        return $this->redirectToRoute('app_movie_index');
    }
}
