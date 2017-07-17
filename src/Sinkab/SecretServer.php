<?php
namespace Sinkab;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SecretServer extends \Slim\App {
    /**
     * @var string $hash Unique hash to identify the secrets
     */
    private $hash;
    /**
     * @var string $secretText The secret itself
     */
    private $secretText;
    /**
     * @var DateTime $createdAt The date and time of the creation
     */
    private $createdAt;
    /**
     * @var DateTime $expiresAt The secret cannot be reached after this time
     */
    private $expiresAt;
    /**
     * @var int $remainingViews How many times the secret can be viewed
     */
    private $remainingViews;
    /**
     * @var string $requestType Possible values: xml and json
     */
    private $requestType;
    /**
     * @var int $status HTTP status code
     */
    private $status;
    /**
     * @var string $status_msg HTTP status message
     */
    private $status_msg;
    /**
     * @var string $content_type Response ContentType
     */
    private $content_type;
    /**
     * @var string $resp_body Response body content
     */
    private $resp_body;

    /**
     * @var array $ERROR_CODES Store the handled status codes and status texts
     */
    public static $ERROR_CODES;
    /**
     * @var Database $DB mysqli databse object
     */
    public static $DB;
    /**
     * @var store request type, supported types are xml and json
     */

    /**
     * SecretServer constructor.
     * @param array|\Sinkab $config Use of the databse settings
     */
    function __construct($config) {
        SecretServer::$DB = new Database($config['db']['host'],$config['db']['user'],$config['db']['password'],$config['db']['database_name']);
        SecretServer::$ERROR_CODES = [
            404 => 'Secret not found',
            405 => 'Invalid input'
        ];
        $this->content_type = 'text/html; charset=UTF-8';
        $this->status = 200;
        $this->status_msg = 'OK';
        $this->resp_body = '';
        parent::__construct();
        $this->init();
    }

    /**
     * Initialize databse and routes
     */
    protected function init() {

        SecretServer::$DB->query("SET NAMES utf8");
        SecretServer::$DB->query("SET CHARACTER SET utf8");
        $sql = "CREATE TABLE IF NOT EXISTS secret(
                id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
                hash VARCHAR (8),
                secretText VARCHAR (500),
                createdAt DATETIME DEFAULT NOW(),
                expiresAt INT,
                remainingViews int,
                deleted TINYINT(1) DEFAULT 0, CONSTRAINT hash UNIQUE INDEX uhash(hash))";
        SecretServer::$DB->real_query($sql);

