<?php

namespace Drupal\spotify_api\Plugin\Block;

use Drupal\spotify_api\Controller\SpotifyClientController;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "spotify_api_block",
 *   admin_label = @Translation("Spotify API Block"),
 * )
 */
class SpotifyApiBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $artists_array = [];
    $artist_controller= new SpotifyClientController();
    $artists = $artist_controller->getArtists();
    foreach ($artists as $artist) {
      $artists_array[] = [
        'name' => $artist->name,
        'link' => Link::fromTextAndUrl(t('See information about artist'), Url::fromUri('internal:/artist/' . $artist->id, []))->toString()
      ];
    }
    $build['block_spotify_api'] = [
      '#theme' => 'block_spotify_api',
      '#artists_array' => $artists_array,
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
}
