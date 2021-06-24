<?php
//================================================================
//  Development Tools
//  Author: Will Allen
//  eMail:  will@willallen.dev
//  website:    willallen.dev
//================================================================

//================================================================
//  Posting to discord webhook for debug purposes
//================================================================
function debugToDiscord( $message ) {

    //  Get time in a format that is useful for filename
    $timestamp = date("Ymd_Gis", strtotime("now"));

    // Check if the input is a string, number or object
    if (!is_string($message)){
        if (is_numeric($message)){
            $message = strval($message);
        } else if (is_object($message)){
            ob_start();
            var_dump($message);
            $message = ob_get_clean();
        } else {
            $message = "Input is " . gettype($message) . ". Nothing was output.";
        }
    }

    //  cURL settings for all output to Discord
    $ch = curl_init( "" );  //  place your discord webhook url here
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

    //  If the output is not very long, send to Discord
    if ( mb_strlen($message) < 400 ) {

        $json_data = json_encode([
            // Message
            "content" => $message,
            
            // Username
            "username" => "Debug Bot",
    
            // Avatar URL.
            // Uncoment to replace image set in webhook
            "avatar_url" => "https://flaticons.net/icon.php?slug_category=application&slug_icon=debug",
    
            // Text-to-speech
            "tts" => false,
    
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

    } else {    //  If output is very long, write to file locally
        
        //  fopen attempts to write to template directory rather than location of this file.
        $dfTitle = "wp-content/debugLogs/Debug_" . $timestamp . ".txt";
        $debugFile = fopen($dfTitle, "w") or die("Unable to open file!");
        fwrite($debugFile, $message);
        fclose($debugFile);

        $json_data = json_encode([
            // Message
            "content" => "Output Too Large for Debug Bot. Logged to " . get_template_directory() . $dfTitle,
            
            // Username
            "username" => "Debug Bot",
    
            // Avatar URL.
            // Uncoment to replace image set in webhook
            "avatar_url" => "https://flaticons.net/icon.php?slug_category=application&slug_icon=debug",
    
            // Text-to-speech
            "tts" => false,
    
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }
    
    //  update postfields with the encoded data from the proper case
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);

    
    $response = curl_exec( $ch );
    // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
    echo $response;

    curl_close( $ch );

    return;
}