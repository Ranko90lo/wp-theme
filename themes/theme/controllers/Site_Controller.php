<?php

namespace theme\controllers;

use wpmvc\web\Controller;

class Site_Controller extends Controller {

    public function action_index() {
        $this->title = 'AAA';

        wp_send_json_success();
    }

}
