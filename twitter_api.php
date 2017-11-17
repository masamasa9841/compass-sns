<?php
class TwitterApi {
	const TWEET_URL        = 'https://api.twitter.com/1.1/statuses/update.json';
	const MEDIA_UPLOAD_URL = 'https://upload.twitter.com/1.1/media/upload.json';
	private $_consumer_key;
	private $_consumer_secret;
	private $_access_token;
	private $_access_token_secret;
	public function __construct( $consumer_key, $consumer_secret, $access_token, $access_token_secret ) {
		$this->_consumer_key        = $consumer_key;
		$this->_consumer_secret     = $consumer_secret;
		$this->_access_token        = $access_token;
		$this->_access_token_secret = $access_token_secret;
	}

	private function _create_signature( $url, $params ) {
		$signature_key = rawurlencode( $this->_consumer_secret ) . '&' . rawurlencode( $this->_access_token_secret );
		$oauth_params  = array(
			'oauth_token'            => $this->_access_token,
			'oauth_consumer_key'     => $this->_consumer_key,
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_nonce'            => md5( uniqid( rand(), true ) ), // ランダムな文字列であればOK
			'oauth_version'          => '1.0',
		);
		// 署名の作成
		$merge_params = array_merge( $params, $oauth_params );
		ksort( $merge_params ); // パラメータ名でソートされていないとダメらしい
		$req_params                      = http_build_query( $merge_params );  // key=val&key=val...
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
	 * 画像をアップロードします.
	 *
	 * @param $img_url
	 * @return string
	 */
	public function post_media( $img_url ) {
		// バイナリまたはBase64エンコードした画像をアップロードする
		// バイナリの場合、"media"パラメータを指定
		// Base64の場合、"media_data"パラメータを指定
		$img_bin = file_get_contents($img_url);
		//  $img_b64 = base64_encode($img_bin);
		// バウンダリーの定義
		$boundary = '-------------------------------------------'.md5(mt_rand());
		// リクエストボディの作成
		$req_body = '';
		$req_body .= '--'.$boundary."\r\n";
		$req_body .= 'Content-Disposition: form-data; name="media";'; // name="media_data" if base64
		$req_body .= "\r\n";
		$req_body .= "\r\n".$img_bin."\r\n";
		$req_body .= '--'.$boundary.'--'."\r\n\r\n";
		$params = $this->_create_signature(TwitterApi::MEDIA_UPLOAD_URL, array());
		// 送信データの作成
		$options = array('http' => array(
			'method' => 'POST',
			'header' => array(
				'Authorization: OAuth '.http_build_query($params, '', ','), // Authorization: OAuth key=val,key=val...
				'Content-Type: multipart/form-data; boundary='.$boundary
			),
			'content' => $req_body
		));
		$options = stream_context_create($options);
		// 送信
		$json = file_get_contents(TwitterApi::MEDIA_UPLOAD_URL, false, $options);
		return $json;
	}
	/**
	 * ツイートを投稿します。$media_idを指定すると画像付きで投稿します
	 *
	 * @param string $status ツイート本文.
	 * @param $media_id
	 * @return string
	 */
	public function tweet($status, $media_id = null) {
		$post_params = array(
			'status' => $status
		);
		if ($media_id != null) {
			$post_params['media_ids'] = $media_id;
		}
		$params = $this->_create_signature(TwitterApi::TWEET_URL, $post_params);
		// 送信データの作成
		$options = array('http' => array(
			'method' => 'POST',
			'header' => array(
				'Authorization: OAuth '.http_build_query($params, '', ',')  // Authorization: OAuth key=val,key=val...
			),
			'content' => http_build_query($post_params) // key=val&key=val...
		));
		$options = stream_context_create($options);
		// 送信
		$json = file_get_contents(TwitterApi::TWEET_URL, false, $options);
		return $json;
	}
	/**
	 * メディアアップロードのレスポンスからmedia_idを取得します
	 *
	 * @param $media_response
	 * @return string
	 */
	public function get_media_id($media_response)
	{
		$res = json_decode($media_response, true);
		if (isset($res['media_id_string'])) {
			return $res['media_id_string'];
		}
		return null;
	}
	}
