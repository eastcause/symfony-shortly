<?php

namespace App\Controller;

use App\Entity\ShortUrl;
use App\Service\ShortUrlService;
use Doctrine\DBAL\Schema\Index;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PanelController extends AbstractController
{

    private ShortUrlService $shortUrlService;

    /**
     * @param ShortUrlService $shortUrlService
     */
    public function __construct(ShortUrlService $shortUrlService)
    {
        $this->shortUrlService = $shortUrlService;
    }


    #[Route('/panel', name: 'app_panel')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Nie jesteś zalogowany!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('panel/index.html.twig', [
            'controller_name' => 'PanelController',
            'redirect_url' => IndexController::REDIRECT,
            'urls' => $this->shortUrlService->getAllUserUrls($this->getUser()),
        ]);
    }

    #[Route('/panel/delete/{id}', name: 'app_panel_delete')]
    public function delete(int $id, AuthorizationCheckerInterface $authorizationChecker) : Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Nie jesteś zalogowany!');
            return $this->redirectToRoute('app_index');
        }

        $shortUrl = $this->shortUrlService->getById($id);
        if ($shortUrl === null) {
            $this->addFlash('error', 'Nie odnaleziono takiego linku!');
            return $this->redirectToRoute('app_panel');
        }

        if ($authorizationChecker->isGranted('ROLE_ADMIN')) {
            if ($this->shortUrlService->delete($shortUrl)) {
                $this->addFlash('success', 'Link został usunięty!');
                return $this->redirectToRoute('app_panel');
            }

            $this->addFlash('error', 'Wystąpił bład, spróbuj ponownie!');
            return $this->redirectToRoute('app_panel');
        }

        if ($shortUrl->getUser() === null) {
            $this->addFlash('error', 'Ten link nie ma właściciela!');
            return $this->redirectToRoute('app_panel');
        }

        if ($shortUrl->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Ten link nie należy do Ciebie!');
            return $this->redirectToRoute('app_panel');
        }

        if ($this->shortUrlService->delete($shortUrl)) {
            $this->addFlash('success', 'Link został usunięty!');
            return $this->redirectToRoute('app_panel');
        }

        $this->addFlash('error', 'Wystąpił bład, spróbuj ponownie!');
        return $this->redirectToRoute('app_panel');
    }
}
