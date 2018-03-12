<?php


namespace Draw\HttpTester\Request;

use function GuzzleHttp\Psr7\parse_request;

class BodyParser
{
    /**
     * The temp directory where the files will be save.
     *
     * @var string
     */
    private $tempDirectory;

    /**
     * @var bool
     */
    private $autoRemoveFileOnShutdown = true;

    public function __construct($tempDirectory = null, $autoRemoveFileOnShutdown = true)
    {
        $this->tempDirectory = $tempDirectory ?: sys_get_temp_dir();
        $this->autoRemoveFileOnShutdown = $autoRemoveFileOnShutdown;
    }

    public function parse($body, $contentType)
    {
        $results = [
            'post' => [],
            'files' => []
        ];

        if(strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            parse_str(urldecode($body), $post);
            $results['post'] = $post;
            return $results;
        }

        preg_match('/boundary=(.*)$/', $contentType, $matches);

        if(!count($matches) || !strlen($matches[1])) {
            return $results;
        }

        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $blocks = preg_split("/-+$boundary/", $body);
        array_pop($blocks);

        foreach($blocks as $content) {
            if (empty($content)) {
                continue;
            }

            $request = parse_request("GET /fake-start-line HTTP/1.1" . $content);

            $contentDisposition = $this->parseContentDisposition($request->getHeaderLine('Content-Disposition'));

            // Remove the last character of the body since it's the line break before the boundary
            $content = substr($request->getBody()->getContents(), 0, -1);
            switch ($contentDisposition['type']) {
                case 'form-data':
                    $contentType = $request->getHeaderLine('Content-Type');
                    $inputName = $contentDisposition['data']['name'];
                    switch ($contentType) {
                        case '':
                            $results['post'][] = $inputName . '=' . urlencode($content);
                            break;
                        default:
                            $fileEntry = $this->createFileEntry(
                                $inputName,
                                $content,
                                $contentType,
                                $contentDisposition['data']['filename']
                            );
                            $results['files'] = array_merge_recursive(
                                $results['files'],
                                $fileEntry
                            );
                    }
                    break;
            }
        }

        parse_str(implode('&', $results['post']), $results['post']);

        return $results;
    }

    private function parseContentDisposition($contentDisposition)
    {
        $parts = explode(';', $contentDisposition);
        $parts = array_map('trim', $parts);
        $type = array_shift($parts);
        $data = [];
        if($parts) {
            foreach($parts as $keyValue) {
                list($key, $value) = explode('=', $keyValue);
                $data[$key] = trim($value, '"');
            }
        }

        return compact('type', 'data');
    }

    private function createFileEntry($inputName, $content, $type, $name)
    {
        $result = [];
        $tmp_name = tempnam($this->tempDirectory, 'draw_');
        $size = file_put_contents($tmp_name, $content);
        $error = $size === false ? UPLOAD_ERR_CANT_WRITE : UPLOAD_ERR_OK;

        if(!$error && $this->autoRemoveFileOnShutdown) {
            register_shutdown_function('unlink', $tmp_name);
        }

        parse_str($inputName . '=temp', $structure);
        $data = compact('name', 'type', 'tmp_name', 'error', 'size');
        foreach($data as $key => $value) {
            $tempArray = $structure;
            array_walk_recursive($tempArray, function(&$temp) use ($value) {
                if($temp != 'temp') {
                    return;
                }
                $temp = $value;
            });
            $result[key($tempArray)][$key] = reset($tempArray);
        }

        return $result;
    }
}