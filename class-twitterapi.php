<?php
/**
 * Twitter api
 * Thanks to https://qiita.com/hituziando/items/4421ee31a5b74a4ad0a0.
 *
 * @package compass-sns
 * @author Masaya Okawa
 * @license GPL-2.0+
 */

/**
 * Setting Twitter api.
 */
class TwitterApi {
	const TWEET_URL        = 'https://api.twitter.com/1.1/statuses/update.json';
	const MEDIA_UPLOAD_URL = 'https://upload.twitter.com/1.1/media/upload.json';
	const CREDENTIALS      = 'https://api.twitter.com/1.1/account/verify_credentials.json';
	/**
	 * Consumer key.
	 *
	 * @var string.
	 */
	private $_consumer_key;
	/**
	 * Consumer secret.
	 *
	 * @var string.
	 */
	private $_consumer_secret;
	/**
	 * Access token.
	 *
	 * @var string.
	 */
	private $_access_token;
	/**
	 * Access token secret.
	 *
	 * @var string.
	 */
	private $_access_token_secret;

	/**
	 * Constructer.
	 *
	 * @param string $consumer_key Consumer Key.
	 * @param string $consumer_secret Consumer Secret.
	 * @param string $access_token Access token.
	 * @param string $access_token_secret Access token secret.
	 */
	public function __construct( $consumer_key, $consumer_secret, $access_token, $access_token_secret ) {
		$this->_consumer_key        = $consumer_key;
		$this->_consumer_secret     = $consumer_secret;
		$this->_access_token        = $access_token;
		$this->_access_token_secret = $access_token_secret;
	}

	/**
	 * Make Outh.
	 *
	 * @param string $url url.
	 * @param array  $params params.
	 */
	private function create_signature( $url, $params ) {
		$signature_key = rawurlencode( $this->_consumer_secret ) . '&' . rawurlencode( $this->_access_token_secret );
		$oauth_params  = array(
			'oauth_token'            => $this->_access_token,
			'oauth_consumer_key'     => $this->_consumer_key,
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_nonce'            => md5( uniqid( rand(), true ) ),
			'oauth_version'          => '1.0',
		);
		$merge_params  = array_merge( $params, $oauth_params );
		ksort( $merge_params );
		$req_params                      = http_build_query( $merge_params );
		$req_params                      = str_replace( array( '+', '%7E' ), array( '%20', '~' ), $req_params );
		$req_params                      = rawurlencode( $req_params );
		$encoded_req_method              = rawurlencode( 'POST' );
		$encoded_url                     = rawurlencode( $url );
		$signature_data                  = $encoded_req_method . '&' . $encoded_url . '&' . $req_params;
		$hash                            = hash_hmac( 'sha1', $signature_data, $signature_key, true );
		$signature                       = base64_encode( $hash );
		$merge_params['oauth_signature'] = $signature;
		return $merge_params;
	}

	/**
	 * Upload image.
	 *
	 * @param string $img_url image url.
	 * @return string
	 */
	public function post_media( $img_url ) {
		$img_bin   = file_get_contents( $img_url );
		$boundary  = '-------------------------------------------' . md5( mt_rand() );
		$req_body  = '';
		$req_body .= '--' . $boundary . "\r\n";
		$req_body .= 'Content-Disposition: form-data; name="media";';
		$req_body .= "\r\n";
		$req_body .= "\r\n" . $img_bin . "\r\n";
		$req_body .= '--' . $boundary . '--' . "\r\n\r\n";
		$params    = $this->create_signature( TwitterApi::MEDIA_UPLOAD_URL, array() );
		$options   = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => array(
					'Authorization: OAuth ' . http_build_query( $params, '', ',' ),
					'Content-Type: multipart/form-data; boundary=' . $boundary,
				),
				'content' => $req_body,
			),
		);
		$options   = stream_context_create( $options );
		$json      = file_get_contents( TwitterApi::MEDIA_UPLOAD_URL, false, $options );
		return $json;
	}

	/**
	 * Push tweet
	 *
	 * @param string $status tweet sentence.
	 * @param string $media_id media id.
	 * @return string
	 */
	public function tweet( $status, $media_id = null ) {
		$post_params = array(
			'status' => $status,
		);
		if ( null !== $media_id ) {
			$post_params['media_ids'] = $media_id;
		}
		$params  = $this->create_signature( TwitterApi::TWEET_URL, $post_params );
		$options = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => array(
					'Authorization: OAuth ' . http_build_query( $params, '', ',' ),
					'Content-Type: application/x-www-form-urlencoded',
				),
				'content' => http_build_query( $post_params ),
			),
		);
		$options = stream_context_create( $options );
		$json    = file_get_contents( TwitterApi::TWEET_URL, false, $options );
		return $json;
	}

	/**
	 * Get media id.
	 *
	 * @param string $media_response media response.
	 * @return string
	 */
	public function get_media_id( $media_response ) {
		$res = json_decode( $media_response, true );
		if ( isset( $res['media_id_string'] ) ) {
			return $res['media_id_string'];
		}
		return null;
	}

	/**
	 * Get Account.
	 *
	 * @return string
	 */
	public function get_account() {
		$api_key             = $this->_consumer_key;
		$api_secret          = $this->_consumer_secret;
		$access_token        = $this->_access_token;
		$access_token_secret = $this->_access_token_secret;
		$request_url         = TwitterApi::CREDENTIALS;
		$request_method      = 'GET';

		$params_a      = array();
		$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret );

		$params_b = array(
			'oauth_token'            => $access_token,
			'oauth_consumer_key'     => $api_key,
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_nonce'            => microtime(),
			'oauth_version'          => '1.0',
		);

		$params_c = array_merge( $params_a, $params_b );

		ksort( $params_c );

		$request_params = http_build_query( $params_c, '', '&' );

		$request_params = str_replace( array( '+', '%7E' ), array( '%20', '~' ), $request_params );

		$request_params = rawurlencode( $request_params );

		$encoded_request_method = rawurlencode( $request_method );
		$encoded_request_url    = rawurlencode( $request_url );

		$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params;

		$hash = hash_hmac( 'sha1', $signature_data, $signature_key, true );

		$signature = base64_encode( $hash );

		$params_c['oauth_signature'] = $signature;

		$header_params = http_build_query( $params_c, '', ',' );

		$context = array(
			'http' => array(
				'method' => $request_method,
				'header' => array(
					'Authorization: OAuth ' . $header_params,
				),
			),
		);

		if ( $params_a ) {
			$request_url .= '?' . http_build_query( $params_a );
		}

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $request_url );
		curl_setopt( $curl, CURLOPT_HEADER, 1 );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $context['http']['method'] );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $context['http']['header'] );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 5 );
		$res1 = curl_exec( $curl );
		$res2 = curl_getinfo( $curl );
		curl_close( $curl );
		$json = substr( $res1, $res2['header_size'] );
		$obj  = json_decode( $json );
		$html = '';
		if ( property_exists( $obj, 'errors' ) ) {
			$html  = '<h2>Connection Error</h2>';
			$html .= '<p>設定を確認してください。</p>';
			return $html;
		}

		$html .= '<p>name: ' . $obj->name . '</p>';
		$html .= '<img src="' . $obj->profile_image_url_https . '">';
		return $html;
	}
}
