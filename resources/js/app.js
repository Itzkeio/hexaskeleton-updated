// Import jQuery first
import $ from 'jquery';
window.$ = $;
window.jQuery = $;

// Then, import Bootstrap and other dependencies that require jQuery
import 'bootstrap';  // Bootstrap's JS depends on jQuery

// import moment from 'moment';
// window.moment = moment;

// Now import the rest of your modules
// import './login';
// import './search';
// import './footbar';

// import 'datatables.net';
// import 'datatables.net-bs5';

// import './plugins/popper.min.js';
// import './plugins/simplebar.min.js';
// import './fonts/custom-font.js';
// import './plugins/feather.min.js';
// import './sweetalert.js';
// import './select2.js';
// import './moment.min.js';
// import './pcoded.js';
// import './config.js';
// import '../jstree/dist/jstree.js';
// import './bootstrap-datepicker.js';
// import './jquery.dataTables.min.js';
// import './datatable.button.min.js';

$(document).ready(function () {
    var observer = window.ResizeObserver ? new ResizeObserver(function (entries) {
        entries.forEach(function (entry) {
            $(entry.target).DataTable().columns.adjust();
        });
    }) : null;

    // Declare resizeHandler using let or const (or var if preferred)
    const resizeHandler = function ($tables) {
        if (observer) {
            $tables.each(function () {
                observer.observe(this);
            });
        }
    };

    // $('.column-search.datepicker').datepicker({
    //     format: "dd MM yyyy",
    //     autoclose: true,
    //     clearBtn: true,
    //     todayHighlight: true
    // });
});