        $this->get('/v1/xml/secret/{hash}', function (Request $request, Response $response){
            $this->requestType = 'xml';
            //$response->withoutHeader();
            \Sinkab\SecretServer::getSecret($request,$this);
            $new_resp = $response->withStatus($this->status,$this->status_msg)->withHeader('ContentType',$this->content_type);
            $new_resp->getBody()->write($this->resp_body);
            return $new_resp;
        });
        $this->get('/v1/json/secret/{hash}', function (Request $request, Response $response){
            $this->requestType = 'json';
            \Sinkab\SecretServer::getSecret($request,$this);
            $new_resp = $response->withStatus($this->status,$this->status_msg)->withHeader('ContentType',$this->content_type);
            $new_resp->getBody()->write($this->resp_body);
            return $new_resp;
        });
        $this->get('/v1/json/secret', function (Request $request, Response $response){
            $uri = $request->getUri();
            $scheme = $uri->getScheme();
            $host = $uri->getHost();
            $action = $scheme.'://'.$host.'/v1/json/secret';

            $form = <<<FORM
            <h2>Add a secret</h2>
<form id="secret-form" method="post" action="{$action}" enctype="application/x-www-form-urlencoded">
<ul style="list-style-type: none; width: 400px; box-shadow: 2px 2px 6px rgba(0,0,0,.4),-1px -1px 1px rgba(0,0,0,.2); padding: 20px;">
<li style="padding-bottom: 10px;"><label for="secret" style="width: 180px; display: inline-block;">Secret</label><input type="text" id="secret" name="secret" required></li>
<li style="padding-bottom: 10px;"><label for="expireAfterViews" style="width: 180px; display: inline-block;">expireAfterViews</label><input type="text" id="expireAfterViews" name="expireAfterViews" required></li>
<li style="padding-bottom: 10px;"><label for="expireAfter" style="width: 180px; display: inline-block;">expireAfter</label><input type="text" id="expireAfter" name="expireAfter" required></li>
<li style="text-align: right; padding-right: 50px"><button type="submit" style="margin-top: 20px;cursor:pointer;">Submit</button></li>
</ul>
</form>
FORM;

            $response->getBody()->write($form);
            return $response;
        });
        $this->get('/v1/xml/secret', function (Request $request, Response $response){
            $uri = $request->getUri();
            $scheme = $uri->getScheme();
            $host = $uri->getHost();
            $action = $scheme.'://'.$host.'/v1/json/secret';

            $form = <<<FORM
            <h2>Add a secret</h2>
<form id="secret-form" method="post" action="{$action}" enctype="application/x-www-form-urlencoded">
<ul style="list-style-type: none; width: 400px; box-shadow: 2px 2px 6px rgba(0,0,0,.4),-1px -1px 1px rgba(0,0,0,.2); padding: 20px;">
<li style="padding-bottom: 10px;"><label for="secret" style="width: 180px; display: inline-block;">Secret</label><input type="text" id="secret" name="secret" required></li>
<li style="padding-bottom: 10px;"><label for="expireAfterViews" style="width: 180px; display: inline-block;">expireAfterViews</label><input type="text" id="expireAfterViews" name="expireAfterViews" required></li>
<li style="padding-bottom: 10px;"><label for="expireAfter" style="width: 180px; display: inline-block;">expireAfter</label><input type="text" id="expireAfter" name="expireAfter" required></li>
<li style="text-align: right; padding-right: 50px"><button type="submit" style="margin-top: 20px;cursor:pointer;">Submit</button></li>
</ul>
</form>
FORM;

            $response->getBody()->write($form);
            return $response;
        });
        $this->post('/v1/xml/secret', function (Request $request, Response $response){
            $this->requestType = 'xml';
            \Sinkab\SecretServer::addSecret($request,$this);
            $new_resp = $response->withStatus($this->status,$this->status_msg)->withHeader('ContentType',$this->content_type);
            $new_resp->getBody()->write($this->resp_body);
            return $new_resp;
        });
        $this->post('/v1/json/secret', function (Request $request, Response $response){
            $this->requestType = 'json';
            \Sinkab\SecretServer::addSecret($request,$this);
            $new_resp = $response->withStatus($this->status,$this->status_msg)->withHeader('ContentType',$this->content_type);
            $new_resp->getBody()->write($this->resp_body);
            return $new_resp;
        });
        $this->run();
    }

    /**
     * Get sicret for hash
     * @param Request $request Current request instance
     * @param SecretServer $secret Current instance
     */
    public static function getSecret(&$request,&$secret) {
        try {

            $esc_hash = \Sinkab\SecretServer::$DB->real_escape_string($request->getAttribute('hash'));

            $row = \Sinkab\SecretServer::$DB->one_row("SELECT *, ((TO_SECONDS(NOW())-TO_SECONDS(createdAt)) DIV 60) as tdiff FROM secret WHERE hash='{$esc_hash}' AND NOT deleted");

            if(!@$row['id']) {
                throw new \Exception(SecretServer::$ERROR_CODES[404],404);
            }

            if($row['expiresAt'] && $row['tdiff'] > $row['expiresAt']) {
                \Sinkab\SecretServer::$DB->real_query("UPDATE secret SET deleted=1 WHERE id={$row['id']}");
                throw new \Exception(SecretServer::$ERROR_CODES[404],404);
            }
            $row['remainingViews']--;
            \Sinkab\SecretServer::$DB->real_query("UPDATE secret SET remainingViews={$row['remainingViews']} WHERE id={$row['id']}");
            if($row['remainingViews'] === 0) {
                \Sinkab\SecretServer::$DB->real_query("UPDATE secret SET deleted=1 WHERE id={$row['id']}");
                throw new \Exception(SecretServer::$ERROR_CODES[404],404);
            }
            $secret->hash = $row['hash'];
            $secret->secretText = $row['secretText'];
            $secret->createdAt = $row['createdAt'];
            $secret->expiresAt = $row['expiresAt'];
            $secret->remainingViews = $row['remainingViews'];

            \Sinkab\SecretServer::response($secret);
        } catch (\Exception $e) {
            \Sinkab\SecretServer::response($secret, $e);
        }
    }

    /**
     * Add an secret from form's data
     * required fields:
     *      secret - The secret itself
     *      expireAfterViews - How many times the secret can be viewed
     *      expireAfter - The secret cannot be reached after this time in minutes
     * @param Request $request Current request instance
     * @param SecretServer $secret Current instance
     */
    public static function addSecret(&$request, &$secret) {
        try {
            $data = $request->getParsedBody();

            $secret->secretText = @$data['secret'];
            if(!@$data['secret']) {
                throw new \Exception(SecretServer::$ERROR_CODES[405],405);
            }
            $secret->remainingViews = @$data['expireAfterViews'];
            $expireAfterViews = intval(@$data['expireAfterViews']?$data['expireAfterViews']:0);
            if(!$expireAfterViews || $expireAfterViews < 0) {
                throw new \Exception(SecretServer::$ERROR_CODES[405],405);
            }
            $secret->remainingViews =$expireAfterViews;
            if(@$data['expireAfter'] == '') {
                throw new \Exception(SecretServer::$ERROR_CODES[405],405);
            }
            $secret->expiresAt = intval(@$data['expireAfter']?$data['expireAfter']:0);

            $st = \Sinkab\SecretServer::$DB->real_escape_string($secret->secretText);

            \Sinkab\SecretServer::$DB->real_query("INSERT INTO secret(secretText,remainingViews,expiresAt) VALUES('{$st}',$secret->remainingViews,$secret->expiresAt)");
            $id = \Sinkab\SecretServer::$DB->insert_id;
            if(!$id) {
                throw new \Exception(SecretServer::$ERROR_CODES[405],405);
            }
            $secret->hash = hash('crc32',$id.'__'.$secret->secretText);

            $secret->createdAt = \Sinkab\SecretServer::$DB->one_field("SELECT createdAt FROM secret WHERE id=$id");
            \Sinkab\SecretServer::$DB->real_query("UPDATE secret SET hash='{$secret->hash}' WHERE id=$id");
            \Sinkab\SecretServer::response($secret);
        } catch (\Exception $e) {
            \Sinkab\SecretServer::response($secret, $e);
        }

    }

    /**
     * Generate an response depend of request type
     * @param SecretServer $secret Current instance
     * @param \Exception|null $e
     */
    public static function response(&$secret, \Exception $e=null) {

        $secret->content_type = 'text/html; charset=UTF-8';
        $secret->status = is_null($e)?200:$e->getCode();
        $secret->status_msg = 'OK';

        switch ($secret->requestType) {
            case 'xml': {
                $secret->content_type = 'application/xml; charset=utf-8';
                $resp_body = '<?xml version="1.0" encoding="utf-8"?>';
                $resp_body .= '<secret>';
                if($secret->status !== 200) {
                    $secret->status_msg = $e->getMessage();
                    $resp_body .= '<error>'.$e->getMessage().'</error>';
                    $resp_body .= '</secret>';break;
                }
                $resp_body .= '<hash>'.$secret->hash.'</hash>';
                $resp_body .= '<secretText>'.$secret->secretText.'</secretText>';
                $resp_body .= '<createdAt>'.$secret->createdAt.'</createdAt>';
                $resp_body .= '<expiresAt>'.$secret->expiresAt.'</expiresAt>';
                $resp_body .= '<remainingViews>'.$secret->remainingViews.'</remainingViews>';
                $resp_body .= '</secret>';
            };break;
            case 'json': {
                $secret->content_type = 'application/json; charset=utf-8';
                if($secret->status !== 200) {
                    $secret->status_msg = $e->getMessage();
                    $resp_body = json_encode([
                        'error' => $e->getMessage()]);break;
                }
                $resp_body = json_encode([
                    'hash' => $secret->hash,
                    'secretText' => $secret->secretText,
                    'secretText' => $secret->secretText,
                    'createdAt' => $secret->createdAt,
                    'expiresAt' => $secret->expiresAt,
                    'remainingViews' => $secret->remainingViews,
                    ]);
            };break;
        }
        $secret->resp_body = $resp_body;
    }
}

?>