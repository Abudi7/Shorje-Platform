<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use App\Repository\SliderImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')")
    ],
    normalizationContext: ['groups' => ['slider_image:read']],
    denormalizationContext: ['groups' => ['slider_image:write']]
)]
#[ORM\Entity(repositoryClass: SliderImageRepository::class)]
#[ORM\Table(name: 'slider_images')]
#[ORM\HasLifecycleCallbacks]
class SliderImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['slider_image:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'عنوان الصورة مطلوب')]
    #[Assert\Length(max: 255, maxMessage: 'العنوان يجب أن يكون أقل من 255 حرف')]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private $image = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private ?string $imageMimeType = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private ?string $buttonText = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private ?string $buttonUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private ?string $buttonText2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private ?string $buttonUrl2 = null;

    #[ORM\Column]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private bool $isActive = true;

    #[ORM\Column]
    #[Groups(['slider_image:read', 'slider_image:write'])]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['slider_image:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['slider_image:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getImageMimeType(): ?string
    {
        return $this->imageMimeType;
    }

    public function setImageMimeType(?string $imageMimeType): static
    {
        $this->imageMimeType = $imageMimeType;
        return $this;
    }

    public function getButtonText(): ?string
    {
        return $this->buttonText;
    }

    public function setButtonText(?string $buttonText): static
    {
        $this->buttonText = $buttonText;
        return $this;
    }

    public function getButtonUrl(): ?string
    {
        return $this->buttonUrl;
    }

    public function setButtonUrl(?string $buttonUrl): static
    {
        $this->buttonUrl = $buttonUrl;
        return $this;
    }

    public function getButtonText2(): ?string
    {
        return $this->buttonText2;
    }

    public function setButtonText2(?string $buttonText2): static
    {
        $this->buttonText2 = $buttonText2;
        return $this;
    }

    public function getButtonUrl2(): ?string
    {
        return $this->buttonUrl2;
    }

    public function setButtonUrl2(?string $buttonUrl2): static
    {
        $this->buttonUrl2 = $buttonUrl2;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
