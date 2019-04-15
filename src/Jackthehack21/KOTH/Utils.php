<?php

class Utils{

    public function getClosest($search, $arr) : int{
        //https://stackoverflow.com/a/5464961 - Thanks :)
        $closest = null;
        foreach ($arr as $item) {
            if ($closest === null || abs($search - $closest) > abs($item - $search)) {
                $closest = $item;
            }
        }
        return $closest;
    }
}