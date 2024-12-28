<?php

use Shuchkin\SimpleXLSXGen;

class PdfAttachmentRunner {
    function shellExec(string $filepath) {
        $return = shell_exec(sprintf('pdfdetach -list %s', $filepath));
        return $return;
    }

    function getAttachments(string $filepath): array {
        // run pdf against pdfdetach to see if contains attachments
        $possibleAttachments = $this->shellExec($filepath);
        // does the returned string from pdfdetach contain an error telling us we have 0 attachments? 
        if($possibleAttachments === '0 embedded files') {
            $possibleAttachments = null;
        }
        // if we have a valid attachments string, explode it by return/new line and loop over each row
        $attachments = $possibleAttachments ? array_values(array_filter(array_map(function($val) {
            // explode each row so we can get the filename
            $parts = explode(':', $val);
            // does the filename contain .xml?
            if(isset($parts[1]) && str_contains($parts[1], '.xml')) {
                // it does, so return the filename
                return trim($parts[1]);
            }
            // return nothing, filter out later
            return;
            // this line is where we explode the main string by newline if multiple attachments were found
        }, explode("\r\n", $possibleAttachments)), function($val) {
            return !!$val;
        })) : [];
    
        return $attachments;
    }

    function saveAttachment(string $filepath, string $xmlFilename) {
        shell_exec(sprintf('pdfdetach -savefile %s %s', $xmlFilename, $filepath));
    }

    function attachmentExists(string $xmlFilename) {
        return file_exists($xmlFilename);
    }

    function getUblPayloadFromXml(string $xmlFilename) {
        // load the XML file content
        $xml = @file_get_contents($xmlFilename);

        if($xml === false) {
            return [];
        }
        // setup our UBL parser
        $parser = new UBLParser();
        // set the loaded XML content into the parser
        $parser->set($xml);

        return $parser->get();
    }

    function generateSpreadsheet(string $filename, array $data): string {
        $header = [
            ['Invoice Reference Number', 'Issue Date', 'Issue Time'],
            [
                $data['ID'], 
                $data['IssueDate'], 
                $data['IssueTime']
            ],
        ];
    
        $items = [
            ['Name', 'Qty', 'Unit Price', 'Amount (SAR)', 'VAT Amount (SAR)'],
            ...array_map(function($item) {
                return [
                    $item['Item']['Name'],
                    $item['InvoicedQuantity'],
                    $item['Price']['PriceAmount'],
                    $item['LineExtensionAmount'],
                    $item['TaxTotal']['TaxAmount']
                ];
            }, $data['InvoiceLines'])
        ];
    
        $xlsxFilename = sprintf('%s.xlsx', $filename);
    
        $xlsx = new SimpleXLSXGen();
        $xlsx->addSheet($header, 'Header');
        $xlsx->addSheet($items, 'Items');
        $xlsx->saveAs($xlsxFilename);

        return $xlsxFilename;
    }
}