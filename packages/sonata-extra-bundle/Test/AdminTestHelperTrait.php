<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataExtraBundle\Test;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;

trait AdminTestHelperTrait
{
    protected function selectListObjectLink(
        Crawler $crawler,
        int|string $identifier,
        string $action,
    ): Link {
        return $crawler
            ->filter(\sprintf('.sonata-ba-list-field-actions[objectid="%s"]', $identifier))
            ->selectLink($action)
            ->link()
        ;
    }

    protected function selectMenuObjectLink(
        Crawler $crawler,
        string $action,
    ): Link {
        return $crawler
            ->filter('ul.dropdown-menu')
            ->selectLink($action)
            ->link()
        ;
    }

    protected function submitBatchAction(
        KernelBrowser $client,
        array $indexes,
        string $action,
        string $button = 'OK',
    ): void {
        $crawler = $client->submitForm(
            $button,
            [
                'idx' => $indexes,
                'action' => $action,
            ]
        );

        $client->submit(
            $crawler->selectButton('Yes, execute')->form()
        );
    }

    protected function appendDefaultFiltersToListUrl(string $listUrl): string
    {
        return \sprintf(
            '%s%s%s',
            $listUrl,
            null === parse_url($listUrl, \PHP_URL_QUERY) ? '?' : '&',
            http_build_query(
                [
                    'filter' => [
                        '_page' => 1,
                        '_per_page' => 25,
                    ],
                ]
            )
        );
    }

    protected function filterListByIdentifier(
        KernelBrowser $client,
        string $listUrl,
        int|string $identifier,
        string $identifierName = 'id',
    ): Crawler {
        return $client->request(
            'GET',
            \sprintf(
                '%s?%s',
                $listUrl,
                http_build_query(
                    [
                        'filter' => [
                            $identifierName => [
                                'type' => 3,
                                'value' => $identifier,
                            ],
                        ],
                    ]
                )
            )
        );
    }
}
