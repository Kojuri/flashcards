<?php

namespace App\services\json;

use \Psr\Http\Message\ResponseInterface as Response;

class Writer extends Pagination
{

    /**
     * @param $tab
     * @param $link
     * @param $extension
     * @return array
     */
    static public function ressource($tab, $link, $extension){
        $head = ["type" => "ressource","meta" => ["local" => "fr-FR"]];
        $result = array($head,$link,$extension => [$tab]);
        return $result;
    }

    /**
     * @param $tab
     * @return array
     */
    static public function collection($tab){
        $head = ["type" => "collection","meta" => ["count" => self::$total,"size" => self::$size,"date" => self::$date]];
        $result = [$head,$tab];
        return $result;
    }

    /**
     * @param Response $resp
     * @param $int
     * @param $data
     * @return Response|static
     */
    static public function json_output(Response $resp, $int, $data = ''){

        $resp = $resp->withHeader('Content-Type','application/json')->withStatus($int);
        $resp->getBody()->write(json_encode($data));
        return $resp;
    }
}