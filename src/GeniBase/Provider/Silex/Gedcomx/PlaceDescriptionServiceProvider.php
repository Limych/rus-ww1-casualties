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

use Pimple\Container;
use GeniBase\Rs\Server\ApiLinksUpdater;
use Gedcomx\Conclusion\PlaceDescription;
use Gedcomx\Rs\Client\Rel;
use Gedcomx\Source\SourceDescription;
use GeniBase\Common\Statistic;
use GeniBase\Rs\Server\GedcomxRsFilter;
use GeniBase\Rs\Server\GedcomxRsUpdater;
use GeniBase\Storager\GeniBaseStorager;
use GeniBase\Storager\StoragerFactory;
use Silex\Application;
use Symfony\Bridge\Twig\Extension\WebLinkExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GeniBase\Common\Sitemap;
use GeniBase\Rs\Server\GedcomxMTimeCalculator;
use Carbon\Carbon;

/**
 *
 *
 * @package GeniBase
 * @subpackage Silex
 * @author Andrey Khrolenok <andrey@khrolenok.ru>
 */
class PlaceDescriptionServiceProvider extends GedcomxServiceProvider
{

    /**
     * {@inheritDoc}
     * @see \Pimple\ServiceProviderInterface::register()
     */
    public function register(Container $app)
    {
        parent::register($app);

        $app['place_description.controller'] = $this;
    }

    /**
     * {@inheritDoc}
     * @see \GeniBase\Provider\Silex\Gedcomx\GedcomxServiceProvider::statistic()
     */
    public function statistic(Application $app)
    {
        $t_places = $app['gb.db']->getTableName('places');
        $query = "SELECT COUNT(*) AS places, MAX(att_modified) AS places_modified FROM $t_places";
        $result = $app['db']->fetchAssoc($query);
        if (false !== $result) {
            return new Statistic($result);
        }

        return new Statistic();
    }

    /**
     * {@inheritDoc}
     * @see \GeniBase\Provider\Silex\Gedcomx\GedcomxServiceProvider::sitemap()
     */
    public function sitemap(Application $app, Request $request)
    {
        $ids = StoragerFactory::newStorager($app['gb.db'], PlaceDescription::class)->getUsedIds();

        $sitemap = new Sitemap();
        $req = $request->duplicate();
        foreach ($ids as $id => $mtime) {
            $url = $request->getUriForPath($this->routesBase . '/' . $id);
            $req->server->set('REQUEST_URI', $url);
            $mtime = $app['http_cache.store']->getMTime($req);
// var_dump($mtime);die;    // TODO Remove me
            $sitemap->addUrl(
                $url,
                $mtime/*,
                SitemapUrl::CHANGEFREQ_MONTHLY    // TODO: Uncomment in production /**/
            );
        }

        return $sitemap;
    }


    /**
     * {@inheritDoc}
     * @see \GeniBase\Provider\Silex\Gedcomx\GedcomxServiceProvider::mountRoutes()
     */
    public function mountRoutes($app, $base)
    {
        parent::mountRoutes($app, $base);

        /** @var Application $app */
        $app->get($base, "place_description.controller:showPlace")->bind('places-root');
        $app->get($base.'/{id}', "place_description.controller:showPlace")->bind('place');
    }

