<?php

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Override;

#[ORM\Entity]
#[ORM\Table(name: 'week')]
class Week extends AbstractMessage implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'smallint', nullable: false)]
    private int $year = 1970;

    #[ORM\Column(type: 'smallint', nullable: false)]
    private int $calendarWeek = 0;

    #[ORM\OneToMany(mappedBy: 'week', targetEntity: Day::class, cascade: ['all'])]
    #[ORM\OrderBy(['dateTime' => 'ASC'])]
    private Collection $days;

    public function __construct()
    {
        $this->days = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDays(): Collection
    {
        return $this->days;
    }

    /**
     * @param Collection<int, Day> $days
     */
    public function setDays(Collection $days): void
    {
        $this->days = $days;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getCalendarWeek(): int
    {
        return $this->calendarWeek;
    }

    public function setCalendarWeek(int $calendarWeek): void
    {
        $this->calendarWeek = $calendarWeek;
    }

    public function getStartTime(): DateTime
    {
        $datetime = $this->getWeekDateTime();
        $datetime->setTime(0, 0);

        return $datetime;
    }

    public function getEndTime(): DateTime
    {
        $endTime = $this->getWeekDateTime();
        $endTime->modify('+4 days 23:59:59');

        return $endTime;
    }

    private function getWeekDateTime(): DateTime
    {
        $dateTime = new DateTime();
        $dateTime->setISODate($this->getYear(), $this->getCalendarWeek());

        return $dateTime;
    }

    /**
     * @return (array|bool|int|null)[]
     *
     * @psalm-return array{id: int|null, year: int, calendarWeek: int, days: array<array-key|mixed, mixed>, enabled: bool}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        $days = [];
        $replacementId = -1;

        foreach ($this->getDays() as $day) {
            $id = null !== $day->getId() ? $day->getId() : $replacementId--;
            $days[$id] = $day->jsonSerialize();
        }

        return [
            'id' => $this->getId(),
            'year' => $this->getYear(),
            'calendarWeek' => $this->getCalendarWeek(),
            'days' => $days,
            'enabled' => $this->isEnabled(),
        ];
    }

    public function __toString()
    {
        return (string) $this->getCalendarWeek();
    }
}
