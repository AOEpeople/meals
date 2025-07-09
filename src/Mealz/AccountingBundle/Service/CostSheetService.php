<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

class CostSheetService
{
    /**
     * @psalm-return array{?array}
     */
    public function mergeDoubleUserTransactions(array $users)
    {
        $mergedUsers = [];

        foreach ($users as $username => &$user) {
            $trimmedUsername = preg_replace('/@([a-zA-Z]+.)+[a-zA-Z]+$/', '', $username);
            if (!isset($mergedUsers[$trimmedUsername])) {
                $mergedUsers[$username] = $user;
            } else {
                $mergedUsers[$username] = $user;
                $mergedUsers[$username]['costs'] = $this->mergeArrayByKey($user['costs'], $mergedUsers[$trimmedUsername]['costs']);
                unset($mergedUsers[$trimmedUsername]);
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
