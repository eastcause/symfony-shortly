<?php

namespace App\Controller;

use App\Entity\ShortUrl;
use App\Form\ShortFormType;
use App\Service\ShortUrlService;
use App\Util\UrlValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IndexController extends AbstractController
{
    const REDIRECT = 'http://localhost/short/';

    private ValidatorInterface $validator;
    private ShortUrlService $shortUrlService;
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;

    /**
     * @param ValidatorInterface $validator
     * @param ShortUrlService $shortUrlService
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ValidatorInterface $validator, ShortUrlService $shortUrlService, RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->validator = $validator;
        $this->shortUrlService = $shortUrlService;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }


    #[Route('/short/{id}', name: 'app_redirect', priority: 1000)]
    public function indexRedirect(string $id) : Response {
        $shortUrlRepo = $this->entityManager->getRepository(ShortUrl::class);
        $shortUrl = $shortUrlRepo->findOneBy(['shortUrl' => $id]);

        if ($shortUrl === null) {
            $this->addFlash('error', 'Nie odnaleziono przekierowania '. $this->requestStack->getCurrentRequest()->getUri());
            return $this->redirectToRoute('app_index');
        }

        return $this->redirect($shortUrl->getUrl());
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {

        $form = $this->createForm(ShortFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $url = $form->get('url')->getData();

            if (!UrlValidatorUtil::validate($url, $this->validator)) {
                $this->addFlash('error', 'Podany link jest nieprawidłowy.');
                return $this->redirectToRoute('app_index');
            }

            try {
                $shortUrl = $this->shortUrlService->createUrl($url, $this->getUser());

                if ($shortUrl === 'ALREADY_EXISTS_SHORT' || $shortUrl === 'ERROR') {
                    $this->addFlash('error', 'Wystąpił błąd spróbuj ponownie!');
                    return $this->redirectToRoute('app_index');
                }

                if (!$this->getUser()) {
                    $this->addFlash('success', 'Link został skrócony, lecz nie został przypisany do żadnego konta!');
                }
                $this->addFlash('success', 'Skrócony link: ' . $this->requestStack->getCurrentRequest()->getUri() . 'short/' . $shortUrl);

                return $this->redirectToRoute('app_index');
            } catch (\Exception $exception)
            {
                $this->addFlash('error', 'Wystąpił błąd spróbuj ponownie później!');
                return $this->redirectToRoute('app_index');
            }
        }

        return $this->render('index/index.html.twig', [
            'shortForm' => $form->createView(),
        ]);
    }
}
