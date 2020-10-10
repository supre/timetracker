<?php


namespace RoarProj\providers;


use DateTime;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RoarProj\entities\entries\Entry;
use RoarProj\entities\entries\EntryFactory;
use RoarProj\services\EntriesService;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class EntriesProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['Entries.service'] = function (Container $container) {
            return new EntriesService(
                $container['Entry.factory'],
                $container['User.repository'],
                $container['Entries.repository'],
                $container['orm'],
                $container['html_template']
            );
        };

        $container['Entry.factory'] = function (Container $container) {
            return new EntryFactory();
        };

        $container['Entries.repository'] = function (Container $container) {
            return $container['orm']->getRepository(Entry::class);
        };

        $container['html_template'] = function (Container $container) {
            $loader = new ArrayLoader(
                ['template' => file_get_contents(__DIR__ . '/../../resources/ReportsTemplate.html')]
            );
            $twig = new Environment($loader);
            return function (array $entries, ?DateTime $before, ?DateTime $after) use ($twig) {
                return $twig->render(
                    'template',
                    [
                        'entries' => $entries,
                        'from'    => $before,
                        'to'      => $after
                    ]
                );
            };
        };
    }
}