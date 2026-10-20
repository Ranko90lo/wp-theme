<?php

namespace theme\controllers;

use theme\Theme;
use theme\models\Event;
use wpmvc\web\View;
use wpmvc\web\Meta_Box_Controller;

class Event_Options_Controller extends Meta_Box_Controller {

    public function on_init() {}

    public function on_action( $model ) {
        echo View::render( 'views/events/meta-info', array(
            'model' => $model,
        ) );
    }

    /**
     * @param Event $model
     * @return void
     */
    public function on_save( $model ) {
        if ( $model->load( Theme::$app->request->post() ) ) {
            $model->save();
        }
    }

}
