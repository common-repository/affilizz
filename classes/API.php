<?php
namespace Affilizz;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Exception;

class API {
	/**
	 * Verifies that the API Key is allowed to access the remote API.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $api_key String The API Key.
	 * @return boolean Did we have access ?
	 */
	public function verify_key( string $api_key ) {
		try {
			$api_key_exists_response = wp_remote_get(
				AFFILIZZ_USER_API_BASE_URL . 'users/me',
				array(
					'headers' => array(
						'ApiKey' => $api_key
					)
				)
			);

			return ! is_wp_error( $api_key_exists_response );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Gets all the organizations to which the user has been granted access.
	 * The available accesses are retrieved from the user/me endpoint, then loop through consecutive calls to the expand endpoint.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $api_key String The API Key.
	 * @return Array A list of available organizations.
	 */
	public function get_entities( string $api_key ) {
		$organizations = array();

		try {
			// Get the accessible organizations
			$entities_query_response = wp_remote_get(
				AFFILIZZ_USER_API_BASE_URL . 'users/me',
				array(
					'headers' => array(
						'ApiKey' => $api_key
					)
				)
			);

			// If we get an error, bail
			if ( is_wp_error( $entities_query_response ) ) return false;

			// Get the entities
			$contents = wp_remote_retrieve_body( $entities_query_response );
			$data = json_decode( $contents );

			if ( $data && $data->authorizations ) {
				foreach ( $data->authorizations as $access ) {
					$organizations[$access->organizationId] = '';
				}
			}
		} catch ( Exception $e ) {
			return false;
		}

		// Expand the organizations, one by one
		if ( ! empty( $organizations ) ) {
			foreach ( $organizations as $id => &$name ) {
				try {
					$organizations_query_url = add_query_arg(
						array(
							'organizationId' => $id
						),
						AFFILIZZ_USER_API_BASE_URL . 'expands'
					);
					$organizations_query_response = wp_remote_get(
						$organizations_query_url,
						array(
							'headers' => array(
								'ApiKey' => $api_key
							)
						)
					);

					// If we have an error, bail
					if ( is_wp_error( $organizations_query_response ) ) {
						return;
					}

					// Get the entities
					$contents = wp_remote_retrieve_body( $organizations_query_response );
					$data = json_decode( $contents );
					$name = $data->name;
				} catch ( Exception $e ) {
				}
			}
		}

		return $organizations;
	}

	/**
	 * Gets all the medias for the selected organization.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $api_key String The API Key.
	 * @param $organization The organization from which to get the medias from.
	 * @return Array A list of available medias for the organization.
	 */
	public function get_media( $api_key, string $organization ) {
		$media = array();

		try {
			$organizations_query_url = add_query_arg(
				array(
					'organizationId' => $organization
				),
				AFFILIZZ_USER_API_BASE_URL . 'expands'
			);

			$organizations_query_response = wp_remote_get(
				$organizations_query_url,
				array(
					'headers' => array(
						'ApiKey' => $api_key
					)
				)
			);

			// If we have an error, bail
			if ( is_wp_error( $organizations_query_response ) ) {
				return;
			}

			// Get the entities
			$contents = wp_remote_retrieve_body( $organizations_query_response );
			$data = json_decode( $contents );

			if ( $data && $data->media ) {
				foreach ( $data->media as $m ) {
					$media[$m->id] = $m->name;
				}
			}
		} catch ( Exception $e ) {
			return false;
		}

		return $media;
	}

	/**
	 * Gets all the channels for the selected organization and media.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $organization The organization from which to get the medias from.
	 * @param $media The media from which to get the channels from.
	 * @return Array A list of available channels for the media and the organization.
	 */
	public function get_channels( string $organization, string $media = null ) {
		$channels = array();

		try {
			$filter = $media ? array( 'mediaId' => $media ) : array( 'organizationId' => $organization );

			$media_query_url = add_query_arg( $filter, AFFILIZZ_USER_API_BASE_URL . 'expands' );
			$media_query_response = wp_remote_get(
				$media_query_url,
				array(
					'headers' => array(
						'ApiKey' => get_option( 'affilizz_api_key' )
					)
				)
			);

			// If we have an error, bail
			if ( is_wp_error( $media_query_response ) ) {
				return;
			}

			// Get the entities
			$contents = wp_remote_retrieve_body( $media_query_response );
			$data = json_decode( $contents );

			if ( $data && $data->media ) {
				foreach ( $data->media as $m ) {
					foreach ( $m->channels as $channel ) {
						$channels[$channel->id] = $channel->name;
					}
				}
			}
		} catch ( Exception $e ) {
			return false;
		}

		return $channels;
	}

	/**
	 * Gets a list of the available publications for the current configuration.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $force_refresh Boolean Should we force a refresh of the publications ?
	 * @return Array A list of available publications.
	 */
	public function get_publications( $force_refresh = false, $search = '' ) {
		$publications        = array();

		try {
			if ( ! $force_refresh && $search == '' ) {
				// $publications = get_transient( \Affilizz\Core::get_publications_transient_key() );
				// if ( ! empty( $publications ) ) return $publications;
			}

			$publications_query_url = add_query_arg(
				array(
					'organizationId' => get_option( 'affilizz_organization' ),
					'mediaId' => get_option( 'affilizz_media' ),
					'time' => time()
				),
				AFFILIZZ_CMS_API_BASE_URL . 'publications'
			);

			// Allow search if we have a search term
			if ( ! empty( $search ) ) {
				$publications_query_url = add_query_arg(
					array(
						'query' => $search
					),
					$publications_query_url
				);
			}

			$publications_query_response = wp_remote_get(
				$publications_query_url,
				array(
					'headers' => array(
						'ApiKey' => get_option( 'affilizz_api_key' )
					)
				)
			);

			// If we have an error, bail
			if ( is_wp_error( $publications_query_response ) ) {
				return;
			}

			// Get the entities
			$contents = wp_remote_retrieve_body( $publications_query_response );
			$api_publications = json_decode( $contents );
			$recent_publications = \Affilizz\Util\Publications::recent();

			if ( $api_publications ) {
				foreach ( $api_publications as $publication ) {
					if ( isset( $publication->name ) ) {
						$publications[$publication->id] = array(
							'name' => esc_attr( $publication->name ),
							'recent' => in_array( $publication->id, $recent_publications ),
						);
					}
				}
			}

			uasort( $publications, function( $a, $b ) {
				if ( $a['recent'] == $b['recent'] ) {
					return 0;
				}
				if ( $a['recent'] ) {
					return -1;
				}
				return 1;
			} );
		} catch ( Exception $e ) {
			return false;
		}

		if ( ! $force_refresh && $search == '' ) {
			// set_transient( \Affilizz\Core::get_publications_transient_key(), $publications, 15 * MINUTE_IN_SECONDS );
		}

		return $publications;
	}

	/**
	 * Retrieves an affiliate content based on its publication_id and its channel_id.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $publication_id The publication ID for this publication.
	 * @param $channel_id The channel ID for this publication.
	 * @return Object A qualified publication.
	 */
	public function get_publication( string $publication_id, string $channel_id ) {
		try {
			$publication_query_url = add_query_arg(
				array(
					'channelId' => $channel_id
				),
				AFFILIZZ_CMS_API_BASE_URL . 'publications/' . $publication_id
			);
			$publication_query_response = wp_remote_get( $publication_query_url, array(
				'headers' => array(
					'ApiKey' => get_option( 'affilizz_api_key' ),
					'WPPluginVersion' => AFFILIZZ_VERSION
				)
			) );

			// If we have an error, bail
			if ( is_wp_error( $publication_query_response ) ) {
				return;
			}

			// Bail if we do not have the channel anymore
			if ( ! empty( $publication_query_response[ 'status' ] ) && $publication_query_response[ 'status' ] == 'NOT_FOUND' ) {
				throw new \Affilizz\Exception\Missing\Channel( sprintf( __( 'Error : %s', 'affilizz' ), $publication_query_response[ 'message' ] ) );
			}

			$contents = wp_remote_retrieve_body( $publication_query_response );
			$publication_metadata = json_decode( $contents );
			return $publication_metadata;
		} catch ( \Affilizz\Exception\Missing\Channel $e ) {
			return [
				'error'   => true,
				'message' => $e->getMessage()
			];
		} catch ( Exception $e ) {
			return false;
		}
	}
}