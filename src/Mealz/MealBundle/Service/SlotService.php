<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class SlotService
{
    private EntityManagerInterface $em;
    private ParticipantRepositoryInterface $participantRepo;
    private SlotRepositoryInterface $slotRepo;
    private DayRepositoryInterface $dayRepo;

    public function __construct(
        EntityManagerInterface $em,
        ParticipantRepositoryInterface $participantRepo,
        SlotRepositoryInterface $slotRepo,
        DayRepositoryInterface $dayRepo
    ) {
        $this->em = $em;
        $this->participantRepo = $participantRepo;
        $this->slotRepo = $slotRepo;
        $this->dayRepo = $dayRepo;
    }

    public function updateSlot(array $parameters): Slot
    {
        /** @var Slot $slot */
        $slot = $this->slotRepo->find($parameters['id']);

        if(isset($parameters['title'])) {
            $slot->setTitle($parameters['title']);
        }
        if(isset($parameters['limit'])) {
            $slot->setLimit($parameters['limit']);
        }
        if(isset($parameters['order'])) {
            $slot->setOrder($parameters['order']);
        }
        if(isset($parameters['enabled'])) {
            $slot->setDisabled(!$parameters['enabled']);
        }

        $this->em->persist($slot);
        $this->em->flush();

        return $slot;
    }

    public function updateState(Slot $slot, string $state): void
    {
        if (!in_array($state, ['0', '1'], true)) {
            throw new InvalidArgumentException('invalid slot state');
        }

        $slot->setDisabled('1' === $state);

        $this->em->persist($slot);
        $this->em->flush();
    }

    public function delete(Slot $slot): void
    {
        $slot->setDeleted(true);

        $this->em->persist($slot);
        $this->em->flush();
    }

    public function getSlotStatusForDay(DateTime $datetime): array
    {
        $status = $this->getSlotsStatusOn($datetime);
        $slots = [];

        foreach ($status as $name => $count) {
            $slot = $this->slotRepo->findOneBy(['slug' => $name]);
            $slots[] = [
                'id' => $slot->getId(),
                'title' => $slot->getTitle(),
                'count' => $count,
                'limit' => $slot->getLimit(),
                'slug' => $slot->getSlug(),
            ];
        }

        return $slots;
    }

    /**
     * @psalm-return array<string, int> Key-value pair of slot-slug and related allocation count
     */
    public function getSlotsStatusOn(DateTime $date): array
    {
        $startDate = (clone $date)->setTime(0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);
        $openMealDaysSlots = $this->getOpenMealDaysWithSlots($startDate, $endDate);
        $slotCountProvider = $this->getBookedSlotCountProvider($startDate, $endDate);

        $slotsStatus = [];

        foreach ($openMealDaysSlots as $item) {
            $slotSlug = $item['slot']->getSlug();
            $allocationCount = $slotCountProvider($item['date'], $item['slot']);
            $slotsStatus[$slotSlug] = $allocationCount;
        }

        return $slotsStatus;
    }

    /**
     * Get slots for each open (not expired) meal day up to a given date in the future.
     *
     * @return array An array of arrays, each containing date and related slot.
     *               Top level array items are indexed by a composite key composed of date and slot-ID, i.e. Y-m-d-slot_id.
     *
     * @psalm-return array<string, array{date: DateTime, slot: Slot}>
     */
    private function getOpenMealDaysWithSlots(DateTime $stateDate, DateTime $endDate): array
    {
        $daysWithSlots = [];
        $mealDays = $this->dayRepo->findAllActive($stateDate, $endDate);
        $mealSlots = $this->slotRepo->findBy(['disabled' => 0, 'deleted' => 0], ['order' => 'ASC']);

        foreach ($mealDays as $day) {
            foreach ($mealSlots as $slot) {
                $date = $day->getDateTime();
                $k = $date->format('Y-m-d') . '-' . $slot->getId();
                $daysWithSlots[$k] = [
                    'date' => $date,
                    'slot' => $slot,
                ];
            }
        }

        return $daysWithSlots;
    }

    /**
     * Get status of booked slots from $startDate to $endDate.
     *
     * The return results are indexed by a composite key comprised of concatenated date and slot-ID.
     */
    private function getBookedSlotCountProvider(DateTime $startDate, DateTime $endDate): callable
    {
        $slotBookingStatus = [];
        $bookedSlotsStatus = $this->participantRepo->getCountBySlots($startDate, $endDate);

        foreach ($bookedSlotsStatus as $bss) {
            $k = $bss['date']->format('Y-m-d-') . $bss['slot'];
            $slotBookingStatus[$k] = $bss['count'];
        }

        return static function (DateTime $date, Slot $slot) use ($slotBookingStatus): int {
            $k = $date->format('Y-m-d-') . $slot->getId();

            return $slotBookingStatus[$k] ?? 0;
        };
    }

    public function getAllSlots(): array
    {
        return $this->slotRepo->findBy(['deleted' => 0]);
    }
}
