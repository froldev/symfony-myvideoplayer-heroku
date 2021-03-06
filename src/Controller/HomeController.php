<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\SearchVideoType;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    const MAX_LINKS_NAV = 6;

    /**
     * @Route("/", name="home")
     */
    public function index(
        CategoryRepository $categoryRepository,
        VideoRepository $videoRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $form = $this->createForm(SearchVideoType::class);

        $videos = $paginator->paginate(
            $videoRepository->findBy(['is_best' => true]),
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('home/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'videos' => $videos,
            'formVideo' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard", name="home_admin")
     */
    public function indexAdmin(
        CategoryRepository $categoryRepository,
        VideoRepository $videoRepository,
        UserRepository $userRepository
    ): Response {
        return $this->render('home/indexAdmin.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'videos' => $videoRepository->findAll(),
            'users' => $userRepository->findAll(),
        ]);
    }

    public function renderNavBar(CategoryRepository $categoryRepository, Request $request): Response
    {
        return $this->render('bricks/_navbar.html.twig', [
            'categories' => $categoryRepository->findBy([], [
                'position' => 'ASC',
            ]),
            'max' => self::MAX_LINKS_NAV,
        ]);
    }

    public function renderFooter(CategoryRepository $categoryRepository): Response
    {
        return $this->render('bricks/_footer.html.twig', [
            'categories' => $categoryRepository->findBy([], [
                'position' => 'ASC',
            ]),
        ]);
    }

    /**
     * @Route("/searchBestMovie/{search}", name="search_movie", methods={"GET", "POST"})
     */
    public function searchBestVideo(?String $search, VideoRepository $videoRepository): Response
    {
        if ($search == "all") {
            $videos = $videoRepository->findBy(['is_best' => true]);
        } else {
            $videos = $videoRepository->findBestVideoBySearch($search);
        }

        $arrayVideos = [];
        foreach ($videos as $key => $value) {
            $arrayVideos[$key]['title'] = $value->getTitle();
            $arrayVideos[$key]['slug'] = $value->getSlug();
            $arrayVideos[$key]['url'] = $value->getUrl();
        }

        return new JsonResponse([
            'videos' => $arrayVideos,
        ]);
    }
}
