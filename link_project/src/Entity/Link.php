<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2048)]
    #[Constraints\NotBlank(message: 'URL не может быть пустым')]
    #[Constraints\Url(message: 'недопустимый формат для url')]
    private string $fullUrl;

    #[ORM\Column(length: 255, unique: true)]
    private string $shortCode;

    #[ORM\Column]
    private bool $isOneTime;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "links")]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user;

    #[ORM\Column(nullable: true)]
    #[Constraints\Type(\DateTimeImmutable::class, message: 'недопустимый формат для даты')]
    private \DateTimeImmutable $expirationDate;

    #[ORM\Column]
    #[Constraints\Type(\DateTimeImmutable::class, message: 'недопустимый формат для даты')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    #[Constraints\Type(\DateTimeImmutable::class, message: 'недопустимый формат для даты')]
    private ?\DateTimeImmutable $lastUsedAt = null;

    #[ORM\Column]
    private int $visitCount = 0;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullUrl(): string
    {
        return $this->fullUrl;
    }

    public function setFullUrl(string $fullUrl): void
    {
        $this->fullUrl = $fullUrl;
    }


    public function getShortCode(): string
    {
        return $this->shortCode;
    }

    public function setShortCode(string $shortCode): void
    {
        $this->shortCode = $shortCode;
    }

    public function getIsOneTime(): bool
    {
        return $this->isOneTime;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getExpirationDate(): \DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?\DateTimeImmutable $lastUsedAt): void
    {
        $this->lastUsedAt = $lastUsedAt;
    }

    public function getVisitCount(): int
    {
        return $this->visitCount;
    }

    public function setVisitCount(int $visitCount): void
    {
        $this->visitCount = $visitCount;
    }

    public function setUser(mixed $user): void
    {
        $this->user = $user;
    }

    public function setIsOneTime(bool $isOneTime): void
    {
        $this->isOneTime = $isOneTime;
    }

    public function setExpirationDate(\DateTimeImmutable $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public  function  isLinkAlive(): bool
    {
        if (($this->getExpirationDate() !== null && $this->getExpirationDate() < new \DateTime())
            || ($this->getIsOneTime() && $this->getVisitCount() > 0)
            || empty($this->getFullUrl())) {

            return false;
        }

        return true;
    }

    public function incrementVisitCount(): void
    {
        ++$this->visitCount;
    }
}
