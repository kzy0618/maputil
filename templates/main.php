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
?>

    <div id="app">

        <div id="app-content">

            <div id="app-content-wrapper">
				<?php print_unescaped($this->inc('part.content'))?>
<!--				--><?php //print_unescaped($this->inc('mason.test'))?>
            </div>

        </div>

    </div>

<?php
// place custom js logic at the end
//script('maputil', 'script');
script('maputil','maputil');
style('maputil', 'style');
?>