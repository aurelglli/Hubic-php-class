<?php
class hubic
  {
    const ACCOUNT_PASSWORD = '';
    const KEY_ID = '';
    const KEY_SECRET = '';
    const CALLBACK_URL = '';
    function __construct()
      {
        switch ($_GET[state])
        {
            case "authorized":
                $getTokens = self::getTokens();
                if (!$getTokens->access_token || round(microtime(true) * 1000 + $getTokens->expires_in) < round(microtime(true) * 1000))
                  {
                    $getTokens = self::refreshTokens($getTokens->refresh_token);
                  }
                /*Test a call*/
                print_r(self::listObj($getTokens->access_token));
                break;
            default:
                header('HTTP/1.0 301 Redirect');
                header("Location: " . self::getAuthorizeLink());
        }
      }
    public function getAuthorizeLink()
      {
        $url = 'https://api.hubic.com/oauth/auth/?';
        $url .= 'client_id=' . self::KEY_ID;
        $url .= '&redirect_uri=' . urlencode(self::CALLBACK_URL);
        $url .= '&response_type=code';
        $url .= '&scope=usage.r,account.r,getAllLinks.r,credentials.r,activate.w,links.drw';
        $url .= '&state=authorized';
        return $url;
      }
    public function getTokens()
      {
        $code       = $_GET[code];
        $o          = array(
            'Authorization: Basic ' . base64_encode(self::KEY_ID . ':' . self::KEY_SECRET)
        );
        $postfields = array(
            'code' => $code,
            'redirect_uri' => self::CALLBACK_URL,
            'grant_type' => 'authorization_code'
        );
        return self::call('https://api.hubic.com/oauth/token/', $o, $postfields);
      }
    public function refreshTokens($refresh_token)
      {
        $o          = array(
            'Authorization: Basic ' . base64_encode(self::KEY_ID . ':' . self::KEY_SECRET)
        );
        $postfields = array(
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token'
        );
        return self::call('https://api.hubic.com/oauth/token/', $o, $postfields);
      }
    public function call($url, $o, $postfields)
      {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_HTTPHEADER, $o);
        curl_setopt($c, CURLOPT_VERBOSE, 0);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        if ($postfields)
          {
            curl_setopt($c, CURLOPT_POST, true);
            curl_setopt($c, CURLOPT_POSTFIELDS, $postfields);
          }
        return json_decode(curl_exec($c));
      }
    public function usage($access_token)
      {
        $o = array(
            'Authorization: Bearer ' . $access_token
        );
        return self::call('https://api.hubic.com/1.0/account/usage/', $o);
      }
    public function listObj($access_token)
      {
        $o = array(
            'Authorization: Bearer ' . $access_token
        );
        return self::call('https://api.hubic.com/1.0/account/', $o);
      }
  }
new hubic();
?>
