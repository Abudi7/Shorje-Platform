<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notifications')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notification:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['notification:read', 'notification:write'])]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $message = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification:read'])]
    private bool $isRead = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification:read'])]
    private bool $isImportant = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $actionUrl = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $actionText = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $icon = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $color = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?array $metadata = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $readAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $expiresAt = null;

    // Notification types constants
    public const TYPE_MESSAGE = 'message';
    public const TYPE_FOLLOW = 'follow';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_ORDER = 'order';
    public const TYPE_REVIEW = 'review';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_PROMOTION = 'promotion';
    public const TYPE_SECURITY = 'security';

    // Notification colors
    public const COLOR_PRIMARY = 'primary';
    public const COLOR_SUCCESS = 'success';
    public const COLOR_WARNING = 'warning';
    public const COLOR_DANGER = 'danger';
    public const COLOR_INFO = 'info';
    public const COLOR_SECONDARY = 'secondary';

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Asia/Baghdad'));
        $this->isRead = false;
        $this->isImportant = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        if ($isRead && !$this->readAt) {
            $this->readAt = new \DateTime('now', new \DateTimeZone('Asia/Baghdad'));
        }
        return $this;
    }

    public function isImportant(): bool
    {
        return $this->isImportant;
    }

    public function setIsImportant(bool $isImportant): static
    {
        $this->isImportant = $isImportant;
        return $this;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function setActionUrl(?string $actionUrl): static
    {
        $this->actionUrl = $actionUrl;
        return $this;
    }

    public function getActionText(): ?string
    {
        return $this->actionText;
    }

    public function setActionText(?string $actionText): static
    {
        $this->actionText = $actionText;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
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

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
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

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeInterface $readAt): static
    {
        $this->readAt = $readAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isExpired(): bool
    {
        if (!$this->expiresAt) {
            return false;
        }
        return $this->expiresAt < new \DateTime('now', new \DateTimeZone('Asia/Baghdad'));
    }

    public function markAsRead(): static
    {
        $this->setIsRead(true);
        return $this;
    }

    public function markAsUnread(): static
    {
        $this->setIsRead(false);
        $this->readAt = null;
        return $this;
    }
}