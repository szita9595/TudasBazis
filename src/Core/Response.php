<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP válasz objektum
 */
class Response
{
    private string $content = '';
    private int $statusCode = 200;
    private array $headers = [];

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function redirect(string $url, int $statusCode = 302): self
    {
        $this->statusCode = $statusCode;
        $this->headers['Location'] = $url;
        return $this;
    }

    public function json(array $data, int $statusCode = 200): self
    {
        $this->statusCode = $statusCode;
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this;
    }

    public function send(): void
    {
        // Státusz kód beállítása
        http_response_code($this->statusCode);
        
        // Headerek küldése
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        // Tartalom küldése
        echo $this->content;
    }

    public static function html(string $content): self
    {
        $response = new self($content);
        $response->setHeader('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    public static function notFound(string $message = 'Az oldal nem található'): self
    {
        return new self($message, 404);
    }

    public static function forbidden(string $message = 'Hozzáférés megtagadva'): self
    {
        return new self($message, 403);
    }
}
