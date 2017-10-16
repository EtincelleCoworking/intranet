<?php

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class SensorApiController extends Controller
{
    public function log($location_slug, $sensor_slug)
    {
        $location = Location::where('slug', $location_slug)->first();
        if (!$location) {
            App::abort(403);
        }
        $sensor = Sensor::where('slug', $sensor_slug)->where('location_id', $location->id)->first();
        if (!$sensor) {
            App::abort(403);
        }
        $item = new SensorLog();
        $item->sensor_id = $sensor->id;
        $item->occured_at = Input::get('occured_at');
        $item->value = Input::get('value');
        $item->save();

        return new Response('OK', 200);
    }

    public function view($location_slug, $sensor_slug)
    {
        $location = Location::where('slug', $location_slug)->first();
        if (!$location) {
            App::abort(403);
        }
        $sensor = Sensor::where('slug', $sensor_slug)->where('location_id', $location->id)->first();
        if (!$sensor) {
            App::abort(403);
        }
        $items = DB::select(DB::raw(sprintf('SELECT sensor_logs.occured_at, sensor_logs.value 
  FROM sensor_logs JOIN sensors ON sensor_logs.sensor_id = sensors.id
  WHERE sensors.slug = "%s" AND sensors.location_id = %d
  ORDER BY sensor_logs.occured_at DESC LIMIT 0,100
  ', $sensor->slug, $sensor->location_id)));
        return View::make('sensor::view', array(
            'location' => $location,
            'sensor' => $sensor,
            'items' =>$items));

    }
}

    // table de log avec TS / value
    //  --> table avec TS / nouvel état -- uniquement les changements d'états
    //
    // tableau de bord des sensors
    // - vert - donnée récente
    // - rouge - pas de donnée récente
    // - grise - pas de donnée du tout

    // SELECT sensors.id as sensor_id, sensors.name as sensor_name, MAX(sensor_logs.occured_at) as last_log_at
    //   FROM sensors
    //      LEFT OUTER JOIN sensor_logs ON sensors.id = sensor_logs.sensor_id
    //      JOIN locations on sensors.location_id = locations.id
    //   GROUP BY sensors.id
    //   ORDER BY locations.name ASC, sensors.order_index ASC



    // init
    //  - connexion au WIFI
    //
    // loop
    //  - mesurer la distance
    //  - POST sur l'API avec la valeur
    //  - dormir pendant 10/30s ?

    // - est-ce qu'en dormant comme cela on va perdre la connexion au WIFI?
    // - reconnexion automatique?

    // pour la détection de mouvement, envoyer l'information quand on l'a?
    // pb de détection du alive => possibilité de timeout? pour envoyer un signal au moins toutes les minutes?
    // loop qui enregistre l'état (avec mouvement?) et qui flush périodiquement via l'API




    // booking
    // - rajouter sur les ressources
    //    * Description
    //    * URL
    //    * infos pour la signalétique (nb de flèches à gauche, nb de flèches à droite)
    // - les rajouter dans le formulaire d'édition
    // - rajouter dans l'interface d'ajout/modification d'une réservation
    // - rajouter dans la légende icone (?) icone lien web
    // - mettre le tarif horaire dans l'interface? afficher le tarif de la réservation?

    // ajouter la gestion des tarifs sur les créneaux horaires pour faciliter la gestion
    // de la facturation avec des tarifs spécifiques

    // utiliser le format A3 pour les impressions? flèche / identification sur un mini plan
    // rajouter les logos pour les entreprises et les rajouter sur les fiches imprimées pour les réservations?



// pas de réservations
// - nom de la salle
// - heure de la prochaine réservation
// - disponible xx minutes
// - diagramme avec le déroulement de la journée et les plages d'occupations
// - possibilité de réserver la salle?

// réservation en cours
// - nom de la salle
// - nom de la réservation
// - heure de fin (durée)
// - heure de début? (dureé?) utile pour savoir si les participants ne sont pas encore arrivés
// - heure de la prochaine réservation + nom?
