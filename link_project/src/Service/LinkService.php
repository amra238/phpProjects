<?php
namespace App\Service;

use App\Entity\Link;
use App\Entity\User;
use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;

class LinkService
{
    public function __construct(
        private EntityManagerInterface $em,
        private LinkRepository $linkRepository,
    ) {}
    public function createFromForm(User $user, Link $link): void
    {
        if (empty($link->getFullUrl())) {
            throw new \Exception('Link must have a full url');
        }

        $link->setUser($user);
        $link->setShortCode(substr(md5(uniqid()), 0, 6));
        $user->addLink($link);

        $this->em->persist($link);
        $this->em->flush();
    }

    public function processingToRedirect(string $code) : Link
    {
        $link = $this->linkRepository->findByShortCode($code);

        if (!$link) {
            throw new \Exception('Link not found');
        }
        if (!$link->isLinkAlive()) {
            throw new \Exception('link is invalid');
        }

        $link->incrementVisitCount();
        $link->setLastUsedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $link;
    }

    public  function processingToDelete(int $id, User $user) : void
    {
        $link = $this->linkRepository->find($id);

        if (!$link) {
            throw new \Exception('Link not found');
        }
        if ($link->getUser()->getEmail() !== $user->getEmail()) {
            throw new \Exception('you must be authorized to delete this link');
        }

        $user->removeLink($link);
        $this->em->remove($link);
        $this->em->flush();
    }
}
