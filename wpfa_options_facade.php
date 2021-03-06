<?php
/*
Contains all functions relating to getting and setting options
*/

function wpfa_generateInitialOptions() {
    //instantiate 'variable like' IDs
    for ($i = 1; $i <=10; $i++ ) {
        update_option("fb_ID$i",'');
    }

    //sanitise settings in options menu upon activation
    for ($i = 1; $i <=10; $i++ ) {
        update_option("page-ID$i",'');
    }

    //default other options to true
    update_option('wpfa-images', 1);
    update_option('wpfa-links', 1);
    update_option('wpfa-videos', 1);

    //option to store time of last update
    update_option('wpfa_last_update_time', 0);
}

//retrieves list of facebook IDs set by user
function wpfa_getIDList() {
    $list = array();
    for ($i = 1; $i <= 10; $i++) {
      if (get_option("page-ID$i") != '')
          array_push($list,get_option("page-ID$i"));
    }
    return $list;
}

//checks if settings have changed
function wpfa_checkOptions() {
    for ($i = 1; $i <=10; $i++ ) {
        // if the 'local' ID is different from what's in the settings and not empty
        $setID = get_option("page-ID$i");
        if ($setID != get_option("fb_ID$i") && $setID != '') {
            //update our 'local' variable so it is in par with the settings
            update_option("fb_ID$i",$setID);
            //generate initial posts if any user set fb ID has changed
            wpfa_gen_initial_posts($setID);
            //rest for a while
            sleep(0.1);
            //call reset cron to retrieve new posts from facebook
            wpfa_reset_cron();
        }
    }
}

//displays a welcome message in an appropriate manner.
function wpfa_displayWelcome() {
    //check so it displays message only upon plugin activation
    if (WPFA_ACTIVATED != get_option('wpfa_activated')) {
        //adds a temporary, variable like option if it's just been activated
        update_option('wpfa_activated','WPFA_ACTIVATED');
        ?>
            <div class="updated">
            <p><?php echo "<strong>Please click on the <em>Feed Aggregator Options</em>
                           on the side pane to get started.</strong>" ?></p>
            </div>
        <?php
    }
}

?>
