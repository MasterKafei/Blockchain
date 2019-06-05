<?php

namespace AppBundle\Service\Util;


class RequestBusiness
{
    public function getResponse($uri, $postParameters = [])
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POSTFIELDS     => http_build_query($postParameters),
        );

        $ch = curl_init($uri);
        curl_setopt_array($ch, $options);
        $content  = curl_exec($ch);

        curl_close($ch);

        return $content;
    }
}
