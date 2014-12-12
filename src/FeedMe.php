<?php namespace Feedme;

use Illuminate\Support\Collection;

class FeedMe {

    protected $url;
    protected $format;
    protected $source;
    protected $articleElement = null;

    protected $string;
    protected $xml;
    protected $json;
    protected $array;

    protected $collection;

    function __construct($url = 'http://cyber.law.harvard.edu/rss/examples/rss2sample.xml', $format = null)
    {
        $this->url = $url;

        $this->source = file($this->url);

        $this->format = $format;

        if (!$this->format) {
            $this->detectFormat();
        }

        $this->autoSetArticleElement();
    }

    public function detectFormat($line = 2)
    {
        $line_index = $line - 1;

        if (str_contains($this->source[$line_index], '<rss version="2.0">')) {
            $this->format = 'rss2';
            return;
        }

        if (str_contains($this->source[$line_index], '<feed xmlns="http://www.w3.org/2005/Atom">')) {
            $this->format = 'atom';
            return;
        }
    }

    private function autoSetArticleElement()
    {
        if ($this->format == 'rss2') {
            $this->setArticleElement('channel.item');
            return;
        }

        if ($this->format == 'atom') {
            $this->setArticleElement('entry');
            return;
        }
    }

    public function setArticleElement($element = null)
    {
        $this->articleElement = $element;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getString()
    {
        if (isset($this->string)) {
            return $this->string;
        }
        return $this->string = implode($this->getSource());
    }

    public function getXml()
    {
        if (isset($this->xml)) {
            return $this->xml;
        }
        return $this->xml = simplexml_load_string($this->getString());
    }

    public function getJson()
    {
        if (isset($this->json)) {
            return $this->json;
        }
        return $this->json = json_encode($this->getXml());
    }

    public function getArray()
    {
        if (isset($this->array)) {
            return $this->array;
        }
        return $this->array = json_decode($this->getJson(), TRUE);
    }

    public function getCollection()
    {
        if (isset($this->collection)) {
            return $this->collection;
        }

        $articles = array_get($this->getArray(), $this->articleElement);

        return $this->collection = new Collection($articles);
    }
}