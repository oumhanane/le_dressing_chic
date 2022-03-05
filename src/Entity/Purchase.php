<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PurchaseRepository;
use symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Purchase

{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'datetime')]
    private $createAt;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message : 'Vous devez renseigner le pays.')]
    private $country;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message : 'Vous devez renseigner la ville.')]
    private $street;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message : 'Vous devez renseigner le code postal.')]
    private $postalCode;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message : 'Vous devez renseigner le téléphone.')]
    private $telephone;

    #[ORM\OneToOne(mappedBy: 'purchase', targetEntity: ListProduct::class, cascade: ['persist', 'remove'])]
    private $listProduct;

    #[ORM\OneToOne(mappedBy: 'purchase', targetEntity: Invoice::class, cascade: ['persist', 'remove'])]
    private $invoice;

    #[ORM\Column(type: 'string', length: 255)]
    private $city;

    #[ORM\PrePersist]
    public function prePersist()
    {
        if(empty($this->createAt))
        {
            $this->createAt = new DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getListProduct(): ?ListProduct
    {
        return $this->listProduct;
    }

    public function setListProduct(ListProduct $listProduct): self
    {
        // set the owning side of the relation if necessary
        if ($listProduct->getPurchase() !== $this) {
            $listProduct->setPurchase($this);
        }

        $this->listProduct = $listProduct;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): self
    {
        // set the owning side of the relation if necessary
        if ($invoice->getPurchase() !== $this) {
            $invoice->setPurchase($this);
        }

        $this->invoice = $invoice;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }
}
