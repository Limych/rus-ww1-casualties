<?php
/**
 * GeniBase — the content management system for genealogical websites.
 *
 * @package GeniBase
 * @author Andrey Khrolenok <andrey@khrolenok.ru>
 * @copyright Copyright (C) 2014-2017 Andrey Khrolenok
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * @link https://github.com/Limych/GeniBase
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */
namespace GeniBase\Provider\Silex\Gedcomx;

use GeniBase\Rs\Server\ApiLinksUpdater;
use Gedcomx\Rs\Client\Rel;
use GeniBase\Common\Statistic;
use Pimple\Container;
use Silex\Application;
use Symfony\Bridge\Twig\Extension\WebLinkExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GeniBase\Storager\EventStorager;

class EventServiceProvider extends GedcomxServiceProvider
{

    /**
     * {@inheritDoc}
     * @see \Pimple\ServiceProviderInterface::register()
     */
    public function register(Container $app)
    {
        parent::register($app);

        $app['event.controller'] = $this;
    }

    /**
     * {@inheritDoc}
     * @see \GeniBase\Provider\Silex\Gedcomx\GedcomxServiceProvider::statistic()
     */
    public function statistic(Application $app)
    {
        $t_events = $app['gb.db']->getTableName('events');

        $query = "SELECT COUNT(*) AS events, MAX(att_modified) AS events_modified FROM $t_events";
        $result = $app['db']->fetchAssoc($query);
        if (false !== $result) {
            $result = new Statistic($result);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @see \GeniBase\Provider\Silex\Gedcomx\GedcomxServiceProvider::mountApiRoutes()
     */
    public function mountApiRoutes($app, $base)
    {
        parent::mountApiRoutes($app, $base);

        $app->get($base, "event.controller:statistic");
        $app->get($base.'/{id}', "event.controller:getOne");
        //         $app->post($base, "persons.controller:save");
        //         $app->put($base.'/{id}', "persons.controller:update");
        //         $app->delete($base.'/{id}', "persons.controller:delete");
    }

    /**
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $id
     * @return \Symfony\Component\HttpFoundation\Response|\Gedcomx\Gedcomx
     */
    public function getOne(Application $app, Request $request, $id)
    {
        $st = new EventStorager($app['gb.db']);
        $gedcomx = $st->loadGedcomx(array( 'id' => $id, ));

        if (false === $gedcomx || empty($gedcomx->toArray())) {
            return new Response(null, 204);
        }

        ApiLinksUpdater::update2($app, $request, $gedcomx);

        if (class_exists('Symfony\Bridge\Twig\Extension\WebLinkExtension')) {
            $wl = new WebLinkExtension($app['request_stack']);
            $wl->link($request->getUri(), Rel::DESCRIPTION);
        }
        return $gedcomx;
    }

//     public function save(Request $request)
//     {}  // TODO

//     public function update($id, Request $request)
//     {}  // TODO

//     public function delete($id)
//     {}  // TODO

//     public function getDataFromRequest(Request $request)
//     {
//         return $note = array(
//             "note" => $request->request->get("note")
//         );
//     }
}