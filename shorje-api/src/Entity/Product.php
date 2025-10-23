<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER')")
    ],
    normalizationContext: ['groups' => ['product:read']],
    denormalizationContext: ['groups' => ['product:write']]
)]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'عنوان المنتج مطلوب')]
    #[Assert\Length(max: 255, maxMessage: 'العنوان يجب أن يكون أقل من 255 حرف')]
    #[Groups(['product:read', 'product:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'وصف المنتج مطلوب')]
    #[Groups(['product:read', 'product:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'سعر المنتج مطلوب')]
    #[Assert\Positive(message: 'السعر يجب أن يكون رقم موجب')]
    #[Groups(['product:read', 'product:write'])]
    private ?string $price = null;

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank(message: 'عملة المنتج مطلوبة')]
    #[Assert\Choice(
        choices: ['IQD', 'USD'],
        message: 'العملة يجب أن تكون دينار عراقي أو دولار أمريكي'
    )]
    private ?string $currency = 'IQD';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'فئة المنتج مطلوبة')]
    #[Assert\Choice(
        choices: ['car', 'home_rental', 'apartment_rental', 'job', 'laptop', 'electronics', 'fashion', 'furniture', 'books', 'sports', 'other'],
        message: 'فئة المنتج غير صحيحة'
    )]
    private ?string $category = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'المدينة مطلوبة')]
    private ?string $city = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'الموقع مطلوب')]
    private ?string $location = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(name: 'product_condition', length: 50, nullable: true)]
    private ?string $condition = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'حالة المنتج مطلوبة')]
    #[Assert\Choice(
        choices: ['available', 'sold', 'reserved'],
        message: 'حالة المنتج غير صحيحة'
    )]
    private ?string $status = 'available';

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $image1 = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $image2 = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $image3 = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $image1MimeType = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $image2MimeType = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $image3MimeType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $seller = null;

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

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): static
    {
        $this->condition = $condition;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getImage1()
    {
        return $this->image1;
    }

    public function setImage1($image1): static
    {
        $this->image1 = $image1;
        return $this;
    }

    public function getImage2()
    {
        return $this->image2;
    }

    public function setImage2($image2): static
    {
        $this->image2 = $image2;
        return $this;
    }

    public function getImage3()
    {
        return $this->image3;
    }

    public function setImage3($image3): static
    {
        $this->image3 = $image3;
        return $this;
    }

    public function getImage1MimeType(): ?string
    {
        return $this->image1MimeType;
    }

    public function setImage1MimeType(?string $image1MimeType): static
    {
        $this->image1MimeType = $image1MimeType;
        return $this;
    }

    public function getImage2MimeType(): ?string
    {
        return $this->image2MimeType;
    }

    public function setImage2MimeType(?string $image2MimeType): static
    {
        $this->image2MimeType = $image2MimeType;
        return $this;
    }

    public function getImage3MimeType(): ?string
    {
        return $this->image3MimeType;
    }

    public function setImage3MimeType(?string $image3MimeType): static
    {
        $this->image3MimeType = $image3MimeType;
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

    public function getSeller(): ?User
    {
        return $this->seller;
    }

    public function setSeller(?User $seller): static
    {
        $this->seller = $seller;
        return $this;
    }

    public function getCategoryDisplayName(): string
    {
        $categories = [
            'car' => 'سيارات',
            'home_rental' => 'إيجار منازل',
            'apartment_rental' => 'إيجار شقق',
            'job' => 'وظائف',
            'laptop' => 'لابتوب',
            'electronics' => 'إلكترونيات',
            'fashion' => 'أزياء',
            'furniture' => 'أثاث',
            'books' => 'كتب',
            'sports' => 'رياضة',
            'other' => 'أخرى'
        ];

        return $categories[$this->category] ?? $this->category;
    }

    public function getStatusDisplayName(): string
    {
        $statuses = [
            'available' => 'متاح',
            'sold' => 'مباع',
            'reserved' => 'محجوز'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getCurrencyDisplayName(): string
    {
        $currencies = [
            'IQD' => 'دينار عراقي',
            'USD' => 'دولار أمريكي'
        ];

        return $currencies[$this->currency] ?? $this->currency;
    }
}