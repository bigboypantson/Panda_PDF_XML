### Building and running your application

When you're ready, start your application by running:
`docker compose up --build`.

Your application will be available at http://localhost.

### Testing the endpoint with PDF containing XML attachment

Using postman, import this cURL request to do a GET request to localhost root with the following query:

`curl --location 'http://localhost/?pdfWithXml=1'`

### Testing the endpoint with PDF with no XML attachment

Using postman, import this cURL request to do a GET request to localhost root with the following query:

`curl --location 'http://localhost/?pdfWithXml=0'`

### Testing with PHPUnit

I have added a few tests which you can run by attaching to the container and running the following command:

`phpunit`