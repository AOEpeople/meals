<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

use Collator;

final class CostSheetService
{
    /**
     * @psalm-return array<array-key, array>
     */
    public function sortUsersByName(array $users, string $locale): array
    {
        $collator = new Collator($locale);
        $collator->setStrength(Collator::PRIMARY);

        uasort($users, static function (array $left, array $right) use ($collator): int {
            return $collator->compare(trim($left['name']), trim($right['name']))
                ?: $collator->compare(trim($left['firstName']), trim($right['firstName']))
                ?: $left['id'] <=> $right['id'];
        });

        return $users;
    }

    /**
     * @psalm-return array<array-key, array>
     */
    public function mergeDoubleUserTransactions(array $users): array
    {
        // ToDo: is mergeDoubleUserTransactions nescessary at all?
        $mergedUsers = [];

        foreach ($users as &$user) {
            $trimmedUsername = preg_replace('/@([a-zA-Z]+.)+[a-zA-Z]+$/', '', $user['username']);
            if (!isset($mergedUsers[$trimmedUsername])) {
                $mergedUsers[$user['username']] = $user;
            } else {
                $mergedUsers[$user['username']] = $user;
                $mergedUsers[$user['username']]['costs'] = $this->mergeArrayByKey($user['costs'], $mergedUsers[$trimmedUsername]['costs']);
                unset($mergedUsers[$trimmedUsername]);
            }
        }

        // Fix keys
        $fixedUsers = [];
        foreach ($mergedUsers as $userData) {
            $fixedUsers[$userData['id']] = $userData;
        }

        return $fixedUsers;
    }

    public function mergeArrayByKey(array $arrOne, array $arrTwo): array
    {
        $outArr = [];

        foreach ($arrOne as $key => $val) {
            $outArr[$key] = round((float) $val + (float) $arrTwo[$key], 2);
        }

        return $outArr;
    }
}
