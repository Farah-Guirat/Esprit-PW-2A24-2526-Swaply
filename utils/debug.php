<?php

class Debug {

    public static function log($title, $data = null) {
        $logFile = __DIR__ . "/debug.log";

        $time = date("Y-m-d H:i:s");

        $message = "\n========================\n";
        $message .= "[$time] $title\n";

        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                $message .= print_r($data, true);
            } else {
                $message .= $data;
            }
        }

        $message .= "\n========================\n";

        file_put_contents($logFile, $message, FILE_APPEND);
    }

}