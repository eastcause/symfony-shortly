<?php

namespace App\Service;

use App\Entity\ShortUrl;
use App\Entity\User;
use App\Repository\ShortUrlRepository;
use App\Util\RandomStringGeneratorUtil;
use Doctrine\ORM\EntityManagerInterface;

class ShortUrlService
{

    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAllUserUrls(User $user)
    {
        /**
         * @var $shorRepo ShortUrlRepository
         */
        $shorRepo = $this->entityManager->getRepository(ShortUrl::class);

        $queryBuilder = $shorRepo->createQueryBuilder('short_url');
        $queryBuilder
            ->where('short_url.user = ' .$user->getId())
            ->orderBy('short_url.createdAt', 'DESC');
        return $queryBuilder->getQuery()->getResult();
    }

    public function createUrl(string $url, ?User $user) : string
    {
        /**
         * @var $shorRepo ShortUrlRepository
         */
        $shorRepo = $this->entityManager->getRepository(ShortUrl::class);

        $shortenUrl = RandomStringGeneratorUtil::generateRandomString();

        if ($shorRepo->findOneBy(['shortUrl' => $shortenUrl]) !== null) {
            return 'ALREADY_EXISTS_SHORT';
        }

        try {
            $shortUrl = new ShortUrl();
            $shortUrl->setUrl($url);
            $shortUrl->setShortUrl($shortenUrl);
            $shortUrl->setUser($user);
            $shortUrl->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($shortUrl);
            $this->entityManager->flush();

            return $shortenUrl;
        } catch (\Exception $exception)
        {
            return 'ERROR';
        }
    }

    public function getById(int $id) : ?ShortUrl
    {
        /**
         * @var $shorRepo ShortUrlRepository
         */
        $shorRepo = $this->entityManager->getRepository(ShortUrl::class);
        return $shorRepo->findOneBy(['id' => $id]);
    }

    public function delete(ShortUrl $shortUrl) : bool {
        try {
            $this->entityManager->remove($shortUrl);
            $this->entityManager->flush();
        }
        catch (\Exception $e) {
            return false;
        }
        return true;
    }


}