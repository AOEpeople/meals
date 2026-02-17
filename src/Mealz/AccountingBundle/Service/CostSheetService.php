<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

final class CostSheetService
{
    /**
     * @psalm-return array{?array}
     */
    public function mergeDoubleUserTransactions(array $users)
    {
        // ToDo: is mergeDoubleUserTransactions nescessary at all?
        $mergedUsers = [];

        foreach ($users as $userId => &$user) {
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
        foreach ($mergedUsers as $username => $userData) {
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
