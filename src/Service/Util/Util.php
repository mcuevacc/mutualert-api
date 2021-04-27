<?php

namespace App\Service\Util;

class Util
{
    public function isExpiredDate($date, $duration){
        $now = new \DateTime();
        $datediff = $now->getTimestamp() - $date->getTimestamp();;
        if($duration>$datediff){
            return true;
        }
        return false;
    }
}