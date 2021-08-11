<?php

namespace App\Mealz\RestBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityManager;
use App\Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Mealz\MealBundle\Entity\Participant;

class ParticipantController extends BaseController
{
    public function todayAction()
    {
        $this->checkUser();

        $participants = $this->getParticipantRepository()->getParticipantsOnDays(new DateTime(), new DateTime());

        $data = array();
        foreach ($participants as $participant) {
            /** @var Participant $participant */
            $meal = $participant->getMeal();
            $dish = $meal->getDish();
            $category = $dish->getCategory();
            array_push($data, array(
                'id' => $participant->getId(),
                'cost_absorbed' => $participant->isCostAbsorbed(),
                'confirmed' => $participant->isConfirmed(),
                'meal' => array(
                    'id' => $meal->getId(),
                    'price' => $meal->getPrice(),
                    'date_time' => $meal->getDateTime(),
                    'dish' => array(
                        'id' => $dish->getId(),
                        'enabled' => $dish->isEnabled(),
                        'slug' => $dish->getSlug(),
                        'title_en' => $dish->getTitleEn(),
                        'title_de' => $dish->getTitleDe(),
                        'price' => $dish->getPrice(),
                        'category' => array(
                            'id' => $category !== null ? $category->getId() : null,
                            'slug' => $category !== null ? $category->getSlug() : null,
                            'title_en' => $category !== null ? $category->getTitleEn() : null,
                            'title_de' => $category !== null ? $category->getTitleDe() : null,
                        )
                    )
                )
            ));
        }

        return array(
            'participants' => $data
        );
    }

    public function deleteAction($participantId)
    {
        $participant = $this->getParticipant($participantId);
        
        if (!$this->getDoorman()->isUserAllowedToLeave($participant->getMeal())) {
            throw new HttpException(403, "It's not allowed for participant to leave.");
        }
        $this->getManager()->remove($participant);
        $this->getManager()->flush();

        return array(
            'participantsCount' => $participant->getMeal()->getParticipants()->count()
        );
    }

    public function confirmAction($participantId)
    {
        $participant = $this->getParticipant($participantId);

        try {
            $participant->setConfirmed(true);

            $manager = $this->getDoctrine()->getManager();
            $manager->transactional(function (EntityManager $manager) use ($participant) {
                $manager->persist($participant);
                $manager->flush();
            });
        } catch (ParticipantNotUniqueException $e) {
            throw new HttpException(422, "Participant must be unique.");
        }

        return array(
            'confirmed' => true,
            'participantId' => $participant->getId()
        );
    }

    /**
     * @param number $participantId
     * @return Participant
     */
    private function getParticipant($participantId)
    {
        $this->checkUser();

        if (null == $participantId) {
            throw new HttpException(400, "Participant's id is missing.");
        }

        /** @var Participant $participant */
        $participant = $this->getParticipantRepository()->find($participantId);

        if (!$participant) {
            throw new HttpException(404, "There is no such participant.");
        }
        if ($this->getUser()->getProfile() !== $participant->getProfile() && !$this->getDoorman()->isKitchenStaff()) {
            throw new HttpException(403, "It's not possible to to request change for other participant.");
        }
        return $participant;
    }
}
