<?php

class DateParser
{
    public static function parseDateForEloquent($date)
    {
        if($date == 'today'){
            $today = date('Y-m-d');
            return [$today . ' 00:00:00', $today . ' 23:59:59'];
        }

        $timestamp = trim($date);

        if (strpos($timestamp, '->') !== false) {
            $dates = explode('->', $timestamp);

            foreach($dates as $index => &$date){
                $date = trim($date);
                if( strpos($date, ' ') !== false) {
                    $dateTime = explode(' ', $date);
                    
                    $hours = self::parseHours($dateTime[0]);
                    $date = self::parseDate($dateTime[1]);
                    
                    $date = $date . ' ' . $hours;
                }else{
                    if(self::checkDateValidity($date)){
                        if($index == 0) $hours = ' 00:00:00';
                        if($index == 1) $hours = ' 23:59:59';
                        
                        $date = date(self::parseDate($date) . $hours);
                    }else{
                        return 'Invalid date formating';
                    }
                }
            }
            
            return $dates;
        }elseif( strpos($timestamp, ' ') !== false) {
            $date = explode(' ', $timestamp);

            if(self::checkDateValidity($date[1]) && self::checkHoursValidity($date[0])){
                $hours = self::parseHours($date[0]);
                $date = self::parseDate($date[1]);

                return $date . ' ' . $hours;
            }else{
                $hours = self::fillHoursWithBlanks($date[0]);
                $date = self::fillDateWithBlanks($date[1]);

                return $date . ' ' . $hours;
            }
        }else{
            if(self::checkHoursValidity($timestamp)){
                $hours = self::parseHours($timestamp);

                return '%' . $hours;
            }

            if(self::checkDateValidity($timestamp)){
                $date = self::parseDate($timestamp);

                return $date . '%';
            }

            if(!self::checkHoursValidity($timestamp) && !self::checkDateValidity($timestamp)){
                return '%' . self::parseDate($timestamp) . '%';
            }
        }
    }

    public static function fillDateWithBlanks($date)
    {
        // $parts = explode('/', $date);
        $date = implode('-', array_reverse(explode('/', $date)));
        

        while(strlen($date) < 10){
            $date = '_' . $date; 
        }

        return $date;
    }

    public static function fillHoursWithBlanks($hours)
    {
        return self::parseHours($hours);
    }

    public static function parseHours($hours)
    {
        while(strlen($hours) < 5){
            $hours .= '_'; 
        }

        $hours .= ':__';

        return $hours;
    }

    public static function parseDate($date)
    {
        $parts = explode('/', $date);

        if(strlen($parts[0]) == 1) {
            $parts[0] = '0' . $parts[0];
        }

        if(strlen($parts[1]) == 1) {
            $parts[1] = '0' . $parts[1];
        }

        if(key_exists(2, $parts)){
            while(strlen($parts[2]) < 4){
                $parts[2] = '_' . $parts[2];
            }
        }

        $date = implode('-', array_reverse($parts));

        return $date;
    }

    public static function checkDateValidity($date)
    {
        return substr_count($date,"/") == 2 && substr_count($date,":") == 0;
    }

    public static function checkHoursValidity($hours)
    {
        return substr_count($hours,":") == 1 && substr_count($hours,"/") == 0;
    }
}