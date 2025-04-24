<?php

namespace App\Entity;

use App\Repository\SongRepository;
use App\Traits\StatisticsPropertiesTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: SongRepository::class)]
class Song
{
    use StatisticsPropertiesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["song"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["song"])]
    #[Assert\Length(
        min: 5,
        max: 7,
        minMessage: 'Your first name must be at least {{ limit }} characters long',
        maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["song"])]
    private ?string $Artiste = null;

    /**
     * @var Collection<int, Pool>
     */
    #[ORM\ManyToMany(targetEntity: Pool::class, inversedBy: 'songs')]
    #[Groups(["song"])]
    private Collection $pools;

    public function __construct()
    {
        $this->pools = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getArtiste(): ?string
    {
        return $this->Artiste;
    }

    public function setArtiste(?string $Artiste): static
    {
        $this->Artiste = $Artiste;

        return $this;
    }

    /**
     * @return Collection<int, Pool>
     */
    public function getPools(): Collection
    {
        return $this->pools;
    }

    public function addPool(Pool $pool): static
    {
        if (!$this->pools->contains($pool)) {
            $this->pools->add($pool);
        }

        return $this;
    }

    public function removePool(Pool $pool): static
    {
        $this->pools->removeElement($pool);

        return $this;
    }

}
