// Logs Core - Funciones principales JQuery para logs
jQuery(function($) {
// recargar tabla
$(document).on("click", '.recargar', function (event) {
    window.LaravelDataTables["log-table"].draw()
});
//fin del ready
});
