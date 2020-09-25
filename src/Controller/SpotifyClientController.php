<?php
/**
 * @file
 * Contains \Drupal\spotify_api\Controller\SpotifyClientController.
 */
namespace Drupal\spotify_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Spotify controller.
 */
class SpotifyClientController extends ControllerBase {
  protected $client;

  public function __construct() {
    $this->client = \Drupal::httpClient();
  }

  /**
   * Authorization.
   * @return mixed|void
   */
  private function authorization() {
    $config = \Drupal::config('spotify_api.settings');
    $client_id = $config->get('client_id');
    $client_secret = $config->get('client_secret');
    // @todo cache token for an hour, which is the expiration time of the token.
    try {
      $authorization = $this->client->request('POST', 'https://accounts.spotify.com/api/token', [
        'form_params' => [
          'grant_type' => 'client_credentials',
        ],
        'headers' => [
          'Authorization' => 'Basic '.base64_encode($client_id.':'.$client_secret),
          'Content-type' => 'application/x-www-form-urlencoded',
        ]
      ]);
      $body = $authorization->getBody();
      // The json returned by Spotify doesn't validate, so let's grab the token manually.
      if (preg_match('/"access_token":"(.*?)","token_type/', $body, $match) == 1) {
        $response = $match[1];
      }
      else {
        $response = NULL;
      }
      return $response;
    }
    catch (GuzzleException $e) {
      return \Drupal::logger('spotify_api')->error($e);
    }
  }

  /**
   * Get artists.
   * @return \Psr\Http\Message\StreamInterface|void
   */
  public function getArtists() {
    $config = \Drupal::config('spotify_api.settings');
    $auth = $this->authorization();
    // Spotify API doesn't allow to get random artists, so let's get that from releases endpoint.
    try {
      $request = $this->client->request('GET', 'https://api.spotify.com/v1/browse/new-releases?limit=15', [
        'headers' => [
          'Authorization' => 'Bearer ' . $auth
        ],
      ]);

      $releases = $request->getBody();
      $decoded = json_decode($releases);
      if (isset($decoded->albums->items)) {
        $artists_array = [];
        $max = $config->get('number_artists');
        $items = $decoded->albums->items;
        $i = 1;
        foreach ($items as $item) {
          foreach ($item->artists as $artist) {
            if ($i <= $max) {
              array_push($artists_array, $artist);
              $i++;
            }
            if ($i > $max) break;
          }
        }
      }
    }
    catch (GuzzleException $e) {
      return \Drupal::logger('spotify_api')->error($e);
    }
    return $artists_array;
  }

  /**
   * Get artist by id.
   * @param $id
   */
  public function getArtist($id){

    $auth = $this->authorization();

    try {
      $requestArtist = $this->client->request('GET', 'https://api.spotify.com/v1/artists/' . $id, [
        'headers' => [
          'Authorization' => 'Bearer ' . $auth
        ]
      ]);

      $responseArtist = json_decode($requestArtist->getBody());
    }
    catch (GuzzleException $e) {
      return \Drupal::logger('spotify_api')->error($e);
    }

    $build['artist_page'] = [
      '#theme' => 'artist_page',
      '#artist' => $responseArtist,
    ];
    return $build;

  }
}
