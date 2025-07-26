<?php

/**
 * Shikimori API class
 * --
 * Shikimori API, searching and getting information by ID or name
 * --
 * Author: UberCHEL
 * version: v1.0.0
 */

namespace uberchel;

class ShikimoriAPI {

 /**
  * var: shikimori api url
  */
  private const HOME_URL = 'https://shikimori.one/';

  private const API_URL  = 'https://shikimori.one/api/';

  private const IMG_URL  = 'https://shikimori.one/system/animes/';

 /**
  * var: array of functions
  */
  private $functions = [];

 /**
  * var: random user agents for emulation
  */
  private $userAgents = [
  	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0',
  	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',
	  'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
  ];
  

 /**
  * Function: Constructor Class
  * 
  * @return void
  */
  public function __construct() {

	// Initialize function getAnime
    $this->functions = array(
      'getAnime' => function($options) {
      	  try {
      	  	  if (empty($options['id'])) {
      	  	  	  return '[]';
      	  	  }
      	  
      	  	  return $this->callMethod('animes/' . $options['id']);
      	  } catch (\Exception $e) {
      	  	  error_log("Error in getAnime: " . $e->getMessage());
      	  	  return '[]';
      	  }
      },

      // Initialize function getPoster
      'getPoster' => function($options) {
      	  try {
      	  	  if (empty($options['id'])) {
      	  	  	  return '[]';
      	  	  }

              $sizes = [
                'ss' => 'x48',
                'sm' => 'x96', 
                'md' => 'preview', 
                'lg' => 'original'
              ];

      	  	  $id = intval($options['id']);

              if (!isset($options['size'])) {
                try {
                  $result = array_map(function ($size) use ($id) {
                      return self::IMG_URL . "{$size}/{$id}.jpg?" . time();
                  }, $sizes);

                  return json_encode($result);
                } catch (\Exception $e) {
                  error_log("Error encoding poster URLs: " . $e->getMessage());
                  return '[]';
                }
              } 
              else {
                  $size = array_key_exists($options['size'], $sizes) ? $sizes[$options['size']] : $sizes['sm'];
                  return json_encode(self::IMG_URL . "{$size}/{$id}.jpg?" . time());
              }
      	  } catch (\Exception $e) {
      	  	  error_log("Error in getPoster: " . $e->getMessage());
      	  	  return '[]';
      	  }
      	    
      },
      
      // Initialize function searchAnime
      'searchAnime' => function($options) {
      	  try {
      	  	  if (empty($options['q'])) {
      	  	  	  return '';
      	  	  }

              $ratings = ['g', 'pg', 'pg_13', 'r', 'r_plus', 'rx'];

      	  	  $query = preg_replace_callback('#([^a-zа-я\s]+)#uSi', function ($a) {
      	  	  	  return '';
      	  	  }, $options['q']);
      	  
      	  	  return $this->callMethod('animes/', [
      	  	  	  'search' => str_replace(' ', '+',  htmlentities($query)),
                  'limit' => empty($options['limit']) ? 1 : (int) $options['limit'],
                  'page' => empty($options['page']) ? 1 : (int) $options['page'],
                  'rating' => empty($options['rating']) ? '' : (in_array($options['rating'], $ratings) ? $options['rating'] : '')
      	  	  ]);

      	  } catch (\Exception $e) {
      	  	  error_log("Error in searchAnime: " . $e->getMessage());
      	  	  return '';
      	  }
      },

      // ???
      'getCalendar' => function () {
        return $this->callMethod('calendar', []);
      }
    );
  }
  
 /**
  * Function: Call API method
  * 
  * @property string $method
  * @property array $options
  * @return mixed - Object or Array
  */
  public function call($method, array $options = array()) {
    try {
      if (empty($this->functions[$method])) {
        return (object) [
          "code" => 400,
          "result" => "Method not found"
        ];
      }
      
      $result = $this->functions[$method]($options);

      if ($result == '[]' || $result == '') {
        return (object) [
          "code" => 404,
          "result" => "404 not found"
        ];
      }

      // Try to decode JSON result
      try {

        $result = str_replace(
          ['"/animes', '/system/animes/', '/system/screenshots', '/system/studios'], 
          ['"' . self::HOME_URL . 'animes', self::IMG_URL, self::HOME_URL . 'system/screenshots', self::HOME_URL . 'system/studios'],
        $result);

        $decoded = json_decode($result);

        if ($method == 'getAnime') {
          unset($decoded->rates_scores_stats);
          unset($decoded->description_html);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
          throw new \Exception("JSON decode error: " . json_last_error_msg());
        }
        
        return (object) [
          "code" => 200,
          "result" => $decoded
        ];

      } catch (\Exception $e) {
        error_log("JSON decode error: " . $e->getMessage());
        return (object) [
          "code" => 500,
          "result" => "Invalid JSON response"
        ];
      }
      
    } catch (\Exception $e) {
      error_log("Error in call method: " . $e->getMessage());
      return (object) [
        "code" => 500,
        "result" => "Internal server error"
      ];
    }
  }

 /**
  * Function: call http method GET/POST
  * 
  * @property string $method
  * @property array $options
  * @return string
  */
  public function callMethod($method, array $options = array()) {
  	try {
  	  $action = $method;
  	
      if ($options) {
      	$action .= strval('?' . http_build_query($options));
      }
    
      $timestamp = intval(time() * 1000);
      $rndUAgent = $this->userAgents[rand(0, count($this->userAgents) -1)];
      $randomIP = rand(0, 200).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(0, 255);

      $ch = curl_init(self::API_URL . $action);
      
      if ($ch === false) {
        throw new \Exception("Failed to initialize cURL");
      }
      
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array (
  	      'REMOTE_ADDR: ' . $randomIP,
  	      'HTTP_X_FORWARDED_FOR: ' . $randomIP,
  	      'Accept: application/json',
  	      'Cache-Control: max-stale=0',
  	      'User-Agent: ' . $rndUAgent,
  	      'X-TIMESTAMP: ' . $timestamp,
  	      'Keepalive: close'
      ));
    
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $out = curl_exec($ch);
      
      if ($out === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new \Exception("cURL error: " . $error);
      }
      
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      
      if ($httpCode >= 400) {
        throw new \Exception("HTTP error: " . $httpCode);
      }
      
      return $out;
      
    } catch (\Exception $e) {
      error_log("Error in callMethod: " . $e->getMessage());
      return '[]';
    }
  }
}