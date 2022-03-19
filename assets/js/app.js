/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';
import '../css/global.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

function scroll_to_top(div) {
    $(div).on('click', function() {
        $('html,body').animate({scrollTop: 0}, 'slow');
    });
    $(window).on('scroll', function(){
        if($(window).scrollTop()<500){
            $(div).fadeOut();
        } else{
            $(div).fadeIn();
        }
    });
}
scroll_to_top("#back-to-top");
