<?php

namespace theme\views\events;

use theme\models\Event;
use wpmvc\helpers\Form;

/**
 * @var \theme\models\Event $model
 */
?>

<?php echo Form::field( $model, 'city' )->select( Event::cities() ); ?>

<?php echo Form::field( $model, 'date' )->input_text(); ?>
