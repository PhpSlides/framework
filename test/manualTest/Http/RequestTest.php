<?php

include_once __DIR__ . '/../../autoload.php';

use PhpSlides\Http\Request as HttpRequest;

class Request extends HttpRequest
{
   function testContentType ()
   {
      print_r($this->contentType());
   }

   function testRequestTime ()
   {
      print_r($this->requestTime());
   }

   function testContentLength ()
   {
      print_r($this->contentLength());
   }

   function testIsHttps ()
   {
      var_dump($this->isHttps());
   }

   function testCsrf ()
   {
      print_r($this->csrf());
   }

   function testProtocol ()
   {
      print_r($this->protocol());
   }

   function testIp ()
   {
      print_r($this->ip());
   }

   function testUrlParam ()
   {
      print_r($this->urlParam());
   }

   function testUrlQuery ()
   {
      print_r($this->urlQuery());
   }

   function testHeader ()
   {
      print_r($this->header());
   }

   function testAuth ()
   {
      print_r($this->auth());
   }

   function testApiKey ()
   {
      print_r($this->apiKey());
   }

   function testBody ()
   {
      print_r($this->body());
   }

   function testGet ()
   {
      print_r($this->get());
   }

   function testPost ()
   {
      print_r($this->post());
   }

   function testRequest ()
   {
      print_r($this->request());
   }

   function testFiles ()
   {
      print_r($this->files());
   }

   function testCookie ()
   {
      print_r($this->cookie());
   }

   function testSession ()
   {
      print_r($this->session());
   }

   function testMethod ()
   {
      print_r($this->method());
   }

   function testUri ()
   {
      print_r($this->uri());
   }

   function testUrl ()
   {
      print_r($this->url());
   }

   function testUserAgent ()
   {
      print_r($this->userAgent());
   }

   function testIsAjax ()
   {
      var_dump($this->isAjax());
   }

   function testReferrer ()
   {
      print_r($this->referrer());
   }

   function testServer ()
   {
      print_r($this->server());
   }

   function testIsMethod ()
   {
      var_dump($this->isMethod('GET'));
   }

   function testAll ()
   {
      print_r($this->all());
   }
}

$req = new Request();

// Uncomment the tests you want to run
// $req->testContentType();
// $req->testRequestTime();
// $req->testContentLength();
// $req->testIsHttps();
// $req->testCsrf();
// $req->testProtocol();
// $req->testIp();
// $req->testUrlParam();
// $req->testUrlQuery();
// $req->testHeader();
// $req->testAuth();
// $req->testApiKey();
// $req->testBody();
// $req->testGet();
// $req->testPost();
// $req->testRequest();
// $req->testFiles();
// $req->testCookie();
// $req->testSession();
// $req->testMethod();
// $req->testUri();
// $req->testUrl();
// $req->testUserAgent();
// $req->testIsAjax();
// $req->testReferrer();
// $req->testServer();
// $req->testIsMethod();
// $req->testAll();