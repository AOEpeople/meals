<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

class CostSheetService
{
    public function mergeDoubleUserTransactions(array $users): array
    {
        $mergedUsers = [];

        foreach ($users as $username => &$user) {
            $trimmedUsername = preg_replace('/@([a-zA-Z]+.)+[a-zA-Z]+$/', '', $username);
            if (!isset($mergedUsers[$trimmedUsername])) {
                $mergedUsers[$trimmedUsername] = $user;
            } else {
                $mergedUsers[$trimmedUsername]['costs'] = $this->mergeArrayByKey($user['costs'], $mergedUsers[$trimmedUsername]['costs']);
            }
        }

        return $mergedUsers;
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
