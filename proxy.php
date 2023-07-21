<?php


class Proxy
{
    public function getContent($proxied_url)
    {
// Configuration parameters
var_dump($proxied_url);
        $proxied_host = parse_url($proxied_url)['host'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// HTTP messages consist of a request line such as 'GET https://example.com/asdf HTTP/1.1'…
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
        curl_setopt($ch, CURLOPT_URL, $proxied_url . $_SERVER['REQUEST_URI']);

// … a set of header fields…
        $request_headers = $this->getallheaders();
        $request_headers['Host'] = $proxied_host;
        $request_headers['X-Forwarded-Host'] = $_SERVER['SERVER_NAME'];
        $request_headers = iterator_to_array($this->reformat($request_headers));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

// … and a message body.
        $request_body = file_get_contents('php://input');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);

// Retrieve response headers in the same request as the body
// Taken from https://stackoverflow.com/a/41135574/3144403
        $response_headers = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use ($response_headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $response_headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );

        $response_body = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

// Set the appropriate response status code &amp; headers
        http_response_code($response_code);
        foreach ($response_headers as $name => $values)
            foreach ($values as $value)
                header("$name: $value", false);
				
		var_dump($response_code);
		var_dump($response_body);
    }

    private function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    private function reformat($headers)
    {
        foreach ($headers as $name => $value) {
            yield "$name: $value";
        }
    }
}