<?php

use Phrawl\Crawler\AbstractBaseCrawler;
use Phrawl\CrawlerEngine;
use Phrawl\Request\RequestFactory;
use Phrawl\Request\Types\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

require __DIR__ . '/vendor/autoload.php';

final class Agenda extends AbstractBaseCrawler
{
    private $data;
    public $name = 'agenda';

    public $startUrls = 'http://www2.planalto.gov.br/acompanhe-o-planalto/agenda-do-presidente-da-republica';

    /**
     * @param Crawler $crawler
     * @param RequestInterface $request
     * @param Client|null $pantherClient
     *
     * @return Generator|void
     * @throws Exception
     */
    public function parser(Crawler $crawler, RequestInterface $request, ?Client $pantherClient = null)
    {
        $this->data = new \DateTime();
        yield RequestFactory::newWebDriver(
            'GET',
            sprintf(
                'http://www2.planalto.gov.br/acompanhe-o-planalto/agenda-do-presidente-da-republica/json/%s',
                $this->data->format('Y-m-d')
            ),
            [],
            null,
            [$this, 'panther']
        );
    }

    public function panther(Crawler $crawler, RequestInterface $request, ?Client $pantherClient = null)
    {
        printf("Dia: %s\n\n", $this->data->format('d/m/Y'));

        $json = json_decode($crawler->filterXPath('//pre')->html(), true);
        foreach ($json as $day) {
            if ($day['isSelected'] === false) {
                continue;
            }

            foreach ($day['items'] as $encontros) {
                printf("%s: %s\n", $encontros['title'], $encontros['start']);
            }
        }
    }
}

(new CrawlerEngine(new Agenda()))
    ->run();