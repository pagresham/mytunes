jQuery(function($) {

    console.log('Admin.js');

    $('[data-toggle="tooltip"]').tooltip();

    if( $('#mytunes-metabox').length ) {
        update_mytunes_range_input();
    }



    /**
     * updates the slider info in the MyTunes post creation dialog
     */
    function update_mytunes_range_input() {

        var range = document.getElementById('mytunes-range');
        console.log( Math.round(range.value / 10) )
        var mytunes_status = document.getElementById('mytunes-status-display');
        mytunes_status.value = Math.round(range.value / 10); // dont set right away, incase use is just updating other content

        range.oninput = function() {
            mytunes_status.value = Math.round(this.value / 10);
        }
    }



})