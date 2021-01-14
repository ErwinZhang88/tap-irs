<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \Firebase\JWT\JWT;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use App\Models\Token;
use Carbon\Carbon;

class Master extends ServiceProvider
{
	protected $http;

    protected $endpoint;

    protected $headers = [];

    protected $body;

    protected $query = [];

    public function __construct(  )
    {
        $this->http = new Client();

        $this->headers = $this->headers();
    }

    public function headers()
    {
        return $this->headers = [];
    }

    public function uri()
    {
        $uri = 'http://irsdev.tap-agri.com/';
        return $uri. $this->endpoint;
    }
    
    public function setEndpoint( $endpoint = "" )
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function setHeaders( array $headers )
    {
        $this->headers = array_merge( $this->headers(), $headers );

        return $this;
    }

    public function setBody( $body )
    {
        $this->body = $body;

        return $this;
    }

    public function setQuery( array $query )
    {
        $this->query = http_build_query( $query );

        return $this;
    }

    public function setMethod( $method )
    {
        switch ( $method ) {
            case "multipart":
                $methods = [ "method" => "POST", "more_content" => [ [ "name" => "_method", "contents" => "put" ] ] ];
                break;
            default:
                $methods = [ "method" => "PUT" ];
                break;
        }

        return $methods;
    }

    public function get()
    {
        try {
			
            $request  = $this->http->request(
                "GET"
                , $this->uri()
                , [
                    "headers" => $this->headers
                    , "query" => $this->query
                ]
            );
			

            $response = json_decode( $request->getBody(), true );

        } catch ( ClientException $e ) {
            $body = $e->getResponse()->getBody();
            $rsp = json_decode( $body->getContents(), true );
			$response = [
				'code' => '422',
				'message'=>'json',
				'contents'=>$rsp
			];
        } catch ( ServerException $e ) {
            // abort( 500 );
			$body = $e->getResponse()->getBody();
			$rsp = $body->getContents();
			$response = [
				'code' => '422',
				'message'=>'html',
				'contents'=>$rsp
			];
        }

        return $response;
    }

    public function post( $type = "json" )
    {
        try {
            $request  = $this->http->request(
                "POST"
                , $this->uri()
                , [
                    "headers" => $this->headers
                    , "query" => $this->query
                    , $type => $this->body
                ]
            );
            $response = json_decode( $request->getBody(), true );

        } catch ( ClientException $e ) {
            $body = $e->getResponse()->getBody();
            $response = json_decode( $body->getContents(), true );

        } catch ( ServerException $e ) {
            abort( 500 );

        }

        return $response;
    }

    public function put( $type = "json" )
    {
        $method = $this->setMethod( $type );
        $body = array_key_exists( "more_content", $method ) ? array_merge( $this->body, $method["more_content"] ) : $this->body;

        try {
            $request  = $this->http->request(
                $method["method"]
                , $this->uri()
                , [
                    "headers" => $this->headers
                    , "query" => $this->query
                    , $type => $body
                ]
            );
            $response = json_decode( $request->getBody(), true );

        } catch ( ClientException $e ) {
            $body = $e->getResponse()->getBody();
            $response = json_decode( $body->getContents(), true );

        } catch ( ServerException $e ) {
            abort( 500 );

        }

        return $response;
    }

    public function deleted()
    {
        try {
            $request  = $this->http->request(
                "DELETE"
                , $this->uri()
                , [
                    "headers" => $this->headers
                    , "query" => $this->query
                    , "json" => $this->body
                ]
            );
            $response = json_decode( $request->getBody(), true );

        } catch ( ClientException $e ) {
            $body = $e->getResponse()->getBody();
            $response = json_decode( $body->getContents(), true );

        } catch ( ServerException $e ) {
            abort( 500 );

        }

        return $response;
    }
	
    
}
