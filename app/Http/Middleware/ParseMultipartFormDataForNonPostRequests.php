<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParseMultipartFormDataForNonPostRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldParse($request)) {
            return $next($request);
        }

        [$inputs, $files, $temporaryFiles] = $this->parseMultipartRequest($request);

        if ($inputs !== []) {
            $request->request->add($inputs);
        }

        if ($files !== []) {
            $request->files->add($files);
        }

        $response = $next($request);

        foreach ($temporaryFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return $response;
    }

    private function shouldParse(Request $request): bool
    {
        if (!in_array($request->getMethod(), ['PUT', 'PATCH'], true)) {
            return false;
        }

        $contentType = $request->headers->get('Content-Type', '');
        if (!str_starts_with($contentType, 'multipart/form-data')) {
            return false;
        }

        return $request->request->count() === 0 && $request->files->count() === 0;
    }

    private function parseMultipartRequest(Request $request): array
    {
        $contentType = $request->headers->get('Content-Type', '');
        preg_match('/boundary=(.*)$/', $contentType, $matches);
        $boundary = isset($matches[1]) ? trim($matches[1], '"') : null;

        if (!$boundary) {
            return [[], [], []];
        }

        $body = $request->getContent();
        if ($body === '') {
            return [[], [], []];
        }

        $inputs = [];
        $files = [];
        $temporaryFiles = [];
        $parts = explode('--' . $boundary, $body);

        foreach ($parts as $part) {
            $part = ltrim($part, "\r\n");
            $part = rtrim($part, "\r\n");

            if ($part === '' || $part === '--') {
                continue;
            }

            [$rawHeaders, $content] = array_pad(preg_split("/\r\n\r\n/", $part, 2), 2, null);
            if ($rawHeaders === null || $content === null) {
                continue;
            }

            $headers = $this->parseHeaders($rawHeaders);
            $disposition = $headers['content-disposition'] ?? null;
            if (!$disposition) {
                continue;
            }

            preg_match('/name="([^"]+)"/', $disposition, $nameMatches);
            $fieldName = $nameMatches[1] ?? null;
            if (!$fieldName) {
                continue;
            }

            $content = preg_replace("/\r\n$/", '', $content) ?? $content;

            preg_match('/filename="([^"]*)"/', $disposition, $fileMatches);
            $fileName = $fileMatches[1] ?? null;

            if ($fileName !== null && $fileName !== '') {
                $temporaryPath = tempnam(sys_get_temp_dir(), 'nextskill_');
                if ($temporaryPath === false) {
                    continue;
                }

                file_put_contents($temporaryPath, $content);
                $temporaryFiles[] = $temporaryPath;

                $files[$fieldName] = new UploadedFile(
                    $temporaryPath,
                    $fileName,
                    $headers['content-type'] ?? 'application/octet-stream',
                    null,
                    true
                );

                continue;
            }

            $inputs[$fieldName] = $content;
        }

        return [$inputs, $files, $temporaryFiles];
    }

    private function parseHeaders(string $rawHeaders): array
    {
        $headers = [];

        foreach (preg_split("/\r\n/", $rawHeaders) as $headerLine) {
            if (!$headerLine || !str_contains($headerLine, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $headerLine, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }

        return $headers;
    }
}
