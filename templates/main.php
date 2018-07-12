<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 2:17 PM
 */

// put library here
// example:
// script('maputil', 'handlebars-v4.0.11');

// jQuery is included by default on every page.

style('maputil', 'style');

?>

    <div id="app">

        <div id="app-content">

            <div id="app-content-wrapper">
                <?php //print_unescaped($this->inc('part.content')); ?>

                <div style="text-align: center;">
                    <h1>
                        <button id="get_one" data-pk="1">Get the first recording</button>
                        <button id="update_one_with_url_param" data-pk="1">Update the first recording with url param</button>
                        <button id="update_one_with_ajax_body" data-pk="1">Update the first recording with ajax body</button>
                    </h1>

                    <div id="placeholder">

                    </div>

                </div>

            </div>

        </div>

    </div>

<?php
// place custom js logic at the end
script('maputil', 'script');
?>