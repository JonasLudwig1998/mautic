<?php

namespace MauticPlugin\MauticFocusBundle\EventListener;

use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubscriber implements EventSubscriberInterface
{
    public const CONTEXT_FOCUS_STATS = 'focus_stats';
    public const FOCUS_GROUP         = 'focus';
    public const PREFIX_FOCUS        = 'f';
    public const PREFIX_STATS        = 'fs';
    public const PREFIX_REDIRECTS    = 'r';
    public const PREFIX_TRACKABLES   = 't';

    public static function getSubscribedEvents(): array
    {
        return [
            ReportEvents::REPORT_ON_BUILD    => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE => ['onReportGenerate', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     */
    public function onReportBuilder(ReportBuilderEvent $event): void
    {
        if (!$event->checkContext(self::CONTEXT_FOCUS_STATS)) {
            return;
        }

        $columns = [
            self::PREFIX_FOCUS.'.name' => [
                'label' => 'mautic.core.name',
                'type'  => 'html',
            ],
            self::PREFIX_FOCUS.'.description' => [
                'label' => 'mautic.core.description',
                'type'  => 'html',
            ],
            self::PREFIX_FOCUS.'.focus_type' => [
                'label' => 'mautic.focus.thead.type',
                'type'  => 'html',
            ],
            self::PREFIX_FOCUS.'.style' => [
                'label' => 'mautic.focus.tab.focus_style',
                'type'  => 'html',
            ],
            self::PREFIX_STATS.'.type' => [
                'label' => 'mautic.focus.interaction',
                'type'  => 'html',
            ],
            self::PREFIX_TRACKABLES.'.hits' => [
                'label' => 'pagehits',
                'type'  => 'html',
            ],
            self::PREFIX_TRACKABLES.'.unique_hits' => [
                'label' => 'uniquehits',
                'type'  => 'html',
            ],
            self::PREFIX_REDIRECTS.'.url' => [
                'label' => 'url',
                'type'  => 'html',
            ],
        ];

        $data = [
            'display_name' => 'mautic.focus.graph.stats',
            'columns'      => $columns,
        ];
        $context = self::CONTEXT_FOCUS_STATS;

        // Register table
        $event->addTable($context, $data, self::FOCUS_GROUP);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event): void
    {
        if ($event->checkContext([self::CONTEXT_FOCUS_STATS])) {
            $queryBuilder = $event->getQueryBuilder();
            $queryBuilder->from(MAUTIC_TABLE_PREFIX.'focus_stats', self::PREFIX_STATS)
                ->leftJoin(self::PREFIX_STATS, MAUTIC_TABLE_PREFIX.'focus', self::PREFIX_FOCUS, self::PREFIX_FOCUS.'.id = '.self::PREFIX_STATS.'.focus_id')
                ->leftJoin(self::PREFIX_STATS, MAUTIC_TABLE_PREFIX.'channel_url_trackables', self::PREFIX_TRACKABLES, self::PREFIX_TRACKABLES.'.channel_id = '.self::PREFIX_STATS.'.focus_id')
                ->leftJoin(self::PREFIX_STATS, MAUTIC_TABLE_PREFIX.'page_redirects', self::PREFIX_REDIRECTS, self::PREFIX_REDIRECTS.'.id = '.self::PREFIX_TRACKABLES.'.redirect_id');
            $event->applyDateFilters($queryBuilder, 'date_added', self::PREFIX_STATS);
            $event->setQueryBuilder($queryBuilder);
        }
    }
}