    /**
     * {@inheritDoc}
     * @see \GeniBase\Provider\Silex\Gedcomx\GedcomxServiceProvider::mountApiRoutes()
     */
    public function mountApiRoutes($app, $base)
    {
        parent::mountApiRoutes($app, $base);

        /** @var ControllerCollection $app */
        $app->get($base, "place_description.controller:getComponents");
        $app->get($base.'/{id}', "place_description.controller:getOne");
        $app->get($base.'/{id}/components', "place_description.controller:getComponents");
//         $app->post($base, "place_description.controller:save");
//         $app->put($base.'/{id}', "place_description.controller:update");
//         $app->delete($base.'/{id}', "place_description.controller:delete");
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
        $gedcomx = StoragerFactory::newStorager($app['gb.db'], PlaceDescription::class)
            ->loadGedcomx(array( 'id' => $id, ));

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

    /**
     *
     * @param string $id
     * @return array|Response
     */
    public function getComponents(Application $app, Request $request, $id = null)
    {
        $gedcomx = StoragerFactory::newStorager($app['gb.db'], 'Gedcomx\Conclusion\PlaceDescription')
            ->loadComponentsGedcomx(array( 'id' => $id ));

        if (false === $gedcomx || empty($gedcomx->toArray())) {
            return new Response(null, 204);
        }

        ApiLinksUpdater::update2($app, $request, $gedcomx);

        return $gedcomx;
    }

//     /**
//      * @todo
//      */
//     public function save(Request $request)
//     {

//         $note = $this->getDataFromRequest($request);
//         return array("id" => $this['service']->save($note));

//     }

//     /**
//      * @todo
//      */
//     public function update($id, Request $request)
//     {
//         $note = $this->getDataFromRequest($request);
//         $this['service']->update($id, $note);
//         return $note;

//     }

//     /**
//      * @todo
//      */
//     public function delete($id)
//     {

//         return $this['service']->delete($id);

//     }

//     public function getDataFromRequest(Request $request)
//     {
//         return $note = array(
//             "note" => $request->request->get("note")
//         );
//     }

    /**
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $id
     * @return \Symfony\Component\HttpFoundation\Response|\Gedcomx\Gedcomx
     */
    public function showPlace(Application $app, Request $request, $id = null)
    {
        $storager = StoragerFactory::newStorager($app['gb.db'], \Gedcomx\Conclusion\PlaceDescription::class);
        if (empty($id)) {
            $gedcomx = $storager->loadComponentsGedcomx(array());
        } else {
            $gedcomx = $storager->loadGedcomx(array( 'id' => $id, ));
        }

        if (false === $gedcomx || empty($gedcomx->toArray())) {
            $app->abort(404);
        }

        GedcomxRsUpdater::update($gedcomx);
        $mtime = GedcomxMTimeCalculator::getMTime($gedcomx);

        if (empty($id)) {
            $response = $app['twig']->render('places_list.html.twig', array( 'gedcomx' => $gedcomx, ));
        } else {
            $pl = $gedcomx->getPlaces();
            $mainPlace = $pl[0];

            if (! empty($res = $mainPlace->getSources())) {
                $src = StoragerFactory::newStorager($app['gb.db'], SourceDescription::class)->loadGedcomx([
                    'id' => GeniBaseStorager::getIdFromReference($res[0]->getDescriptionRef()),
                ]);
                $gedcomx->embed($src);
            }

            $gedcomx2 = $storager->loadComponentsGedcomx(array( 'id' => $mainPlace->getId() ));
            GedcomxRsUpdater::update($gedcomx2);

            $gedcomx3 = GedcomxRsFilter::filter(
                $storager->loadNeighboringPlacesGedcomx($mainPlace),
                $gedcomx,
                $gedcomx2
            );
            GedcomxRsUpdater::update($gedcomx3);

            $response = $app['twig']->render(
                'place.html.twig',
                array(
                    'gedcomx' => $gedcomx,
                    'components' => $gedcomx2->getPlaces(),
                    'neighbors' => $gedcomx3->getPlaces(),
                )
            );
        }

        $response = new Response($response);
        $response->setEtag(sha1(
            $gedcomx->toJson()
            . (isset($gedcomx2) ? $gedcomx2->toJson() : null)
            . (isset($gedcomx3) ? $gedcomx3->toJson() : null)
        ), true);

        if ($mtime) {
            $response->setLastModified($mtime);
        }

        $exp = Carbon::now()->addMonth();
        $response->setExpires($exp);
        $response->setSharedMaxAge($exp->diffInSeconds());

        return $response;
    }
}