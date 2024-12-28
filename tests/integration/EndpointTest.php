<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class EndpointTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        // setup Guzzle HTTP client
        $this->client = new Client([
            'base_uri' => 'http://localhost',
            'timeout'  => 10,
        ]);
    }

    public function testPdfWithXmlAttachmentEndpointReturns200ResponseCodeWithMessageAndUblPayload()
    {
        // send a GET request to the / endpoint
        $response = $this->client->get('/');
        // decode the JSON response body
        $payload = json_decode($response->getBody(), true);
        // assert the HTTP status code is 201
        $this->assertEquals(201, $response->getStatusCode());
        // assert the content type is JSON
        $this->assertTrue(
            $response->hasHeader('Content-Type') &&
            strpos($response->getHeaderLine('Content-Type'), 'application/json') === 0
        );
        // assert data array exists (holds UBL data)
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('ID', $payload['data']);
        $this->assertArrayHasKey('IssueDate', $payload['data']);
        $this->assertArrayHasKey('IssueTime', $payload['data']);
        $this->assertArrayHasKey('InvoiceTypeCode', $payload['data']);
        $this->assertArrayHasKey('DocumentCurrencyCode', $payload['data']);
        $this->assertEquals($payload['data']['DocumentCurrencyCode'], 'SAR');
    }

    public function testPdfWithoutXmlAttachmentEndpointReturns400ResponseCodeWithErrorMessage()
    {
        try {
            // send GET request to / endpoint using the PDF file with no xml attachment
            $this->client->get('/?pdfWithXml=0');
            $this->fail('Expected ClientException was not thrown.');
        } catch (ClientException $e) {
            $response = $e->getResponse();
            // decode the JSON response body
            $payload = json_decode($response->getBody(), true);
            // assert the HTTP status code is 400
            $this->assertEquals(400, $response->getStatusCode());
            // assert the content type is JSON
            $this->assertTrue(
                $response->hasHeader('Content-Type') &&
                strpos($response->getHeaderLine('Content-Type'), 'application/json') === 0
            );
            // assert the JSON payload contains message
            $this->assertArrayHasKey('message', $payload);
        }
        
    }
}
