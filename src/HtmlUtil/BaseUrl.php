<?php
declare(strict_types=1);



namespace Zalt\HtmlUtil;

class BaseUrl 
{
    /**
     * BaseUrl
     *
     * @var string
     */
    protected $_baseUrl = '';
    
    public function __toString(): string
    {
        return $this->_baseUrl;
    }

    public function getBaseUrl(): string
    {
        return $this->_baseUrl;
    }

    public function setBaseUrl(string $base): void
    {
        $this->_baseUrl = rtrim($base, '/\\');
    }
}
