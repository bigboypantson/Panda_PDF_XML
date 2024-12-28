<?php

use PHPUnit\Framework\TestCase;

class PdfAttachmentRunnerTest extends TestCase {
    private $pdfAttachmentRunner;

    protected function setUp(): void {
        $this->pdfAttachmentRunner = new PdfAttachmentRunner();
    }

    public function testGetAttachmentsMethodReturnsEmptyArrayWhenGivenPdfWithNoXmlAttachments() {
        $filepath = 'tests/data/pdf-without-xml.pdf';
            
        $attachments = $this->pdfAttachmentRunner->getAttachments($filepath);
        $this->assertEmpty($attachments);
    }

    public function testGetAttachmentsMethodReturnsArrayWhenGivenPdfWithXmlAttachments() {
        $filepath = 'tests/data/pdf-with-xml.pdf';

        $attachments = $this->pdfAttachmentRunner->getAttachments($filepath);

        $this->assertNotEmpty($attachments);
        $this->assertEquals($attachments[0], '300187978810003_20241110T133818_12600003996.xml');
    }

    public function testUblPayloadFromXmlMethodReturnsArrayWhenGivenXmlFile() {
        $ublData = $this->pdfAttachmentRunner->getUblPayloadFromXml('tests/data/test.xml');

        $this->assertArrayHasKey('ID', $ublData);
        $this->assertArrayHasKey('IssueDate', $ublData);
        $this->assertArrayHasKey('IssueTime', $ublData);
    }

    public function testUblPayloadFromXmlMethodReturnsEmptyArrayWhenGivenNonExistingXmlFile() {
        $ublData = $this->pdfAttachmentRunner->getUblPayloadFromXml('tests/data/test-doesnt-exist.xml');

        $this->assertEmpty($ublData);
    }

    protected function tearDown(): void
    {
        $this->pdfAttachmentRunner = null;
    }
}