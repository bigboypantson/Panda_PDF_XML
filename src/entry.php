<?php

include '../vendor/autoload.php';

//set our content type to JSON
header('Content-Type: application/json');

$pdfRunner = new PdfAttachmentRunner();

$pdfWithXml = $_GET['pdfWithXml'] ?? 1;
// check if we should use the PDF with or without XML attachment to mimic a PDF being uploaded
$filepath = $pdfWithXml ? __DIR__.'/data/pdf-with-xml.pdf' : __DIR__.'/data/pdf-without-xml.pdf';
// get all (if any) attachments for the input file
$attachments = $pdfRunner->getAttachments($filepath);

// we must have some valid attachments for this PDF
if(count($attachments)) {
    // get the first attachment filename
    $xmlFilename = $attachments[0];
    // save the first XML attachment to a location, this would be probably be tmp folder or S3, you could just store the PDF for future reference.
    $pdfRunner->saveAttachment($filepath, $xmlFilename);
    // if the file doesn't exist in our location return an error
    if(!$pdfRunner->attachmentExists($xmlFilename)) {
        http_response_code(400);
        echo json_encode([
            'message' => 'Invoice contained invalid XML attachments that we were unable to save.'
        ]);

        return;
    }

    // get the UBL data from the XML
    $data = $pdfRunner->getUblPayloadFromXml($xmlFilename);
    // split the filename so we can use it later for saving the spreadsheet
    $filename = explode('.', $xmlFilename);

    $xlsxFilename = $pdfRunner->generateSpreadsheet($filename[0], $data);
    // set 201 response code as we've created a spreadsheet
    http_response_code(201);
    // return a JSON payload
    echo json_encode([
        'message' => 'Invoice contained XML attachment, spreadsheet generated and data returned.',
        'xml' => $xmlFilename,
        'xlsx' => $xlsxFilename,
        'data' => $data
    ]);
} else {
    // set 400 response code
    http_response_code(400);
    // return a JSON payload
    echo json_encode([
        'message' => 'Invoice contained no XML attachments.'
    ]);
}