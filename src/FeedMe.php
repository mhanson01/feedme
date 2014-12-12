<?php namespace Feedme;

use Illuminate\Support\Collection;

class FeedMe {

    /**
     * URL of the feed
     *
     * @var string
     */
    protected $url;
    /**
     * Format of the feed
     * rss, rss2, atom
     *
     * @var null
     */
    protected $format;
    /**
     * Source code of the page as loaded by file()
     *
     * @var array
     */
    protected $source;
    /**
     * a dot notation string representation of the main "item" or "entry" elements
     * @var null
     */
    protected $articleElement = null;

    /**
     * Feed formatted as a string
     *
     * @var
     */
    protected $string;
    /**
     * Feed formatted as xml
     *
     * @var
     */
    protected $xml;
    /**
     * Feed formatted as json
     *
     * @var
     */
    protected $json;
    /**
     * Feed formatted as an array
     *
     * @var
     */
    protected $array;

    /**
     * Feed elements formatted as an Illuminate\Support\Collection
     *
     * @var
     */
    protected $collection;

    /**
     * Provide the url to the feed and you may also hard code the format
     *
     * @param string $url
     * @param null   $format
     */
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

    /**
     * Try to detect the format of the feed from line 2 of the source
     *
     * @param int $line
     */
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

    /**
     * Set the default dot notated element for each feed format
     */
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

    /**
     * Call this to set the dot notated element for "items" or "entry"s
     *
     * @param null $element
     */
    public function setArticleElement($element = null)
    {
        $this->articleElement = $element;
    }

    /**
     * Return the source
     *
     * @return array
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Return a string of the feed
     *
     * @return string
     */
    public function getString()
    {
        if (isset($this->string)) {
            return $this->string;
        }
        return $this->string = implode($this->getSource());
    }

    /**
     * Return the processed XML of the feed
     *
     * @return \SimpleXMLElement
     */
    public function getXml()
    {
        if (isset($this->xml)) {
            return $this->xml;
        }
        return $this->xml = simplexml_load_string($this->getString());
    }

    /**
     * Return the json format of the feed
     *
     * @return string
     */
    public function getJson()
    {
        if (isset($this->json)) {
            return $this->json;
        }
        return $this->json = json_encode($this->getXml());
    }

    /**
     * Return an processed array of the feed
     *
     * @return mixed
     */
    public function getArray()
    {
        if (isset($this->array)) {
            return $this->array;
        }
        return $this->array = json_decode($this->getJson(), TRUE);
    }

    /**
     * Return a collection of "item" or "entry" elements
     * 
     * @return Collection
     */
    public function getCollection()
    {
        if (isset($this->collection)) {
            return $this->collection;
        }

        $articles = array_get($this->getArray(), $this->articleElement);

        return $this->collection = new Collection($articles);
    }
}