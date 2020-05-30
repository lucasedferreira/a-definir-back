<?php

class BaseParser
{
    public static function parse($fromTo, $values)
    {
        foreach($fromTo as $from => $to){
            if(key_exists($from, $values)){
                $values[$to] = $values[$from];
                unset($values[$from]);
            }
        }

        return $values;
    }
}